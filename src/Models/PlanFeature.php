<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Juzaweb\CMS\Models\Model;

/**
 * Juzaweb\Subscription\Models\PlanFeature
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $plan_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $value
 * @property string|null $feature_key
 * @property-read Plan $plan
 * @method static Builder|PlanFeature newModelQuery()
 * @method static Builder|PlanFeature newQuery()
 * @method static Builder|PlanFeature query()
 * @method static Builder|PlanFeature whereCreatedAt($value)
 * @method static Builder|PlanFeature whereDescription($value)
 * @method static Builder|PlanFeature whereFeatureKey($value)
 * @method static Builder|PlanFeature whereId($value)
 * @method static Builder|PlanFeature wherePlanId($value)
 * @method static Builder|PlanFeature whereTitle($value)
 * @method static Builder|PlanFeature whereUpdatedAt($value)
 * @method static Builder|PlanFeature whereValue($value)
 * @mixin Eloquent
 */
class PlanFeature extends Model
{
    protected $table = 'subscription_plan_features';

    protected $fillable = [
        'plan_id',
        'title',
        'description',
        'feature_key',
        'value',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }
}
