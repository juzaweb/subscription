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
use Juzaweb\Subscription\Models\Plan as PlanModel;
use Juzaweb\Subscription\Models\PlanPaymentMethod;
use Juzaweb\Subscription\Support\Entities\CreatedPlanResult;
use Juzaweb\Subscription\Support\Entities\SubscribeResult;

class Stripe extends PaymentMethodAbstract implements PaymentMethod
{
    protected string $name = 'stripe';
    protected bool $isRedirect = false;

    public function subscribe(PlanModel $plan, PlanPaymentMethod $planPaymentMethod, Request $request): SubscribeResult
    {
        $paymentMethod = $user->createSetupIntent()->client_secret;

        // Process the payment
        $subscription = $user->newSubscription('default', 'plan_ID')->create($request->stripeToken);
    }

    public function createPlan(PlanModel $plan): CreatedPlanResult
    {
        $plan = \Stripe\Plan::create(
            [
                'product' => [
                    'name' => 'Monthly Subscription'
                ],
                'currency' => 'usd',
                'interval' => 'month',
                'price' => $plan->price,
            ]
        );
    }

    public function updatePlan(PlanModel $plan, PlanPaymentMethod $planPaymentMethod): string
    {
        // TODO: Implement updatePlan() method.
    }

    public function webhook(Request $request): bool|PaymentResult
    {
        // TODO: Implement webhook() method.
    }

    public function getConfigs(): array
    {
        // TODO: Implement getConfigs() method.
    }

    public function return(PlanModel $plan, array $data): PaymentResult
    {
        // TODO: Implement return() method.
    }
}
