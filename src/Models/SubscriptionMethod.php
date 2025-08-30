<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;
use Juzaweb\Core\Traits\Translatable;

class SubscriptionMethod extends Model
{
    use HasAPI, Translatable, HasUuids;

    protected $table = 'subscription_methods';

    protected $fillable = [
        'driver',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public $translatedAttributes = [
        'name',
        'description',
    ];

    protected $hidden = [
        'config',
    ];

    public function getConfig(?string $key = null, $default = null): null|array|string
    {
        if (is_null($key)) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }
}
