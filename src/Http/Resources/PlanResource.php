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

class PlanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'price' => $this->resource->price,
            'is_free' => $this->resource->is_free,
            'created_at' => jw_date_format($this->resource->created_at),
            'updated_at' => jw_date_format($this->resource->updated_at),
        ];
    }
}
