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
use Illuminate\Support\Facades\Log;
use Juzaweb\CMS\Models\User;
use Juzaweb\Subscription\Abstracts\PaymentMethodAbstract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Contrasts\PaymentReturnResult;
use Juzaweb\Subscription\Exceptions\PaymentException;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Juzaweb\Subscription\Models\UserSubscription;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Paypal extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'paypal';

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        $response = $this->getProvider()
            ->addProduct($plan->name, $plan->description ?? "Subscription plan {$plan->name}", 'SERVICE', 'SOFTWARE')
            //->addPlanTrialPricing('DAY', 7)
            ->addMonthlyPlan('Monthly Subscription', 'Monthly Subscription Plan', $plan->price)
            ->setReturnAndCancelUrl($this->getReturnUrl($plan), $this->getCancelUrl($plan))
            ->setupSubscription($user->name, $user->email, now()->addMinutes(2));

        $approveLink = Arr::get($response, 'links.0.href');

        $this->setRedirectUrl($approveLink);

        return true;
    }

    public function webhook(Request $request): bool|PaymentReturnResult
    {
        $resource = $request->input('resource');
        $eventType = $request->input('event_type');
        $amount = Arr::get($resource, 'agreement_details.last_payment_amount.value');
        $provider = $this->getProvider();

        if (!$this->verifyWebhook($provider, $request)) {
            throw new PaymentException("Event {$eventType} Webhook Signature Invalid.");
        }

        $handleEvents = [
            'PAYMENT.SALE.COMPLETED',
            'BILLING.SUBSCRIPTION.CANCELLED',
            'BILLING.SUBSCRIPTION.SUSPENDED'
        ];

        if (!in_array($eventType, $handleEvents)) {
            return false;
        }

        $status = match ($eventType) {
            'PAYMENT.SALE.COMPLETED' => UserSubscription::STATUS_ACTIVE,
            'BILLING.SUBSCRIPTION.CANCELLED' => UserSubscription::STATUS_CANCEL,
            default => UserSubscription::STATUS_SUSPEND,
        };

        return $this->makePaymentReturnResult(
            Arr::get($resource, 'billing_agreement_id'),
            $amount,
            $request->input('id'),
            $status
        );
    }

    public function return(PlanModel $plan, array $data): ?PaymentReturnResult
    {
        $provider = $this->getProvider();

        $response = $provider->showSubscriptionDetails($data['subscription_id']);

        $status = Arr::get($response, 'status') == 'ACTIVE'
            ? UserSubscription::STATUS_ACTIVE
            : UserSubscription::STATUS_CANCEL;

        return $this->makePaymentReturnResult(
            Arr::get($response, 'id'),
            Arr::get($response, 'billing_info.last_payment.amount.value'),
            $data['token'],
            $status
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

    protected function verifyWebhook($provider, Request $request): bool|int
    {
        $payload = file_get_contents('php://input');
        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);

        $transmissionId = $headers['PAYPAL-TRANSMISSION-ID'][0];
        $transmissionSig = $headers['PAYPAL-TRANSMISSION-SIG'][0];
        $transmissionTime = $headers['PAYPAL-TRANSMISSION-TIME'][0];

        $cert_url = $headers['PAYPAL-CERT-URL'][0];
        $cert = file_get_contents($cert_url);

        $signature = base64_decode($transmissionSig);

        // <transmissionId>|<timeStamp>|<webhookId>|<crc32>
        $string_chain = implode(
            '|',
            [
                $transmissionId,
                $transmissionTime,
                $this->getConfigByMod()['webhook_id'],
                crc32($payload),
            ]
        );

        return openssl_verify(
            data: $string_chain,
            signature: $signature,
            public_key: openssl_pkey_get_public(public_key: $cert),
            algorithm: 'sha256WithRSAEncryption'
        );
    }

    protected function verifyWebhook2($provider, Request $request): bool
    {
        $requestBody = json_encode($request->post(), JSON_THROW_ON_ERROR);

        /**
         * In Documentions https://developer.paypal.com/docs/api/webhooks/#verify-webhook-signature_post
         * All header keys as UPPERCASE, but I recive the header key as the example array, First letter as UPPERCASE
         */
        $headers = array_change_key_case($request->headers->all(), CASE_UPPER);

        $verifyData = [
            "transmission_id" => $headers['PAYPAL-TRANSMISSION-ID'][0],
            "transmission_time" => $headers['PAYPAL-TRANSMISSION-TIME'][0],
            "cert_url" => $headers['PAYPAL-CERT-URL'][0],
            "auth_algo" => $headers['PAYPAL-AUTH-ALGO'][0],
            "transmission_sig" => $headers['PAYPAL-TRANSMISSION-SIG'][0],
            "webhook_id" => $this->getConfigByMod()['webhook_id'],
            "webhook_event" => $requestBody,
        ];

        $this->logger()->info('Webhook Verify Data', $verifyData);
        $verifyResponse = $provider->verifyWebHook($verifyData);

        $this->logger()->info('Webhook Verify Response', $verifyResponse);

        return Arr::get($verifyResponse, 'verification_status') != 'SUCCESS';
    }

    protected function logger(): \Psr\Log\LoggerInterface
    {
        return Log::build(
            [
                'driver' => 'daily',
                'path' => storage_path('logs/paypal.log'),
            ]
        );
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
            //'notify_url'     => 'https://your-app.com/paypal/notify',
            'notify_url'     => null,
            'locale'         => 'en_US',
            'validate_ssl'   => false,
        ];
        //dd($config);
        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        return $provider;
    }
}
