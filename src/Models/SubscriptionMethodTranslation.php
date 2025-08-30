<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\Core\Models\Model;

class SubscriptionMethodTranslation extends Model
{
    protected $table = 'subscription_method_translations';

    protected $fillable = [
        'name',
        'description',
    ];

    public function subscriptionMethod(): BelongsTo
    {
        return $this->belongsTo(SubscriptionMethod::class, 'subscription_method_id');
    }
}
