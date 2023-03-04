<?php

namespace Juzaweb\Subscription\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Juzaweb\Subscription\Models\Plan;

class PaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::modelExists(Plan::class, 'id', fn($q) => $q->whereIsActive())
            ],
            'method' => [
                'required',
                'string',
            ],
            'module' => [
                'required',
                'string',
            ],
        ];
    }
}
