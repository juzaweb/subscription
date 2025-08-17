<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Core\Models\Model;
use Juzaweb\Core\Traits\HasAPI;

class Plan extends Model
{
    use HasAPI;

    protected $table = 'plans';

    protected $fillable = [];
}
