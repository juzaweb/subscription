<?php

namespace Juzaweb\Subscription\Support\PaymentMethods;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Juzaweb\Subscription\Abstracts\PaymentMethodAbstract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Exceptions\PaymentMethodException;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\UserSubscription;
use PayPal\Api\Agreement;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class Paypal extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'paypal';

    protected ApiContext $apiContext;

    public function createPlan(): string
    {
        $plan = new Plan();
        $plan->setName($this->plan->name)
            ->setDescription($this->plan->description)
            ->setType('infinite');

        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency('Month')
            ->setFrequencyInterval('1')
            ->setCycles('0')
            ->setAmount(
                new Currency(
                    [
                        'value' => $this->plan->price,
                        'currency' => 'USD'
                    ]
                )
            );

        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl(route('paypal.return'))
            ->setCancelUrl(route('paypal.cancel'))
            ->setAutoBillAmount('yes')
            ->setInitialFailAmountAction('CONTINUE')
            ->setMaxFailAttempts('0');

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        $createdPlan = $plan->create($this->getApiContext());

        $patch = new Patch();
        $value = new PayPalModel('{"state":"ACTIVE"}');
        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);

        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);
        $createdPlan->update($patchRequest, $this->apiContext);

        $plan = Plan::get($createdPlan->getId(), $this->apiContext);

        return $plan->getId();
    }

    public function isRedirect(): bool
    {
        return true;
    }

    public function getRedirectUrl(): string
    {
        $agreement = new Agreement();
        $agreement->setName('Stream3s Monthly Subscription Agreement')
            ->setDescription('Stream3s Premium Plan')
            ->setStartDate(
                Carbon::now()->addMinutes(2)->toIso8601String()
            );

        $plan = new Plan();
        $plan->setId($this->plan->planPaymentMethods()->where(['method_id' => $plan]));
        $agreement->setPlan($plan);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            $agreement = $agreement->create($this->getApiContext());
            return $agreement->getApprovalLink();
        } catch (PayPalConnectionException $e) {
            throw new PaymentMethodException($e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function return(Request $request)
    {
        if (isset($_GET['token'])) {
            $token = $request->token;
            if (UserSubscription::where('token', '=', $token)->exists()) {
                return redirect()->route('client.upgrade');
            }

            $agreement = new \PayPal\Api\Agreement();

            $error = false;
            try {
                $result = $agreement->execute($token, $this->apiContext);
                $message = trans('app.payment_success');

                if ($result->state != 'Active') {
                    $message = 'Payment not active';
                    $error = true;
                }

                if ($error) {
                    $session = [
                        'status' => ($error) ? 'warning' : 'success',
                        'message' => $message,
                    ];

                    \Log::error('paypalReturn - ' . $message);

                    session()->put('message', json_encode($session));
                    session()->save();

                    return redirect()->route('client.upgrade');
                }

                $subscriber = new UserSubscription();
                $subscriber->user_id = \Auth::id();
                $subscriber->role = 'subscriber';
                $subscriber->method = 'paypal';
                $subscriber->token = $token;
                $subscriber->payer_id = $result->payer->payer_info->payer_id;
                $subscriber->payer_email = $result->payer->payer_info->email;
                $subscriber->agreement_id = $result->id;
                $subscriber->amount = $result->plan->payment_definitions[0]->amount->value;
                $subscriber->save();

                event(new PaymentSuccess($result));
            } catch (PayPalConnectionException $ex) {
                $message = trans('app.cancel_paypal');
                $error = true;

                $session = [
                    'status' => ($error) ? 'warning' : 'success',
                    'message' => $message,
                ];

                session()->put('message', json_encode($session));
                session()->save();

                return redirect()->route('client.upgrade');
            }
        } else {
            $message = trans('app.cancel_paypal');
            $error = true;
        }

        $session = [
            'status' => ($error) ? 'warning' : 'success',
            'message' => $message,
        ];

        session()->put('message', json_encode($session));
        session()->save();

        return redirect()->route('client.upgrade');
    }

    public function cancel()
    {
        if (\Auth::check()) {
            return redirect()->route('client.upgrade');
        }

        return redirect()->route('frontend.home');
    }

    public function webhook(Request $request)
    {
        $resource = $request->input('resource');
        $agreement = UserSubscription::where('agreement_id', '=', @$resource['billing_agreement_id'])->first(['user_id']);
        \Log::info('Paypal Notify: ' . json_encode($request->all()));

        if (empty($agreement)) {
            \Log::error('Not available agreement: postNotify ' . json_encode($request->all()));
            return response('Webhook Handled', 200);
        }

        $user = User::find($agreement->user_id);
        if (empty($user)) {
            \Log::error('Empty user. ' . json_encode($request->all()));
            return response('Webhook Handled', 200);
        }

        if (strtotime($user->premium_enddate) > time()) {
            $expiration_date = date("Y-m-d 23:59:59", strtotime("+1 month", strtotime($user->premium_enddate)));
        } else {
            $expiration_date = date("Y-m-d 23:59:59", strtotime("+1 month"));
        }

        $user->update(
            [
                'premium_enddate' => $expiration_date,
            ]
        );

        $subscriber = new PaymentHistory();
        $subscriber->method = 'paypal';
        $subscriber->user_id = $user->id;
        $subscriber->agreement_id = $resource['billing_agreement_id'];
        $subscriber->save();

        return response('Webhook Handled', 200);
    }

    public function getConfigs(): array
    {
        return [
            'mode' => [
                'type' => 'select',
                'label' => 'Payment Mode',
                'data' => [
                    'options' => [
                        'sandbox' => 'Sandbox',
                        'live' => 'Live',
                    ]
                ]
            ],
            'sandbox_client_id' => [
                'label' => 'Sandbox Client ID',
            ],
            'sandbox_secret' => [
                'label' => 'Sandbox Secret',
            ],
            'live_client_id' => [
                'label' => 'Live Client ID',
            ],
            'live_secret' => [
                'label' => 'Live Secret',
            ],
        ];
    }

    protected function getApiContext(): ApiContext
    {
        if (isset($this->apiContext)) {
            return $this->apiContext;
        }

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($this->client_id, $this->secret)
        );
        $this->apiContext->setConfig($this->getPaypalSettings());

        return $this->apiContext;
    }

    protected function getPaypalSettings(): array
    {
        return [
            /**
             * Payment Mode
             *
             * Available options are 'sandbox' or 'live'
             */
            'mode' => env('PAYPAL_MODE', 'sandbox'),

            // Specify the max connection attempt (3000 = 3 seconds)
            'http.ConnectionTimeOut' => 3000,

            // Specify whether we want to store logs
            'log.LogEnabled' => true,

            // Specigy the location for our PayPal logs
            'log.FileName' => storage_path('/logs/paypal.log'),

            /**
             * Log Level
             *
             * Available options: 'DEBUG', 'INFO', 'WARN' or 'ERROR'
             *
             * Logging is most verbose in the DEBUG level and decreases
             * as you proceed towards ERROR. WARN or ERROR would be a
             * recommended option for live environments.
             *
             */
            'log.LogLevel' => 'DEBUG'
        ];
    }
}
