@extends('layouts.master')

@section('title', 'Add Delivery Rate')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex justify-content-between">
            <h4>Add Delivery Rate</h4>

            <a href="{{ route('admin.delivery_rates.index') }}" class="btn btn-primary">

                <i class="fas fa-arrow-left"></i> Back

            </a>
        </div>
    </div>
</div>

<form id="createFrm">
    @csrf

    <div class="card">
        <div class="card-body">

            <div class="row">

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

                                        <option value="{{ $state }}">
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
                                        placeholder="Enter delivery charge">

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

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

                                    <input type="checkbox" id="switch-status" name="status" switch="status" value="1"
                                        checked>

                                    <label for="switch-status" data-on-label="Yes" data-off-label="No">
                                    </label>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </div>

        <div class="card-footer text-end">

            <button type="reset" class="btn btn-warning">

                Clear

            </button>

            <button type="button" id="createBtn" class="btn btn-success">

                Save

            </button>

        </div>
    </div>
</form>

@endsection

@section('script')

<script>
$(document).ready(function() {

    $('#createBtn').click(function(e) {

        e.preventDefault();

        let btn = $(this);

        let formData = new FormData($('#createFrm')[0]);

        $.ajax({

            url: "{{ route('admin.delivery_rates.create') }}",

            type: "POST",

            data: formData,

            processData: false,

            contentType: false,

            beforeSend: () => {

                btn.prop('disabled', true);

                showToastr('info', 'Saving...');

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