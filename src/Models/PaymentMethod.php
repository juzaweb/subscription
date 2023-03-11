<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Builder;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;

/**
 * Juzaweb\Subscription\Models\PaymentMethod
 *
 * @property int $id
 * @property string $name
 * @property string $method
 * @property string|null $description
 * @property array|null $configs
 * @property string $module
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod newModelQuery()
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod newQuery()
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod query()
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereConfigs($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereCreatedAt($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereDescription($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereFilter($params = [])
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereId($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereMethod($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereModule($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereName($value)
 * @method static Builder|\Juzaweb\Subscription\Models\PaymentMethod whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentMethod extends Model
{
    use ResourceModel;

    protected $table = 'subscription_payment_methods';

    protected $fillable = [
        'configs',
        'method',
        'name',
        'module',
    ];

    protected $casts = [
        'configs' => 'array',
    ];
}
