<?php

namespace Juzaweb\Modules\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'price' => $this->resource->price,
            'is_free' => $this->resource->is_free,
            'duration' => $this->resource->duration,
            'duration_unit' => $this->resource->duration_unit,
            'features' => $this->resource->relationLoaded('features') ?
                $this->resource->features->map(function ($feature) {
                    return [
                        'name' => $feature->name,
                        'value' => $feature->value,
                    ];
                }) : [],
            'active' => $this->resource->active,
        ];
    }
}
