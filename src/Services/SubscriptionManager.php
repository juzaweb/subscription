<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Juzaweb\Modules\Core\Application;
use Juzaweb\Modules\Core\Models\Authenticatable;
use Juzaweb\Modules\Payment\Exceptions\PaymentException;
use Juzaweb\Modules\Subscription\Contracts\Subscriptable;
use Juzaweb\Modules\Subscription\Contracts\Subscription;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionModule;
use Juzaweb\Modules\Subscription\Entities\Feature;
use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Entities\SubscriptionReturnResult;
use Juzaweb\Modules\Subscription\Enums\SubscriptionHistoryStatus;
use Juzaweb\Modules\Subscription\Enums\SubscriptionStatus;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\Subscription as SubscriptionModel;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod as PaymentMethod;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod as SubscriptionMethodModel;

class SubscriptionManager implements Subscription
{
    protected array $drivers = [];

    protected array $modules = [];

    protected array $features = [];

    public function __construct(Application $app)
    {
    }

    public function feature(string $key, string $module, callable $callback): void
    {
        $this->features[$module][$key] = $callback;
    }

    public function create(
        Authenticatable $user,
        Subscriptable $subscriptable,
        string $module,
        Plan $plan,
        PaymentMethod $method,
        array $params = []
    ): SubscribeResult {
        $sandbox = setting('subscription_sandbox', true);
        $subscription = $this->driver($method->driver)
            ->setConfigs($method->config)
            ->sandbox($sandbox);

        $handler = $this->module($module);

        $history = new SubscriptionHistory();
        $history->billable()->associate($subscriptable);
        $history->fill(
            [
                'driver' => $method->driver,
                'module' => $module,
                'amount' => $plan->price,
                'method_id' => $method->id,
                'plan_id' => $plan->id,
            ]
        );
        $history->save();

        $subscribe = $subscription->subscribe($plan, [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'service_name' => $handler->getServiceName(),
            'service_description' => $handler->getServiceDescription(),
            'return_url' => route('subscription.return', [$module, $history->id]),
            'cancel_url' => route('subscription.cancel', [$module, $history->id]),
        ]);

        $history->update(['agreement_id' => $subscribe->getTransactionId()]);
        $subscribe->setSubscriptionHistory($history);

        if ($subscribe->isSuccessful()) {
            $history->update(['status' => SubscriptionHistoryStatus::SUCCESS]);

            $subscribe->setSubscriptionHistory($history);

            $handler->onSuccess($subscribe, $params);
        }

        return $subscribe;
    }

    public function complete(SubscriptionHistory $history, array $params = []): SubscriptionReturnResult
    {
        $sandbox = $this->sandboxMode();

        $subscription = $this->driver($history->driver)
            ->setConfigs($history->method->config)
            ->sandbox($sandbox);

        $complete = $subscription->complete($history, $params);

        if ($complete->isSuccessful()) {
            $history->update([
                'status' => SubscriptionHistoryStatus::SUCCESS,
                'end_date' => now()->addMonth(),
            ]);

            $complete->setSubscriptionHistory($history);

            $handler = $this->module($history->module);
            $handler->onSuccess($complete, $params);

            SubscriptionModel::create(
                [
                    'driver' => $history->driver,
                    'module' => $history->module,
                    'amount' => $history->amount,
                    'agreement_id' => $history->agreement_id,
                    'start_date' => now(),
                    'end_date' => $history->end_date,
                    'method_id' => $history->method_id,
                    'plan_id' => $history->plan_id,
                    'billable_id' => $history->billable_id,
                    'billable_type' => $history->billable_type,
                    'status' => SubscriptionStatus::ACTIVE,
                ]
            );
        } else {
            $history->update(['status' => SubscriptionHistoryStatus::FAILED]);
        }

        return $complete;
    }

    public function cancel(SubscriptionHistory $history, array $params = []): true
    {
        $history->update(['status' => SubscriptionHistoryStatus::CANCELLED]);

        $handler = $this->module($history->module);

        $handler->onCancel($history, $params);

        return true;
    }

