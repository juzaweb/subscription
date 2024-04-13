<div class="repeater-item card mb-1">
    <div class="card-body">
        <div class="repeater-item-remove">
            <a href="javascript:void(0)" class="btn btn-danger btn-sm remove-repeater-item">
                <i class="fa fa-trash"></i>
            </a>
        </div>

        <div class="repeater-item-content">
            <div class="row">
                <div class="col-md-3">
                    {{ Field::text(
                        trans('subscription::content.feature.value'),
                        "features[{$marker}][value]",
                        [
                             'value' => $item->value ?? '',
                            //  'options' => [
                            //      '&#10003;' => '✓',
                            //      '&#10006;' => '✗',
                            // ],
                        ]
                     )
                    }}
                </div>

                <div class="col-md-6">
                    <input type="hidden" name="features[{{ $marker }}][id]"
                           class="form-control "
                           id="{{ $marker }}-features[{{ $marker }}][id]"
                           value="{{ $item->id ?? '' }}"
                           autocomplete="off"
                           placeholder=""
                    />

                    {{ Field::text(
                        trans('subscription::content.feature.label'),
                         "features[{$marker}][title]",
                         [
                             'value' => $item->title ?? '',
                        ]
                        )
                    }}
                </div>


            </div>
        </div>
    </div>
</div>
