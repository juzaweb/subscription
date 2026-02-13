@extends('core::layouts.admin')

@section('content')
    <form action="{{ $action }}" class="form-ajax" method="post">
        @if($model->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-12">
                <a href="{{ $backUrl }}" class="btn btn-warning">
                    <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('Save') }}
                </button>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-9">
                <x-card title="{{ __('Information') }}">
                    {{ Field::text($model, "name", ['value' => $model->name, 'label' => __('Name')]) }}

                    {{ Field::text($model, "price", ['value' => $model->price, 'label' => __('Price'), 'step' => '0.01', 'type' => 'number'])->disabled($model->exists) }}

                    {{ Field::checkbox($model, "is_free", ['value' => $model->is_free, 'label' => __('Is Free plan')])->disabled($model->exists) }}
                </x-card>

                <x-card title="{{ __('Features') }}">
                    @foreach($features as $feature)
                        @if($feature->type === \Juzaweb\Modules\Subscription\Enums\FeatureType::BOOLEAN)
                            {{ Field::checkbox($feature->label, "features[{$feature->name}]", [
                                'value' => $model->getFeatureValue($feature->name) ? 1 : 0,
                            ]) }}
                        @endif

                        @if($feature->type === \Juzaweb\Modules\Subscription\Enums\FeatureType::TEXT)
                            {{ Field::text($feature->label, "features[{$feature->name}]", [
                                'value' => $model->getFeatureValue($feature->name),
                            ]) }}
                        @endif

                        @if($feature->type === \Juzaweb\Modules\Subscription\Enums\FeatureType::NUMBER)
                            {{ Field::text($feature->label, "features[{$feature->name}]", [
                                'value' => $model->getFeatureValue($feature->name),
                                'min' => 0,
                                'type' => 'number',
                            ]) }}
                        @endif

                        @if($feature->type === \Juzaweb\Modules\Subscription\Enums\FeatureType::SIZE)
                            {{ Field::text($feature->label, "features[{$feature->name}]", [
                                'value' => $model->getFeatureValue($feature->name),
                                'min' => 0,
                                'type' => 'number',
                            ]) }}

                            <small class="form-text text-muted">{{ __('Size in MB') }}</small>
                        @endif
                    @endforeach
                </x-card>
            </div>

            <div class="col-md-3">
                <x-language-card :label="$model" :locale="$locale" />

                <x-card>
                    {{ Field::checkbox($model, 'active', ['value' => $model->active ?? 1]) }}
                </x-card>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript" nonce="{{ csp_script_nonce() }}">
        $(function () {
            $('#is_free').on('change', function () {
                if ('{{ $model->exists }}' === '1') {
                    return;
                }

                if ($(this).is(':checked')) {
                    $('#price').prop('disabled', true);
                } else {
                    $('#price').prop('disabled', false);
                }
            }).trigger('change');
        });
    </script>
@endsection
