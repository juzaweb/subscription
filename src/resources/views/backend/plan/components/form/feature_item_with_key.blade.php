<div class="card mb-1">
    @php
     /**
     * @var \Juzaweb\Subscription\Models\Plan $model
    * @var array $feature
     */
        $item = $model->features->where('feature_key', $feature['key'])->first();
    @endphp
    <div class="card-body">
        <div class="repeater-item-content">
            <div class="row">
                <div class="col-md-12">
                    <input type="hidden" name="features[{{ $marker }}][id]"
                           class="form-control "
                           id="{{ $marker }}-features[{{ $marker }}][id]"
                           value="{{ $item->id ?? '' }}"
                    />

                    <input type="hidden" name="features[{{ $marker }}][feature_key]"
                           class="form-control "
                           id="{{ $marker }}-features[{{ $marker }}][feature_key]"
                           value="{{ $feature['key'] }}"
                    />

                    <input type="checkbox" name="features[{{ $marker }}][value]"
                           id="{{ $marker }}-features[{{ $marker }}][value]"
                           class="feature_key-checkbox"
                           value="1"
                           {{ ($item->value ?? 0) == 1 ? 'checked' : '' }}
                    /> <label for="{{ $marker }}-features[{{ $marker }}][value]">{{ $feature['label'] }}</label>
                </div>

                <div class="col-md-12 box-label {{ ($item->value ?? 0) == 1 ? '' : 'box-hidden' }}">
                    {{ Field::text(
                        trans('subscription::content.feature.label'),
                         "features[{$marker}][title]",
                         [
                             'value' => $item->title ?? $feature['label'],
                        ]
                        )
                    }}
                </div>
            </div>
        </div>
    </div>
</div>
