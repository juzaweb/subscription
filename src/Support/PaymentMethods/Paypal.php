<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Support\PaymentMethods;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Juzaweb\Subscription\Abstracts\PaymentMethodAbstract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Paypal extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'paypal';

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): bool
    {
        $response = $this->getProvider()
            ->addProduct('Demo Product', 'Demo Product', 'SERVICE', 'SOFTWARE')
            //->addPlanTrialPricing('DAY', 7)
            ->addMonthlyPlan('Demo Plan', 'Demo Plan', 100)
            ->setReturnAndCancelUrl($this->getReturnUrl($plan), $this->getCancelUrl($plan))
            ->setupSubscription('John Doe', 'john@example.com', now()->addMinutes(2));

        $approveLink = Arr::get($response, 'links.0.href');

        $this->setRedirectUrl($approveLink);

        return true;
    }

    public function webhook(Request $request): bool|PaymentReturnResult
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

        $provider = $this->getProvider();
        $verifyResource = $provider->verifyWebHook(
            [
                "transmission_id" => $headers['PAYPAL-TRANSMISSION-ID'],
                "transmission_time" => $headers['PAYPAL-TRANSMISSION-TIME'],
                "cert_url" => $headers['PAYPAL-CERT-URL'],
                "auth_algo" => $headers['PAYPAL-AUTH-ALGO'],
                "transmission_sig" => $headers['PAYPAL-TRANSMISSION-SIG'],
                "webhook_id" => $this->getConfigByMod()['webhook_id'],
                "webhook_event" => $requestBody,
            ]
        );

        if ($verifyResource['verification_status'] != 'SUCCESS') {
            throw new PaymentException('Webhook Signature Invalid.');
        }

        if ($eventType != 'PAYMENT.SALE.COMPLETED') {
            return false;
        }

        return $this->makePaymentReturnResult(
            Arr::get($resource, 'billing_agreement_id'),
            $amount,
            $request->input('id')
        );
    }

    public function createPlan(PlanModel $plan): string
    {
        // TODO: Implement createPlan() method.
    }

    public function updatePlan(PlanModel $plan, PlanPaymentMethod $planPaymentMethod): string
    {
        // TODO: Implement updatePlan() method.
    }

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

    protected function getProvider(): PayPalClient
    {
        $config = [
            'mode'    => Arr::get($this->paymentMethod->configs, 'mod', 'sandbox'),
            'live' => [
                'client_id'         => Arr::get($this->paymentMethod->configs, 'live_client_id'),
                'client_secret'     => Arr::get($this->paymentMethod->configs, 'live_secret'),
                //'app_id'            => 'APP-80W284485P519543T',
            ],
            'sandbox' => [
                'client_id'         => Arr::get($this->paymentMethod->configs, 'sandbox_client_id'),
                'client_secret'     => Arr::get($this->paymentMethod->configs, 'sandbox_secret'),
                //'app_id'            => 'APP-80W284485P519543T',
            ],
            'payment_action' => 'Sale',
            'currency'       => 'USD',
            'notify_url'     => 'https://your-app.com/paypal/notify',
            'locale'         => 'en_US',
            'validate_ssl'   => false,
        ];
        //dd($config);
        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        return $provider;
    }
}
