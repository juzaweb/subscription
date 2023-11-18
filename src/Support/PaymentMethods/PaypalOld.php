<?php

namespace Juzaweb\Subscription\Support\PaymentMethods;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Juzaweb\Subscription\Abstracts\PaymentMethodAbstract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Contrasts\PaymentResult;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use PayPal\Api\Agreement;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class PaypalOld extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'paypal';
    protected bool $isRedirect = true;

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
        $merchantPreferences->setReturnUrl($this->getReturnUrl($plan))
            ->setCancelUrl($this->getCancelUrl($plan))
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
        $createdPlan->update($patchRequest, $this->getApiContext());

        $planAPI = Plan::get($createdPlan->getId(), $this->getApiContext());

        return $planAPI->getId();
    }

    public function updatePlan(PlanModel $plan, PlanPaymentMethod $planPaymentMethod): string
    {
        $planAPI = Plan::get($planPaymentMethod->payment_plan_id, $this->getApiContext());

        // make changes to the plan object
        $planAPI->setDescription($plan->description ?? "Subscription plan {$plan->name}");

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
        $merchantPreferences->setReturnUrl($this->getReturnUrl($plan))
            ->setCancelUrl($this->getCancelUrl($plan))
            ->setAutoBillAmount('yes')
            ->setInitialFailAmountAction('CONTINUE')
            ->setMaxFailAttempts('0');

        $planAPI->setPaymentDefinitions([$paymentDefinition]);
        $planAPI->setMerchantPreferences($merchantPreferences);

        $patch = new Patch();
        $value = new PayPalModel('{"state":"ACTIVE"}');
        $patch->setOp('replace')
            ->setPath('/')
            ->setValue($value);

        $patchRequest = new PatchRequest();
        $patchRequest->addPatch($patch);

        // update the plan on PayPal server
        $planAPI->update($patchRequest, $this->getApiContext());

        return $planAPI->getId();
    }

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): bool
    {
        $agreement = new Agreement();
        $agreement->setName('Monthly Subscription Agreement')
            ->setDescription("{$plan->name} Premium Plan")
            ->setStartDate(Carbon::now()->addMinutes(2)->toIso8601String());

        $planAPI = new Plan();
        $planAPI->setId($planPaymentMethod->payment_plan_id);
        $agreement->setPlan($planAPI);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            $agreement = $agreement->create($this->getApiContext());
            $this->setRedirectUrl($agreement->getApprovalLink());
            return true;
        } catch (PayPalConnectionException $e) {
            throw new PaymentException($e);
        }
    }

    public function return(PlanModel $plan, array $data): PaymentResult
    {
        $agreement = new Agreement();
        $token = Arr::get($data, 'token');

        $result = $agreement->execute($token, $this->getApiContext());

        if ($result->state != 'Active') {
            throw new PaymentException('Payment not active.');
        }

        return $this->makePaymentReturnResult(
            $result->id,
            $result->plan->payment_definitions[0]->amount->value,
            $token
        );
    }

    public function webhook(Request $request): bool|PaymentResult
    {
        $resource = $request->input('resource');
        $eventType = $request->input('event_type');
        $amount = Arr::get($resource, 'amount.total');
        $requestBody = json_encode($request->post(), JSON_THROW_ON_ERROR);

        /**
         * In Documentions https://developer.paypal.com/docs/api/webhooks/#verify-webhook-signature_post
         * All header keys as UPPERCASE, but I recive the header key as the example array, First letter as UPPERCASE
         */
        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);
        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
        $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
        $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
        $signatureVerification->setWebhookId($this->getConfigByMod()['webhook_id']);
        $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
        $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);
        $signatureVerification->setRequestBody($requestBody);

        try {
            $output = $signatureVerification->post($this->getApiContext());
        } catch (Exception $e) {
            throw new PaymentException($e);
        }

        if ($output->getVerificationStatus() != 'SUCCESS') {
            throw new PaymentException('Webhook Signature Invalid.');
        }

        if ($eventType != 'BILLING.SUBSCRIPTION.CREATED') {
            return false;
        }

        return $this->makePaymentReturnResult(
            Arr::get($resource, 'billing_agreement_id'),
            $amount,
            $request->input('id')
        );
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
            'sandbox_webhook_id' => [
                'label' => 'Sandbox Webhook ID',
            ],
            'live_client_id' => [
                'label' => 'Live Client ID',
            ],
            'live_secret' => [
                'label' => 'Live Secret',
            ],
            'live_webhook_id' => [
                'label' => 'Live Webhook ID',
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

    /**
     * @return array<string, string>
     */
    protected function getConfigByMod(): array
    {
        $clientId = Arr::get($this->paymentMethod->configs, 'mod') == 'live'
            ? Arr::get($this->paymentMethod->configs, 'live_client_id')
            : Arr::get($this->paymentMethod->configs, 'sandbox_client_id');

        $secret = Arr::get($this->paymentMethod->configs, 'mod') == 'live'
            ? Arr::get($this->paymentMethod->configs, 'live_secret')
            : Arr::get($this->paymentMethod->configs, 'sandbox_secret');

        $webhook = Arr::get($this->paymentMethod->configs, 'mod') == 'live'
            ? Arr::get($this->paymentMethod->configs, 'live_webhook_id')
            : Arr::get($this->paymentMethod->configs, 'sandbox_webhook_id');

        return ['client_id' => $clientId, 'secret' => $secret, 'webhook_id' => $webhook];
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
