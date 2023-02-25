<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\UUIDPrimaryKey;

class PaymentMethod extends Model
{
    use UUIDPrimaryKey;

    protected $table = 'membership_payment_methods';

    protected $fillable = [
        'name',
        'configs'
    ];

    protected $casts = [
        'configs' => 'array'
    ];
}
