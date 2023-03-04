<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;

/**
 * Juzaweb\Subscription\Models\PaymentMethod
 *
 * @property int $id
 * @property string $name
 * @property string $method
 * @property array|null $configs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereConfigs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $description
 * @property string $module
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereFilter($params = [])
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMethod whereModule($value)
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
