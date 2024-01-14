<h5>{{ trans('subscription::content.features') }}</h5>

<div class="row form-repeater">
    <div class="col-md-12 repeater-items">
        @foreach($model->features as $feature)
            @component('subscription::backend.plan.components.form.feature_item',
 ['marker' => $feature->id, 'item' => $feature])

            @endcomponent
        @endforeach
    </div>

    <div class="col-md-12">
        <button type="button" class="btn btn-primary btn-sm add-repeater-item">{{__('Add new Feature')}}</button>
    </div>

    <script type="text/html" class="repeater-item-template">
        @component('subscription::backend.plan.components.form.feature_item', ['marker' => '{marker}'])

        @endcomponent
    </script>
</div>
