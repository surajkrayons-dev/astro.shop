@extends('layouts.master')

@section('title') Add Horoscope @endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Add Horoscope</h4>

            <div class="page-title-right">
                <a href="{{ route('admin.horoscopes.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<form id="createFrm" enctype="multipart/form-data">
    @csrf

    <div class="row">

        <!-- LEFT -->
        <div class="col-lg-8">

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Horoscope Details</h4>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Zodiac <span class="text-danger">*</span></label>
                            <select name="zodiac_id" class="form-control select2-class" required data-placeholder="Select Zodiac">
                                <option value=""></option>
                                @foreach($zodiacs as $zodiac)
                                    <option value="{{ $zodiac->id }}">{{ $zodiac->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control select2-class" required data-placeholder="Select Type">
                                <option value=""></option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="tomorrow">Tomorrow</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" name="date" class="form-control">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter title">
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Enter description..."></textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Love</label>
                            <textarea name="love" class="form-control" rows="3" placeholder="Enter love description..."></textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Career</label>
                            <textarea name="career" class="form-control" rows="3" placeholder="Enter career description..."></textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Health</label>
                            <textarea name="health" class="form-control" rows="3" placeholder="Enter health description..."></textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Finance</label>
                            <textarea name="finance" class="form-control" rows="3" placeholder="Enter finance description..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Status</h4>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold mb-0">
                            Status <sup class="text-danger fs-5">*</sup>
                        </label>

                        <div class="square-switch">
                            <input type="hidden" name="status" value="0">

                            <input
                                type="checkbox"
                                id="square-status"
                                name="status"
                                value="1"
                                checked
                            >
                            <label for="square-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lucky Number</label>
                        <input type="text" name="lucky_number" class="form-control" placeholder="Enter lucky number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lucky Color</label>
                        <input type="text" name="lucky_color" class="form-control" placeholder="Enter lucky color">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <button id="createBtn" type="submit" class="btn btn-success w-100 mb-2">
                        Save Horoscope
                    </button>
                    <button type="reset" class="btn btn-warning w-100">
                        Clear
                    </button>
                </div>
            </div>

        </div>

    </div>
</form>
@endsection


@section('script')
<script>
$(document).ready(function () {

    $('#createBtn').on('click', function (e) {
        e.preventDefault();

        let formData = new FormData($('#createFrm')[0]);
        let btn = $(this);

        $('#status').each(function() {
            if ($(this).prop('checked')) {
                formData.append($(this).attr('name'), 1);
            } else {
                formData.append($(this).attr('name'), 0);
            }
        });

        $.ajax({
            url: "{{ route('admin.horoscopes.create') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                btn.prop('disabled', true);
                showToastr('info', 'Saving...');
            },
            success: function (res) {
                showToastr('success', res.message);
                window.location.href = "{{ route('admin.horoscopes.index') }}";
            },
            error: function (xhr) {
                showToastr('error', 'Something went wrong');
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    });

});
</script>
@endsection
