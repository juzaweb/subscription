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
use Juzaweb\Modules\Payment\Exceptions\PaymentException;
use Juzaweb\Modules\Subscription\Contracts\Subscription;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;

class SubscriptionManager implements Subscription
{
    protected array $drivers = [];

    protected array $modules = [];

    public function __construct(Application $app)
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

    public function module(string $name)
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
