<?php

namespace Juzaweb\Modules\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *  schema="SubscriptionResource",
 *  title="SubscriptionResource",
 *
 *  @OA\Property(property="id", type="string", example="99a5e8aa-534a-4467-9c98-107f9c21b96d"),
 *  @OA\Property(property="plan_id", type="string", example="99a5e8aa-534a-4467-9c98-107f9c21b96d"),
 *  @OA\Property(property="method_id", type="string", example="99a5e8aa-534a-4467-9c98-107f9c21b96d"),
 *  @OA\Property(property="amount", type="number", example=9.99),
 *  @OA\Property(property="start_date", type="string", format="date-time", example="2023-10-10 10:10:10"),
 *  @OA\Property(property="end_date", type="string", format="date-time", example="2023-11-10 10:10:10"),
 *  @OA\Property(property="status", type="string", example="active"),
 *  @OA\Property(property="plan", ref="#/components/schemas/PlanResource"),
 *  @OA\Property(property="method", ref="#/components/schemas/SubscriptionMethodResource")
 * )
 */
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
