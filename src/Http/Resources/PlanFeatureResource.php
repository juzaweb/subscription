<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Juzaweb\Subscription\Models\PlanFeature;

/**
 * @property PlanFeature $resource
 */
class PlanFeatureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'value' => $this->resource->value,
            'feature_key' => $this->resource->feature_key,
        ];
    }
}
