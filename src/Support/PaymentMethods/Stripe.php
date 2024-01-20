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
use Juzaweb\Subscription\Abstracts\PaymentMethodAbstract;
use Juzaweb\Subscription\Contrasts\PaymentMethod;
use Juzaweb\Subscription\Contrasts\PaymentResult;
use Juzaweb\Subscription\Models\ModuleSubscription;
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Juzaweb\Subscription\Support\Entities\CreatedPlanResult;
use Juzaweb\Subscription\Support\Entities\SubscribeResult;
use Stripe\Checkout\Session;
use Stripe\Stripe as StripeSdk;
use Stripe\StripeClient;
use Stripe\Webhook;

class Stripe extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'stripe';
    protected bool $isRedirect = false;

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): SubscribeResult
    {
        $this->initStripe();

        $session = Session::create(
            [
                'success_url' => $this->getReturnUrl($plan).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->getCancelUrl($plan),
                'mode' => 'subscription',
                'line_items' => [
                    [
                        'price' => $planPaymentMethod->getMeta('price_id'),
                        'quantity' => 1,
                    ]
                ],
            ]
        );

        return SubscribeResult::make($session->id)->setRedirectUrl($session->url);
    }

    public function createPlan(PlanModel $plan): CreatedPlanResult
    {
        $stripe = new StripeClient($this->paymentMethod->configs['api_key']);

        $product = $stripe->products->create(['name' => 'Basic Dashboard']);

        $price = $stripe->prices->create(
            [
                'product' => $product->id,
                'unit_amount' => $plan->price * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
            ]
        );

        return CreatedPlanResult::make($product->id)->setMeta(['price_id' => $price->id]);
    }

    public function updatePlan(PlanModel $plan, PlanPaymentMethod $planPaymentMethod): string
    {
        $stripe = new StripeClient($this->paymentMethod->configs['api_key']);

        $stripe->plans->delete(
            $planPaymentMethod->getMeta('price_id'),
            []
        );

        $price = $stripe->prices->create(
            [
                'unit_amount' => $plan->price * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => $planPaymentMethod->plan_id,
            ]
        );

        return $price->product->id;
    }

    public function webhook(Request $request): bool|PaymentResult
    {
        $this->initStripe();

        $payload = $request->getContent();
        $sigHeader = $request->headers->get('HTTP_STRIPE_SIGNATURE');
        $webhookSecret = $this->paymentMethod->configs['webhook_secret'];

        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );

        $status = match ($event->type) {
            'invoice.paid' => ModuleSubscription::STATUS_ACTIVE,
            'invoice.payment_failed' => ModuleSubscription::STATUS_SUSPEND,
            default => ModuleSubscription::STATUS_CANCEL,
        };

        return $this->makePaymentReturnResult(
            $event->data->id,
            $event->data->object->amount_paid,
            $event->data->object->customer,
            $status
        );
    }

    public function getConfigs(): array
    {
        return [
            'api_key' => [
                'label' => 'API Key',
            ],
            'webhook_secret' => [
                'label' => 'Webhook Secret',
            ],
        ];
    }

    public function return(PlanModel $plan, array $data): PaymentResult
    {
        $stripe = new StripeClient($this->paymentMethod->configs['api_key']);

        $session = $stripe->checkout->sessions->retrieve($_GET['session_id']);
        $customer = $stripe->customers->retrieve($session->customer);

        return $this->makePaymentReturnResult(
            $session->subscription,
            $customer->balance,
            $data['token']
        );
    }

    protected function initStripe(): void
    {
        StripeSdk::setApiKey($this->paymentMethod->configs['api_key']);
    }
}
