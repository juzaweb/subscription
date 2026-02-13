<?php

namespace Juzaweb\Modules\Subscription\Models;

use Juzaweb\Modules\Core\Models\Model;
use Juzaweb\Modules\Core\Traits\HasAPI;

class PlanFeature extends Model
{
    use HasAPI;

    protected $table = 'plan_features';

    protected $fillable = [
        'plan_id',
        'name',
        'value',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
