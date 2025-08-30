<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;
use Juzaweb\Core\Traits\Translatable;
use Juzaweb\Modules\Subscription\Enums\DurationUnit;
use Juzaweb\Modules\Subscription\Enums\PlanStatus;
use Juzaweb\Translations\Contracts\Translatable as TranslatableContract;

class Plan extends Model implements TranslatableContract
{
    use HasAPI, Translatable, HasUuids;

    protected $table = 'plans';

    protected $fillable = [
        'price',
        'is_free',
        'status',
        'module',
        'duration',
        'duration_unit',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
        'duration' => 'integer',
        'duration_unit' => DurationUnit::class,
        'status' => PlanStatus::class,
    ];

    public $translatedAttributes = [
        'name',
        'description',
    ];

    public function subscriptionMethods(): HasMany
    {
        return $this->hasMany(PlanSubscriptionMethod::class, 'plan_id');
    }
}
