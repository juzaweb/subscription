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
use Juzaweb\Modules\Subscription\Facades\Subscription;

class SubscriptionMethodRequest extends FormRequest
{
    public function rules(): array
    {
        $drivers = Subscription::drivers()->keys()->toArray();
        $locale = $this->get('locale', config('translatable.fallback_locale'));

        return [
			'driver' => [Rule::requiredIf(! $this->route('id')), 'string', Rule::in($drivers)],
			'config' => ['required', 'array'],
            "{$locale}.name" => ['required', 'string', 'max:255'],
            "{$locale}.description" => ['nullable', 'string'],
		];
    }
}
