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

    public function __construct(protected array $config)
    {
    }

    /**
     * Get the configuration options for the subscription method.
     *
     * @return array
     */
    abstract public function config(): array;

    /**
     * Get the configuration value for a specific key.
     *
     * @param string $key
     * @return string|int|null
     */
    public function getConfig(string $key): int|string|null
    {
        return $this->config[$key] ?? null;
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
}
