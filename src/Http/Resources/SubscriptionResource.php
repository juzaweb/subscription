<?php

namespace Juzaweb\Modules\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'plan_id' => $this->resource->plan_id,
            'method_id' => $this->resource->method_id,
            'amount' => $this->resource->amount,
            'start_date' => $this->resource->start_date,
            'end_date' => $this->resource->end_date,
            'status' => $this->resource->status,
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'method' => new SubscriptionMethodResource($this->whenLoaded('method')),
        ];
    }
}