    public function webhook(Request $request, string $driver): void
    {
        $method = SubscriptionMethodModel::where('driver', $driver)->first();
        $subscription = $this->driver($driver)
            ->setConfigs($method->config)
            ->sandbox($this->sandboxMode());

        $result = $subscription->webhook($request);

        if ($result === null) {
            $this->logger()->info(
                "Webhook for driver [$driver] return null: ",
                [
                    'driver' => $driver,
                    'payload' => $request->all(),
                ]
            );
            return;
        }

        if ($result->isSuccessful()) {
            $agreement = SubscriptionModel::where('agreement_id', $result->getTransactionId())
                ->where('driver', $driver)
                ->first();
            $history = SubscriptionHistory::where('agreement_id', $result->getTransactionId())
                ->where('driver', $driver)
                ->first();

            if ($agreement) {
                $agreement->update([
                    'status' => SubscriptionStatus::ACTIVE,
                    'end_date' => now()->addMonth()->endOfDay(),
                ]);
            } else {
                $newSubscription = SubscriptionModel::create(
                    [
                        'driver' => $history->driver,
                        'module' => $history->module,
                        'amount' => $history->amount,
                        'agreement_id' => $history->agreement_id,
                        'start_date' => now(),
                        'end_date' => now()->addMonth()->endOfDay(),
                        'method_id' => $history->method_id,
                        'plan_id' => $history->plan_id,
                        'billable_id' => $history->billable_id,
                        'billable_type' => $history->billable_type,
                        'status' => SubscriptionStatus::ACTIVE,
                    ]
                );

                $history->update([
                    'status' => SubscriptionHistoryStatus::SUCCESS,
                    'end_date' => now()->addMonth()->endOfDay(),
                    'subscription_id' => $newSubscription->id,
                ]);
            }

            $result->setSubscriptionHistory($history);
            $handler = $this->module($result->getSubscriptionHistory()->module);
            $handler->onSuccess($result, $request->all());
            return;
        }

        $this->logger()->info(
            "Webhook for driver [$driver] none success: ",
            [
                'driver' => $driver,
                'payload' => $request->all(),
                'result' => [
                    'transaction_id' => $result->getTransactionId(),
                    'status' => $result->getStatus(),
                ]
            ]
        );
    }

    public function modules(): Collection
    {
        return collect($this->modules)->map(
            function ($resolver) {
                return $resolver();
            }
        );
    }

    public function module(string $name): SubscriptionModule
    {
        if (!isset($this->modules[$name])) {
            throw new InvalidArgumentException("Payment module [$name] is not registered.");
        }

        return $this->modules[$name]();
    }

    public function hasModule(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    public function driver(string $name): SubscriptionMethod
    {
        if (!isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Payment driver [$name] is not registered.");
        }

        return $this->drivers[$name]();
    }

    public function drivers(): Collection
    {
        return collect($this->drivers)->map(function ($resolver) {
            return $resolver();
        });
    }

    public function features(string $module): Collection
    {
        return collect($this->features[$module] ?? [])
            ->map(
                function ($resolver, $key) {
                    return new Feature($key, $resolver());
                }
            );
    }

    public function registerDriver(string $name, callable $resolver): void
    {
        if (isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Payment driver [$name] already registered.");
        }

        $this->drivers[$name] = $resolver;
    }

    public function registerModule(string $name, callable $resolver): void
    {
        if (isset($this->modules[$name])) {
            throw new InvalidArgumentException("Payment module [$name] already registered.");
        }

        $this->modules[$name] = $resolver;
    }

    public function renderConfig(string $driver, array $config = []): string
    {
        $fields = $this->driver($driver)->getConfigs();
        $hasSandbox = $this->driver($driver)->hasSandbox();

        if (empty($fields)) {
            throw new PaymentException("Subscription driver [$driver] has no configuration.");
        }

        return view(
            'subscription::method.components.config',
            ['fields' => $fields, 'config' => $config, 'hasSandbox' => $hasSandbox]
        )->render();
    }

    protected function logger()
    {
        return Log::driver('subscription');
    }

    protected function sandboxMode(): bool
    {
        return (bool) setting('subscription_sandbox', true);
    }
}
