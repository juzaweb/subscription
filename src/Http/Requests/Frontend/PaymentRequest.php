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
                'required',
            ],
            'cancel_url' => [
                'required',
            ],
        ];
    }
}
