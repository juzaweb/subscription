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

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Juzaweb\Core\Application;
use Juzaweb\Core\Models\User;
use Juzaweb\Modules\Payment\Exceptions\PaymentException;
use Juzaweb\Modules\Subscription\Contracts\Subscription;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionModule;
use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Enums\SubscriptionHistoryStatus;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod as PaymentMethod;

class SubscriptionManager implements Subscription
{
    protected array $drivers = [];

    protected array $modules = [];

    public function __construct(Application $app)
    {
    }

    public function create(
        User $user,
        string $module,
        Plan $plan,
        PaymentMethod $method,
        array $params = [],
        bool $sandbox = false
    ): SubscribeResult {
        $subscription = $this->driver($method->driver)
            ->setConfigs($method->config)
            ->sandbox($sandbox);

        $handler = $this->module($module);

        $history = SubscriptionHistory::create(
            [
                'method' => $method->driver,
                'module' => $module,
                'amount' => $plan->price,
                'method_id' => $method->id,
                'plan_id' => $plan->id,
                'user_id' => $user->id,
            ]
        );

        $subscribe = $subscription->subscribe($plan, [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'service_name' => $handler->getServiceName(),
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

    public function complete()
    {

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
}
