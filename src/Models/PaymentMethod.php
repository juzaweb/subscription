<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;

class PaymentMethod extends Model
{
    protected $table = 'membership_payment_methods';

    protected $fillable = [
        'name',
        'configs'
    ];

    protected $casts = [
        'configs' => 'array'
    ];
}
