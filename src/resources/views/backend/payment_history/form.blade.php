@extends('cms::layouts.backend')

@section('content')
    @component('cms::components.form_resource', [
        'model' => $model
    ])

        <div class="row">
            <div class="col-md-12">

                {{ Field::text($model, 'agreement_id') }}

			{{ Field::text($model, 'amount') }}

			{{ Field::text($model, 'end_date') }}

			{{ Field::text($model, 'method') }}

			{{ Field::text($model, 'method_id') }}

			{{ Field::text($model, 'module') }}

			{{ Field::text($model, 'plan_id') }}

			{{ Field::text($model, 'token') }}

			{{ Field::text($model, 'type') }}

			{{ Field::text($model, 'user_id') }}

			{{ Field::text($model, 'user_subscription_id') }}

            </div>
        </div>

    @endcomponent
@endsection
