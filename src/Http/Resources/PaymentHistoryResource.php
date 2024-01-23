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

class PaymentHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'token' => $this->resource->token,
            'method' => $this->resource->method,
            'module' => $this->resource->module,
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'plan' => PlanResource::make($this->resource->plan)->withFeatures(false)->response()->getData(true)['data'],
            'created_at' => jw_date_format($this->resource->created_at),
            'updated_at' => jw_date_format($this->resource->updated_at),
        ];
    }
}
