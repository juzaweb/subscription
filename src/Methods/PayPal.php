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
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\PlanSubscriptionMethod;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPal extends SubscriptionDriver
{
    protected string $name = 'PayPal';

    protected string $description = 'PayPal payment method for subscriptions';

    public function createPlan(Plan $plan, array $options = []): PlanSubscriptionMethod
    {
        $product = $this->createProduct([
            'name' => $options['product_name'] ?? $plan->name,
            'description' => $options['product_description'] ?? $plan->description,
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

        return PlanSubscriptionMethod::create(
            [
                'payment_plan_id' => $servicePlan['id'],
                'plan_id' => $plan->id,
                'method' => $this->name,
                'data' => $servicePlan,
            ]
        );
    }

    public function updatePlan(Plan $plan, PlanPaymentMethod $planPaymentMethod): string
    {
        //
    }

    public function subscribe(Plan $plan, PlanPaymentMethod $planPaymentMethod): bool
    {
        $response = $this->getProvider()
            ->addProductById($serviceName, $serviceDescription, 'SERVICE', 'SOFTWARE')
            ->addBillingPlanById($plan->name, $plan->description ?? '', 100)
            ->setReturnAndCancelUrl(
                'https://example.com/paypal-success',
                'https://example.com/paypal-cancel'
            )
            ->setupSubscription('John Doe', 'john@example.com', '2021-12-10');
    }

    public function complete(Plan $plan, array $data): PaymentReturnResult
    {

    }

    public function webhook(Request $request): bool|PaymentReturnResult
    {

    }

    public function createProduct(array $data)
    {
        return $this->getProvider()->createProduct($data);
    }

    public function config(): array
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
            'mode' => $this->getConfig('sandbox') ? 'sandbox' : 'live',
            'live' => [
                'client_id' => $this->getConfig('client_id'),
                'client_secret' => $this->getConfig('client_secret'),
                'app_id' => $this->getConfig('app_id'),
            ],
            'sandbox' => [
                'client_id' => $this->getConfig('client_id'),
                'client_secret' => $this->getConfig('client_secret'),
                'app_id' => $this->getConfig('app_id'),
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            'notify_url' => 'https://your-app.com/paypal/notify',
            'locale' => 'en_US',
            'validate_ssl' => true,
        ];
    }
}
