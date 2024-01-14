<?php

namespace Juzaweb\Subscription\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\CMS\Models\Model;
use Juzaweb\CMS\Models\User;
use Juzaweb\CMS\Traits\ResourceModel;
use Juzaweb\CMS\Traits\UseUUIDColumn;
use Juzaweb\Network\Traits\Networkable;

class ModuleSubscription extends Model
{
    use ResourceModel, UseUUIDColumn, Networkable;

    protected $table = 'subscription_module_subscriptions';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPEND = 'suspend';
    public const STATUS_CANCEL = 'cancel';

    protected $fillable = [
        'register_by',
        'module_id',
        'module_type',
        'agreement_id',
        'amount',
        'start_date',
        'end_date',
        'method_id',
        'plan_id',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function activeSubscription(): void
    {
        $expirationDate = now()->addMonth()->format('Y-m-d 23:59:59');
        $this->setAttribute('start_date', now());
        $this->setAttribute('end_date', $expirationDate);
        $this->setAttribute('status', self::STATUS_ACTIVE);
        $this->save();
    }
}
