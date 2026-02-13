<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Methods;

use Illuminate\Http\Request;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Entities\SubscriptionReturnResult;
use Juzaweb\Modules\Subscription\Entities\WebhookResult;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\PlanSubscriptionMethod;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use Psr\Http\Message\StreamInterface;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPal extends SubscriptionDriver implements SubscriptionMethod
{
    protected string $name = 'PayPal';

    protected string $description = 'PayPal payment method for subscriptions';

    public function createPlan(Plan $plan, array $options = []): PlanSubscriptionMethod
    {
        if ($exists = $plan->subscriptionMethods()->where('method', $this->name)->first()) {
            return $exists;
        }

        $product = $this->createProduct([
            'name' => $options['service_name'] ?? $plan->name,
            'description' => $options['service_description'] ?? $plan->description,
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);

        if (isset($product['error']) && $product['error']) {
            throw new SubscriptionException($product['error']['message'] ?? 'Could not create product');
        }

        $servicePlan = $this->getProvider()->createPlan(
            [
                'product_id' => $product['id'],
                'name' => $plan->name,
                'description' => $plan->description,
                'status' => 'ACTIVE',
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => 'MONTH',
                            'interval_count' => 1
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => $plan->price,
                                'currency_code' => 'USD'
                            ]
                        ]
                    ]
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee' => [
                        'value' => '0',
                        'currency_code' => 'USD'
                    ],
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ],
                // 'taxes' => [
                //     'percentage' => '10',
                //     'inclusive' => false
                // ]
            ]
        );

        if (isset($servicePlan['error']) && $servicePlan['error']) {
            throw new SubscriptionException($servicePlan['error']['message'] ?? 'Could not create plan');
        }

        $servicePlan['product_id'] = $product['id'];

        return PlanSubscriptionMethod::create(
            [
                'payment_plan_id' => $servicePlan['id'],
                'plan_id' => $plan->id,
                'method' => $this->name,
                'data' => $servicePlan,
            ]
        );
    }

    public function updatePlan(Plan $plan)
    {
        //
    }

    public function subscribe(Plan $plan, array $options = []): SubscribeResult
    {
        $customerName = $options['customer_name'];
        $customerEmail = $options['customer_email'];
        $serviceName = $options['service_name'] ?? $plan->name;
        $serviceDescription = $options['service_description'] ?? $plan->description;
        $returnUrl = $options['return_url'] ?? null;
        $cancelUrl = $options['cancel_url'] ?? null;

        $methodPlan = $this->createPlan(
            $plan,
            [
                'service_name' => $serviceName,
                'service_description' => $serviceDescription,
            ]
        );

        $response = $this->getProvider()
            ->addProductById($methodPlan->data['product_id'])
            ->addBillingPlanById($methodPlan->payment_plan_id)
            ->setReturnAndCancelUrl($returnUrl, $cancelUrl)
            ->setupSubscription($customerName, $customerEmail);

        $redirectUrl = collect($response['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        return SubscribeResult::make($response['id'], $redirectUrl, $response);
    }

    public function complete(SubscriptionHistory $history, array $data): SubscriptionReturnResult
    {
        $provider = $this->getProvider();

        $subscription = $provider->showSubscriptionDetails($history->agreement_id);

        return SubscriptionReturnResult::make($history->agreement_id, $data)
            ->setSubscriptionHistory($history)
            ->setSuccessful($subscription['status'] === 'ACTIVE');
    }

    public function webhook(Request $request): ?WebhookResult
    {
        $provider = $this->getProvider();
        $webhookId = $this->getConfigInMode('webhook_id');

        $json = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => $webhookId,
            'webhook_event' => json_decode($request->getContent(), true),
        ];

        $response = $provider->verifyWebHook($json);

        if (!isset($response['verification_status']) || $response['verification_status'] !== 'SUCCESS') {
            $this->getLogger()->error(
                'Invalid webhook signature',
                [
                    'driver' => $this->name,
                    'request' => $json,
                    'response' => $response,
                ]
            );
            throw new SubscriptionException('Invalid webhook signature');
        }

        $eventType = $request->input('event_type');

        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                $state = strtolower($request->input('resource.state'));

                $status = match ($state) {
                    'completed' => 'completed',
                    'suspended', 'expired' => 'suspended',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                };

                $transactionId = $request->input('resource.billing_agreement_id')
                    ?? $request->input('resource.id');

                return WebhookResult::make($transactionId, $status, $request->all())
                    ->setSuccessful($state === 'completed');
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    $status = $eventType === 'BILLING.SUBSCRIPTION.CANCELLED' ? 'cancelled' : 'suspended';
                    $transactionId = $request->input('resource.id');

                    return WebhookResult::make($transactionId, $status, $request->all())
                        ->setSuccessful(false);
        }

        return null;
    }

    public function createProduct(array $data): array|StreamInterface|string
    {
        return $this->getProvider()->createProduct($data);
    }

    public function getConfigs(): array
    {
        return [
            'client_id' => __('Client ID'),
            'secret' => __('Secret'),
            'webhook_id' => __('Webhook ID'),
        ];
    }

    public function sandbox(bool $sandbox = true): static
    {
        $this->config['sandbox'] = $sandbox;

        return $this;
    }

    protected function getProvider(): PayPalClient
    {
        $provider = new PayPalClient($this->getPaypalSettings());

        $paypalToken = $provider->getAccessToken();
        $provider->setAccessToken($paypalToken);

        return $provider;
    }

    protected function getPaypalSettings(): array
    {
        return [
            'mode' => $this->config('sandbox') ? 'sandbox' : 'live',
            'live' => [
                'client_id' => $this->config('client_id'),
                'client_secret' => $this->config('secret'),
                'app_id' => $this->config('app_id'),
            ],
            'sandbox' => [
                'client_id' => $this->config('sandbox_client_id'),
                'client_secret' => $this->config('sandbox_secret'),
                'app_id' => $this->config('sandbox_app_id'),
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            'notify_url' => route('subscription.webhook', [$this->name]),
            'locale' => 'en_US',
            'validate_ssl' => true,
        ];
    }
}
