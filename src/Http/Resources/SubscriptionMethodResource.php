<?php

namespace Juzaweb\Modules\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *  schema="SubscriptionMethodResource",
 *  title="SubscriptionMethodResource",
 *
 *  @OA\Property(property="id", type="string", example="99a5e8aa-534a-4467-9c98-107f9c21b96d"),
 *  @OA\Property(property="name", type="string", example="Paypal"),
 *  @OA\Property(property="description", type="string", example="Pay with paypal"),
 *  @OA\Property(property="driver", type="string", example="paypal"),
 *  @OA\Property(property="active", type="boolean", example=true)
 * )
 */
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
