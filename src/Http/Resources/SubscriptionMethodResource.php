<?php

namespace Juzaweb\Modules\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'driver' => $this->resource->driver,
            'active' => $this->resource->active,
        ];
    }
}
