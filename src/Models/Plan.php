<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;

class Plan extends Model
{
    use ResourceModel;

    protected $table = 'membership_plans';

    protected $fillable = [
        'name',
        'price',
    ];

    protected $casts = [
        'price' => 'float'
    ];
}
