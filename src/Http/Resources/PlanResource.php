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
use Juzaweb\Subscription\Models\Plan;

/**
 * @property-read Plan $resource
 */
class PlanResource extends JsonResource
{
    protected bool $withFeatures = true;

    public function withFeatures(bool $withFeatures): static
    {
        $this->withFeatures = $withFeatures;

        return $this;
    }

    public function toArray($request): array
    {
        $data = [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'price' => $this->resource->price,
            'is_free' => $this->resource->is_free,
            'created_at' => jw_date_format($this->resource->created_at),
            'updated_at' => jw_date_format($this->resource->updated_at),
        ];

        if ($this->withFeatures) {
            $data['features'] = PlanFeatureResource::collection($this->resource->features)
                ->toResponse($request)->getData(true)['data'];
        }

        return $data;
    }
}
