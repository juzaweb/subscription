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
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            {{--@component('core::components.datatables.filters')
                <div class="col-md-3 jw-datatable_filters">

                </div>
            @endcomponent--}}
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


    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post"
                  action=""
                  data-success="handlePaymentSuccess"
                  id="payment-form"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ __('Test Payment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="payment-container">


                            <div id="form-card"></div>

                            <div id="payment-message"></div>

                            <button type="submit" class="btn btn-primary">{{ __('Send Payment Request') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{ $dataTable->scripts() }}
@endsection
