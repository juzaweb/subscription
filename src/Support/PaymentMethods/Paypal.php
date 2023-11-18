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
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Juzaweb\Subscription\Models\UserSubscription;
use Juzaweb\Subscription\Support\Entities\CreatedPlanResult;
use Juzaweb\Subscription\Support\Entities\SubscribeResult;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Paypal extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'paypal';

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): SubscribeResult
    {
        /** @var User $user */
        $user = $request->user();

        $response = $this->getProvider()->addProductById($planPaymentMethod->metas['product_id'])
            ->addBillingPlanById($planPaymentMethod->payment_plan_id)
            ->setReturnAndCancelUrl($this->getReturnUrl($plan), $this->getCancelUrl($plan))
            ->setupSubscription($user->name, $user->email, now()->addMinutes(2));

        $approveLink = Arr::get($response, 'links.0.href');

        return SubscribeResult::make(Arr::get($response, 'id'))->setRedirectUrl($approveLink);
    }

    public function webhook(Request $request): bool|PaymentReturnResult
    {
        $resource = $request->input('resource');
        $eventType = $request->input('event_type');
        $amount = $this->getAmountInWebhookResource($resource);
        $provider = $this->getProvider();

        if (!$this->verifyWebhook($provider, $request)) {
            throw new RuntimeException("Event {$eventType} Webhook Signature Invalid.");
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
            Arr::get($resource, 'id'),
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

    public function createPlan(PlanModel $plan): CreatedPlanResult
    {
        // Create product for plan
        $provider = $this->getProvider();

        $product = $provider->createProduct(
            [
                'name' => "{$plan->name} plan monthly subscription",
                'type' => 'DIGITAL',
            ]
        );

        // Create plan
        $planParams = [
            'product_id' => $product['id'],
            'name' => $plan->name,
            'description' => $plan->description ?? "Monthly Subscription {$plan->name} Plan",
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => 'MONTH',
                        'interval_count' => 1,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => $plan->price,
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ]
        ];

        $response = $provider->createPlan($planParams);

        return CreatedPlanResult::make(Arr::get($response, 'id'))->setMeta(['product_id' => $product['id']]);
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

    protected function getAmountInWebhookResource(array $resource)
    {
        $amount = Arr::get($resource, 'amount.total');

        if (empty($amount)) {
            return Arr::get($resource, 'agreement_details.last_payment_amount.value');
        }

        return $amount;
    }

    protected function verifyWebhook2($provider, Request $request): bool|int
    {
        $payload = $request->getContent();
        $headers = $request->headers;

        $transmissionId = $headers->get('PAYPAL-TRANSMISSION-ID');
        $transmissionTime = $headers->get('PAYPAL-TRANSMISSION-TIME');
        $certUrl = $headers->get('PAYPAL-CERT-URL');
        $transmissionSig = $headers->get('PAYPAL-TRANSMISSION-SIG');

        // Check domain cert
        $domain = get_domain_by_url($certUrl);
        if ($domain !== 'paypal.com' && !str_contains($domain, '.paypal.com')) {
            return false;
        }

        $cert = file_get_contents($certUrl);
        $signature = base64_decode($transmissionSig);

        // <transmissionId>|<timeStamp>|<webhookId>|<crc32>
        $stringChain = implode(
            '|',
            [
                $transmissionId,
                $transmissionTime,
                $this->getConfigByMod()['webhook_id'],
                crc32($payload),
            ]
        );

        return openssl_verify(
            data: $stringChain,
            signature: $signature,
            public_key: openssl_pkey_get_public(public_key: $cert),
            algorithm: 'sha256WithRSAEncryption'
        );
    }

    protected function verifyWebhook(PayPalClient $provider, Request $request): bool
    {
        $verifyResponse = $provider->setWebHookID($this->getConfigByMod('webhook_id'))->verifyIPN($request);

        if (isset($verifyResponse['error'])) {
            return false;
        }

        return Arr::get($verifyResponse, 'verification_status') == 'SUCCESS';
    }

    protected function logger(): LoggerInterface
    {
        return Log::build(
            [
                'driver' => 'daily',
                'path' => storage_path('logs/paypal.log'),
            ]
        );
    }

    protected function getConfigByMod(?string $key = null): string|array
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

        $params = ['client_id' => $clientId, 'secret' => $secret, 'webhook_id' => $webhook];

        return $key ? Arr::get($params, $key) : $params;
    }

    protected function getProvider(): PayPalClient
    {
        $config = [
            'mode' => Arr::get($this->paymentMethod->configs, 'mod', 'sandbox'),
            'live' => [
                'client_id' => Arr::get($this->paymentMethod->configs, 'live_client_id'),
                'client_secret' => Arr::get($this->paymentMethod->configs, 'live_secret'),
                //'app_id'            => 'APP-80W284485P519543T',
            ],
            'sandbox' => [
                'client_id' => Arr::get($this->paymentMethod->configs, 'sandbox_client_id'),
                'client_secret' => Arr::get($this->paymentMethod->configs, 'sandbox_secret'),
                //'app_id'            => 'APP-80W284485P519543T',
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            //'notify_url'     => 'https://your-app.com/paypal/notify',
            'notify_url' => null,
            'locale' => 'en_US',
            'validate_ssl' => false,
        ];
        //dd($config);
        $provider = new PayPalClient($config);
        $provider->getAccessToken();
        return $provider;
    }
}
