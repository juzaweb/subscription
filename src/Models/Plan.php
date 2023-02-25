<?php

namespace Juzaweb\Subscription\Models;

use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\CMS\Traits\UUIDPrimaryKey;

class Plan extends Model
{
    use ResourceModel, UUIDPrimaryKey;

    protected $table = 'membership_plans';

    protected $fillable = [
        'name',
        'price',
    ];

    protected $casts = [
        'price' => 'float'
    ];
}
