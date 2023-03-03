@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model,
    ])

        <input type="hidden" name="module" value="{{ $module }}">

        <div class="row" id="plan-form">
            <div class="col-md-8">
                {{ Field::text($model, 'name') }}

                {{ Field::textarea($model, 'description') }}

                {{ Field::text($model, 'price', ['label' => trans('subscription::content.price'), 'class' => 'is-number number-format', 'value' => number_format($model->price), 'prefix' => '$', 'disabled' => $model->is_free == 1]) }}
            </div>

            <div class="col-md-4 mt-3">
                {{ Field::checkbox($model, 'is_free', ['label' => trans('subscription::content.is_free'), 'checked' => $model->is_free == 1]) }}

                {{ Field::checkbox($model, 'enable_trial', ['label' => trans('subscription::content.enable_trial'), 'checked' => $model->enable_trial == 1]) }}
            </div>
        </div>


    @endcomponent

@endsection



