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

use Illuminate\Http\Resources\Json\ResourceCollection;

class PlanCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return $this->collection->map(
            fn ($plan) => [
                'id' => $plan->id,
                'uuid' => $plan->uuid,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'is_free' => $plan->is_free,
                'created_at' => jw_date_format($plan->created_at),
                'updated_at' => jw_date_format($plan->updated_at),
            ]
        );
    }
}
