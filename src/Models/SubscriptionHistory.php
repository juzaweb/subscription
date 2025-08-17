<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class SubscriptionHistory extends Model
{
    use HasAPI;

    protected $table = 'subscription_histories';

    protected $fillable = [];
}
