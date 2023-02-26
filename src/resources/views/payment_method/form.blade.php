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
                 'options' => array_merge(['' => '--- '.trans('subscription::content.payment_method').' ---'], $methodOptions)
             ]
             )
        }}

        <div class="box-hidden" id="show-configs"></div>
    </div>
</div>

