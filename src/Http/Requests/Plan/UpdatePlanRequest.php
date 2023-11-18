<?php

namespace Juzaweb\Subscription\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Juzaweb\Subscription\Models\Plan;

class UpdatePlanRequest extends FormRequest
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
                Rule::modelExists(Plan::class),
            ],
        ];
    }
}
