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

use Illuminate\Support\Collection;

interface Subscription
{
    public function modules(): Collection;

    public function module(string $name);

    public function registerDriver(string $name, callable $resolver): void;

    public function driver(string $name);

    public function drivers(): Collection;

    public function registerModule(string $name, callable $resolver): void;

    public function renderConfig(string $driver, array $config = []): string;
}
