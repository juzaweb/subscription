<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 */

namespace Juzaweb\Modules\Subscription\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_free' => ['required', 'boolean'],
            'price' => [
                Rule::requiredIf(!$this->input('is_free') && $this->isMethod('post')),
                'numeric',
                'min:0',
            ],
            'features' => ['nullable', 'array'],
            'features.*' => ['required'],
        ];
    }
}
