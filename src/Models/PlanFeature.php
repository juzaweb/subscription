<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class PlanFeature extends Model
{
    use HasAPI;

    protected $table = 'plan_features';

    protected $fillable = [];
}
