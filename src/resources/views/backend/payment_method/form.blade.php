@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model,
    ])

        <input type="hidden" name="module" value="{{ $module }}">

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ trans('cms::app.info') }}</h5>
            </div>
            <div class="card-body">
                {{ Field::text($model, 'name') }}

                {{ Field::textarea($model, 'description') }}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ trans('cms::app.config') }}</h5>
            </div>
            <div class="card-body">
                {{ Field::select(
                    trans('subscription::content.payment_method'),
                     'method',
                     [
                         'id' => 'select-payment-method',
                         'options' => array_merge(['' => '--- '.trans('subscription::content.payment_method').' ---'], $methodOptions),
                         'value' => $model->method,
                     ]
                     )
                }}

                <div @if(empty($model->configs)) class="box-hidden" @endif id="show-configs">
                    {{ Field::render($configFields, $model->configs ?? []) }}
                </div>
            </div>
        </div>

    @endcomponent

@endsection



