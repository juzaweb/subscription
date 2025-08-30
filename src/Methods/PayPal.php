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
use Juzaweb\Modules\Subscription\Entities\SubscriptionReturnResult;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\PlanSubscriptionMethod;
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
                        'sequence' => 3,
                        'total_cycles' => 12,
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

    public function updatePlan(Plan $plan): string
    {
        //
    }

    public function subscribe(Plan $plan, array $options = [])
    {
        $customerName = $options['customer_name'];
        $customerEmail = $options['customer_email'];
        $serviceName = $options['service_name'] ?? $plan->name;
        $serviceDescription = $options['service_description'] ?? $plan->description;

        $methodPlan = $this->createPlan($plan, [
            'service_name' => $serviceName,
            'service_description' => $serviceDescription,
        ]);

        $response = $this->getProvider()
            ->addProductById($methodPlan->data['product_id'])
            ->addBillingPlanById($methodPlan->payment_plan_id)
            ->setReturnAndCancelUrl(
                route('subscription.return', [$this->name]),
                route('subscription.cancel', [$this->name])
            )
            ->setupSubscription($customerName, $customerEmail);

        dd($response);
    }

    public function complete(Plan $plan, array $data): SubscriptionReturnResult
    {

    }

    public function webhook(Request $request): SubscriptionReturnResult
    {

    }

    public function createProduct(array $data)
    {
        return $this->getProvider()->createProduct($data);
    }

    public function getConfigs(): array
    {
        return [
            'clientId' => __('Client ID'),
            'secret' => __('Secret'),
        ];
    }

    protected function getProvider(): PayPalClient
    {
        $provider = new PayPalClient;

        $provider->setApiCredentials($this->getPaypalSettings());

        return $provider;
    }

    protected function getPaypalSettings(): array
    {
        return [
            'mode' => $this->config('sandbox') ? 'sandbox' : 'live',
            'live' => [
                'client_id' => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'app_id' => $this->config('app_id'),
            ],
            'sandbox' => [
                'client_id' => $this->config('sandbox_client_id'),
                'client_secret' => $this->config('sandbox_client_secret'),
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
