<?php

namespace Juzaweb\Modules\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *  schema="PlanResource",
 *  title="PlanResource",
 *
 *  @OA\Property(property="id", type="string", example="99a5e8aa-534a-4467-9c98-107f9c21b96d"),
 *  @OA\Property(property="name", type="string", example="Premium Plan"),
 *  @OA\Property(property="price", type="number", example=9.99),
 *  @OA\Property(property="is_free", type="boolean", example=false),
 *  @OA\Property(property="duration", type="integer", example=1),
 *  @OA\Property(property="duration_unit", type="string", example="month"),
 *  @OA\Property(property="features", type="array", @OA\Items(
 *      @OA\Property(property="name", type="string"),
 *      @OA\Property(property="value", type="string")
 *  )),
 *  @OA\Property(property="active", type="boolean", example=true)
 * )
 */
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
