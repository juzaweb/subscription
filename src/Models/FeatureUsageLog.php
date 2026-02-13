<?php

namespace Juzaweb\Modules\Subscription\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Juzaweb\Modules\Admin\Models\Website;
use Juzaweb\Modules\Core\Models\Model;

class FeatureUsageLog extends Model
{
    use HasUuids;

    protected $table = 'feature_usage_logs';

    protected $fillable = [
        'feature_name',
        'usage_date',
        'usage_count',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'usage_count' => 'integer',
    ];

    /**
     * Get usage count for a specific feature on a specific date
     */
    public static function getUsageCount(string $featureName, ?\DateTime $date = null): int
    {
        $date = $date ?? now();

        return static::where('feature_name', $featureName)
            ->where('usage_date', $date->format('Y-m-d'))
            ->value('usage_count') ?? 0;
    }

    /**
     * Increment usage count for a specific feature
     */
    public static function incrementUsage(string $featureName, int $increment = 1): void
    {
        $date = now()->format('Y-m-d');

        $existing = static::where('feature_name', $featureName)
            ->where('usage_date', $date)
            ->first();

        if ($existing) {
            $existing->increment('usage_count', $increment);
        } else {
            static::create([
                'feature_name' => $featureName,
                'usage_date' => $date,
                'usage_count' => $increment,
            ]);
        }
    }
}
