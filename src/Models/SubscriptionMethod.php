<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;
use Juzaweb\Modules\Core\Traits\Translatable;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod as SubscriptionMethodContract;
use Juzaweb\Modules\Subscription\Facades\Subscription;
use Juzaweb\Modules\Subscription\Http\Resources\SubscriptionMethodResource;

class SubscriptionMethod extends Model
{
    use HasAPI, HasUuids, Translatable;

    protected $table = 'subscription_methods';

    protected $fillable = [
        'driver',
        'config',
        'active',
    ];

    protected $casts = [
        'config' => 'array',
        'active' => 'boolean',
    ];

    public $translatedAttributes = [
        'name',
        'description',
        'locale',
    ];

    protected $hidden = [
        'config',
    ];

    public static function getResource(): string
    {
        return SubscriptionMethodResource::class;
    }

    public function paymentDriver(): SubscriptionMethodContract
    {
        return Subscription::driver($this->driver)
            ->setConfigs($this->config);
    }

    public function getConfig(?string $key = null, $default = null): null|array|string
    {
        if (is_null($key)) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }
}
