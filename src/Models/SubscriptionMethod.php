<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class SubscriptionMethod extends Model
{
    use HasAPI;

    protected $table = 'subscription_methods';

    protected $fillable = [
        'name',
        'driver',
        'description',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];


}
