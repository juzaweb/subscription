<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class Subscription extends Model
{
    use HasAPI;

    protected $table = 'subscriptions';

    protected $fillable = [

    ];
}
