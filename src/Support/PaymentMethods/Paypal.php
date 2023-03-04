<?php

namespace Juzaweb\Subscription\Support\PaymentMethods;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Juzaweb\Subscription\Abstracts\PaymentMethodAbstract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Exceptions\SubscriptionExistException;
use Juzaweb\Subscription\Models\PaymentHistory;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
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

    public function createPlan(PlanModel $plan): string
    {
        $planAPI = new Plan();
        $planAPI->setName($plan->name)
            ->setDescription($plan->description ?? "Subscription plan {$plan->name}")
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
                        'value' => $plan->price,
                        'currency' => 'USD'
                    ]
                )
            );

        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl(route('ajax', ['subscription/return']))
            ->setCancelUrl(route('ajax', ['subscription/cancal']))
            ->setAutoBillAmount('yes')
            ->setInitialFailAmountAction('CONTINUE')
            ->setMaxFailAttempts('0');

        $planAPI->setPaymentDefinitions([$paymentDefinition]);
        $planAPI->setMerchantPreferences($merchantPreferences);

        $createdPlan = $planAPI->create($this->getApiContext());

        $patch = new Patch();
        $value = new PayPalModel('{"state":"ACTIVE"}');
        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);

        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);
        $createdPlan->update($patchRequest, $this->apiContext);

        $planAPI = Plan::get($createdPlan->getId(), $this->apiContext);

        return $planAPI->getId();
    }

    public function getRedirectUrl(PlanPaymentMethod $planPaymentMethod): string
    {
        $agreement = new Agreement();
        $agreement->setName('Monthly Subscription Agreement')
            ->setDescription("{$planPaymentMethod->plan->name} Premium Plan")
            ->setStartDate(Carbon::now()->addMinutes(2)->toIso8601String());

        $planAPI = new Plan();
        $planAPI->setId($planPaymentMethod->payment_plan_id);
        $agreement->setPlan($planAPI);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            $agreement = $agreement->create($this->getApiContext());
            return $agreement->getApprovalLink();
        } catch (PayPalConnectionException $e) {
            throw new PaymentException($e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function return(PlanModel $plan, array $data): UserSubscription
    {
        $agreement = new Agreement();
        $token = Arr::get($data, 'token');

        if (PaymentHistory::where('token', '=', $token)->exists()) {
            throw new SubscriptionExistException('Payment already exist.');
        }

        $result = $agreement->execute($token, $this->getApiContext());

        if ($result->state != 'Active') {
            throw new PaymentException('Payment not active.');
        }

        $subscriber = new UserSubscription();
        $subscriber->plan_id = $plan->id;
        $subscriber->method_id = $this->paymentMethod->id;
        $subscriber->user_id = Auth::id();
        $subscriber->agreement_id = $result->id;
        $subscriber->amount = $result->plan->payment_definitions[0]->amount->value;
        $subscriber->save();
        return $subscriber;
    }

    public function webhook(Request $request)
    {
        $resource = $request->input('resource');
        $agreement = UserSubscription::where(['agreement_id' => $resource['billing_agreement_id']])->first(['user_id']);

        Log::info('Paypal Notify: ' . json_encode($request->all()));

        if (empty($agreement)) {
            Log::error('Not available agreement: postNotify ' . json_encode($request->all()));
            return response('Webhook Handled', 200);
        }

        $user = User::find($agreement->user_id);
        if (empty($user)) {
            Log::error('Empty user. ' . json_encode($request->all()));
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
        return true;
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

        $config = $this->getConfigByMod();

        $this->apiContext = new ApiContext(new OAuthTokenCredential($config['client_id'], $config['secret']));

        $paypalSettings = $this->getPaypalSettings();
        $paypalSettings['mod'] = Arr::get($this->paymentMethod->configs, 'mod', 'sandbox');

        $this->apiContext->setConfig($paypalSettings);
        return $this->apiContext;
    }

    protected function getConfigByMod(): array
    {
        $clientId = Arr::get($this->paymentMethod->configs, 'mod') == 'live'
            ? Arr::get($this->paymentMethod->configs, 'live_client_id')
            : Arr::get($this->paymentMethod->configs, 'sandbox_client_id');

        $secret = Arr::get($this->paymentMethod->configs, 'mod') == 'live'
            ? Arr::get($this->paymentMethod->configs, 'live_secret')
            : Arr::get($this->paymentMethod->configs, 'sandbox_secret');

        return ['client_id' => $clientId, 'secret' => $secret];
    }

    protected function getPaypalSettings(): array
    {
        return [
            /**
             * Payment Mode
             *
             * Available options are 'sandbox' or 'live'
             */
            'mode' => 'sandbox',

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
