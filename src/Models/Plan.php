<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;
use Juzaweb\Modules\Subscription\Enums\DurationUnit;

class Plan extends Model
{
    use HasAPI, HasUuids;

    protected $table = 'plans';

    protected $fillable = [
        'price',
        'is_free',
        'active',
        'module',
        'duration',
        'duration_unit',
        'name',
    ];

    protected $casts = [
        'price' => 'float',
        'is_free' => 'boolean',
        'duration' => 'integer',
        'duration_unit' => DurationUnit::class,
    ];

    public function subscriptionMethods(): HasMany
    {
        return $this->hasMany(PlanSubscriptionMethod::class, 'plan_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function getFeatureValue(string $name)
    {
        return $this->features->firstWhere('name', $name)?->value;
    }
}
