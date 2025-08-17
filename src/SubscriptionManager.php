<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Juzaweb\Modules\Subscription\Contracts\Subscription;

class SubscriptionManager implements Subscription
{
    protected array $drivers = [];

    protected array $modules = [];

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

    public function driver(string $name)
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
}
