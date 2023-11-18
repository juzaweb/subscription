<?php

namespace Juzaweb\Subscription\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PaymentMethod newModelQuery()
 * @method static Builder|PaymentMethod newQuery()
 * @method static Builder|PaymentMethod query()
 * @method static Builder|PaymentMethod whereConfigs($value)
 * @method static Builder|PaymentMethod whereCreatedAt($value)
 * @method static Builder|PaymentMethod whereDescription($value)
 * @method static Builder|PaymentMethod whereFilter($params = [])
 * @method static Builder|PaymentMethod whereId($value)
 * @method static Builder|PaymentMethod whereMethod($value)
 * @method static Builder|PaymentMethod whereModule($value)
 * @method static Builder|PaymentMethod whereName($value)
 * @method static Builder|PaymentMethod whereUpdatedAt($value)
 * @mixin Eloquent
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
        'metas',
    ];

    protected $casts = [
        'configs' => 'array',
        'metas' => 'array',
    ];
}
