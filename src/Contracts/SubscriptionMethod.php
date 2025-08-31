<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Contracts;

use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Models\Plan;

interface SubscriptionMethod
{
    public function subscribe(Plan $plan, array $options = []): SubscribeResult;

    /**
     * Get the configuration value for a specific key.
     *
     * @param string $key
     * @return array
     */
    public function getConfigs(): array;

    /**
     * Get the configuration options for the subscription method.
     *
     * @param  string  $key
     * @return array
     */
    public function config(string $key): string|int|null;

    /**
     * Set the configuration options for the subscription method.
     *
     * @param  array  $config
     * @return $this
     */
    public function setConfigs(array $config): static;

    /**
     * Get the name of the subscription method.
     *
     * @return bool
     */
    public function hasSandbox(): bool;
}
