@extends('layouts.master')

@section('title', 'Update Delivery Rate')

@section('content')

<div class="row">
    <div class="col-12">

        <div class="page-title-box d-flex justify-content-between">

            <h4>Update Delivery Rate</h4>

            <a href="{{ route('admin.delivery_rates.index') }}" class="btn btn-primary">

                <i class="fas fa-arrow-left"></i> Back

            </a>

        </div>

    </div>
</div>

<form id="updateFrm">
    @csrf

    <div class="card">

        <div class="card-body">

            <div class="row">

                {{-- LEFT --}}
                <div class="col-lg-8">

                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-bold">
                                        State
                                        <sup class="text-danger fs-5">*</sup>
                                    </label>

                                    <select name="state" class="form-control select2-class2"
                                        data-placeholder="Choose State">

                                        <option value=""></option>

                                        @foreach($states as $state)

                                        <option value="{{ $state }}"
                                            {{ $delivery_rate->state == $state ? 'selected' : '' }}>

                                            {{ $state }}

                                        </option>

                                        @endforeach

                                    </select>

                                </div>

                                <div class="col-md-6 mb-3">

                                    <label class="form-label fw-bold">
                                        Delivery Charge
                                        <sup class="text-danger fs-5">*</sup>
                                    </label>

                                    <input type="number" name="delivery_charge" class="form-control" min="0" step="0.01"
                                        value="{{ $delivery_rate->delivery_charge }}"
                                        placeholder="Enter delivery charge">

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

                {{-- RIGHT --}}
                <div class="col-lg-4">

                    <div class="card">

                        <div class="card-header">

                            <h4 class="card-title mb-0">
                                Delivery Rate Status
                            </h4>

                        </div>

                        <div class="card-body">

                            <div class="form-group d-flex justify-content-between align-items-center">

                                <label class="form-label fw-bold">
                                    Status
                                </label>

                                <input type="hidden" name="status" value="0">

                                <div class="square-switch">

                                    <input type="checkbox" id="square-status" name="status" switch="status" value="1"
                                        {{ $delivery_rate->status ? 'checked' : '' }}>

                                    <label for="square-status" data-on-label="Yes" data-off-label="No">
                                    </label>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="card-footer text-end">

            <button type="button" id="updateBtn" class="btn btn-success">

                Update

            </button>

        </div>

    </div>

</form>

@endsection

@section('script')

<script>
$(document).ready(function() {

    $('#updateBtn').click(function() {

        let btn = $(this);

        let formData = new FormData($('#updateFrm')[0]);

        $.ajax({

            url: "{{ route('admin.delivery_rates.update', $delivery_rate->id) }}",

            type: "POST",

            data: formData,

            processData: false,

            contentType: false,

            beforeSend: () => {

                btn.prop('disabled', true);

                showToastr('info', 'Updating...');

            },

            success: res => {

                showToastr('success', res.message);

                window.location.href =
                    "{{ route('admin.delivery_rates.index') }}";

            },

            error: xhr => {

                btn.prop('disabled', false);

                showToastr(
                    'error',
                    formatErrorMessage(xhr)
                );

            }

        });

    });

});
</script>

@endsection