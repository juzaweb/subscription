@extends('core::layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @can('subscription-methods.create')
                <a href="{{ admin_url('subscription-methods/create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add Payment Method') }}
                </a>
            @endcan

            @can('subscription-methods.create')
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModal">
                    <i class="fas fa-registered"></i> {{ __('Test Payment') }}
                </button>
            @endcan

            <div class="float-right">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="sandbox-toggle"
                        {{ setting('subscription_sandbox', 1) == 1 ? 'checked' : '' }}>
                    <label class="custom-control-label" for="sandbox-toggle">{{ __('Sandbox Mode') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            {{-- @component('components.datatables.filters')
                <div class="col-md-3 jw-datatable_filters">

                </div>
            @endcomponent --}}
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Payment Methods') }}</h3>
                </div>
                <div class="card-body">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('subscription.js') }}"></script>

    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('subscription.subscribe', ['test']) }}"
                data-success="handlePaymentSuccess" id="subscription-form">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ __('Test Payment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="payment-container">
                            {{ Field::select(__('Method'), 'method_id')->dropDownList($paymentMethods) }}

                            {{ Field::select(__('Plan'), 'plan_id')->dropDownList($testPlans) }}

                            <div id="form-card"></div>

                            <div id="payment-message"></div>

                            <button type="submit" class="btn btn-primary">{{ __('Send Payment Request') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{ $dataTable->scripts(null, ['nonce' => csp_script_nonce()]) }}

    <script type="text/javascript" nonce="{{ csp_script_nonce() }}">
        const subscription = new SubscriptionForm('test', '#subscription-form');

        $('#sandbox-toggle').on('change', function() {
            let sandbox = $(this).is(':checked') ? 1 : 0;
            $.ajax({
                url: '{{ route('subscription-methods.update-sandbox') }}',
                type: 'POST',
                data: {
                    sandbox: sandbox
                },
                success: function(response) {
                    show_notify(response);
                },
                error: function(xhr) {
                    show_notify(xhr);
                }
            });
        });
    </script>
@endsection
