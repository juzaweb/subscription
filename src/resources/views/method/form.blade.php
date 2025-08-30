@extends('core::layouts.admin')

@section('content')
    <form action="{{ $action }}" class="form-ajax" method="post">
        @if($model->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-12">
                <a href="{{ admin_url('subscription-methods') }}" class="btn btn-warning">
                    <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                </a>

                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('Save') }}
                </button>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Information') }}</h3>
                    </div>
                    <div class="card-body">
                        {{ Field::select($model, 'driver')->dropDownList(
                            [
                                ...['' => __('Select a driver')],
                                ...$drivers,
                            ]
                        )->disabled($model->exists) }}

                        {{ Field::text($model, "{$locale}[name]", ['id' => 'name', 'value' => $model->name, 'label' => __('Name')]) }}

                        {{ Field::textarea($model, "{$locale}[description]", ['value' => $model->description, 'label' => __('Description')]) }}

                        {{ Field::checkbox($model, 'active', ['value' => $model->active ?? 1]) }}
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <x-language-card :label="$model" :locale="$locale" />
            </div>

            <div class="col-md-12 @if(! $model->exists) d-none @endif" id="config-form">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Config') }}</h3>
                    </div>
                    <div class="card-body">
                        @if($model->exists)
                            {!! \Juzaweb\Modules\Subscription\Facades\Subscription::renderConfig($model->driver, $model->config ?? []) !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function () {
            $('#driver').on('change', function () {
                let driver = $(this).val();
                if (!driver) {
                    $('#name').val('');
                    $('#config-form').addClass('d-none');
                    $('#config-form .card-body').empty();
                    return;
                }

                $('#name').val(driver);

                let url = '{{ admin_url("subscription-methods/:driver/get-data") }}'.replace(':driver', driver);

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        if (data && data.config) {
                            $('#config-form').removeClass('d-none');
                            $('#config-form .card-body').html(data.config);
                        } else {
                            $('#config-form').addClass('d-none');
                            $('#config-form .card-body').empty();
                        }
                    },
                    error: function () {
                        show_notify('Error fetching driver data');
                    }
                });
            });
        });
    </script>
@endsection
