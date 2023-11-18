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

                <h5>{{ trans('subscription::content.features') }}</h5>

                 {{ Field::repeater(
                    [
                        'label' => 'Feature',
                        'name' => 'features',
                        'fields' => [
                            'row' => [
                                'type' => 'row',
                                'fields' => [
                                    'name' => [
                                        'type' => 'col',
                                        'col' => 6,
                                        'fields' => [
                                            'name' => [
                                                'type' => 'text',
                                                'label' => trans('subscription::content.feature.label'),
                                            ],
                                        ],
                                    ],
                                    'value' => [
                                        'type' => 'col',
                                        'col' => 6,
                                        'fields' => [
                                            'value' => [
                                                'type' => 'text',
                                                'label' => trans('subscription::content.feature.value'),
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                     ],
                     $model->features
                 ) }}

                <div class="mb-3"></div>
            </div>

            <div class="col-md-4 mt-3">
                {{ Field::select($model, 'status', ['options' => \Juzaweb\Subscription\Models\Plan::getAllstatus()]) }}

                {{ Field::checkbox($model, 'is_free', ['label' => trans('subscription::content.is_free'), 'checked' => $model->is_free == 1]) }}
            </div>
        </div>


    @endcomponent

@endsection



