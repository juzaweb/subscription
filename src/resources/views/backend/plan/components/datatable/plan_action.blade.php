
<button
        class="btn btn-sm btn-info sync-plan"
        title="{{ __('Sync plan to Payment Gateway') }}"
        data-plan-id="{{ $row->id }}"
        @if($row->is_free)
            disabled
        @endif
>
    <i class="fa fa-refresh"></i>
</button>
