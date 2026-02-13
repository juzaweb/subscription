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

use Illuminate\Support\Facades\Log;

abstract class SubscriptionDriver
{
    /**
     * The name of the subscription method.
     *
     * @var string
     */
    protected string $name;

    /**
     * The description of the subscription method.
     *
     * @var string
     */
    protected string $description;

    protected array $config;

    protected bool $hasSandbox = true;

    protected bool $returnInEmbed = false;

    /**
     * Get the configuration options for the subscription method.
     *
     * @param  string  $key
     * @return array
     */
    public function config(string $key): string|int|null
    {
        return $this->config[$key] ?? null;
    }

    /**
     * Get the configuration value for a specific key.
     *
     * @param string $key
     * @return array
     */
    abstract public function getConfigs(): array;

    /**
     * Set the configuration options for the subscription method.
     *
     * @param  array  $config
     * @return $this
     */
    public function setConfigs(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the name of the subscription method.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the description of the subscription method.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Determine if the subscription method has a sandbox mode.
     *
     * @return bool
     */
    public function hasSandbox(): bool
    {
        return $this->hasSandbox;
    }

    public function isReturnInEmbed(): bool
    {
        return $this->returnInEmbed;
    }

    protected function getConfigInMode(string $key): array|int|string|null
    {
        return $this->config('sandbox')
            ? $this->config("sandbox_{$key}")
            : $this->config($key);
    }

    protected function getLogger()
    {
        return Log::driver('subscription');
    }
}
