<?php

namespace Juzaweb\Subscription\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

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
            'return_url' => [
                'nullable',
                'string',
            ],
            'cancel_url' => [
                'nullable',
                'string',
            ],
            'plan' => [
                'required',
                'exists:subscription_plans,uuid',
            ],
            'method' => [
                'required',
                'exists:subscription_payment_methods,method',
            ],
            'id' => [
                'required',
                'string',
            ],
        ];
    }
}
