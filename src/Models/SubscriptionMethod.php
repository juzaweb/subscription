<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;
use Juzaweb\Core\Traits\Translatable;

class SubscriptionMethod extends Model
{
    use HasAPI, Translatable;

    protected $table = 'subscription_methods';

    protected $fillable = [
        'driver',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    protected $appends = [
        'sandbox',
    ];

    public $translatedAttributes = [
        'name',
        'description',
    ];

    protected $hidden = [
        'config',
    ];

    public function getSandboxAttribute(): bool
    {
        return (bool) $this->getConfig('sandbox', false);
    }

    public function getConfig(?string $key = null, $default = null): null|array|string
    {
        if (is_null($key)) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }
}
