@extends('layouts.master')

@section('title', 'Add Store Banner')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between">
                <h4>Add Store Banner</h4>
                <a href="{{ route('admin.store_banners.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form id="createFrm" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-body">

                <div class="row">

                    <div class="col-lg-8">

                        <div class="card mb-3">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Desktop Banner <sup class="text-danger">*</sup>
                                            </label>

                                            <input type="file" name="desktop_media" id="desktopMediaInput"
                                                class="form-control" accept="image/*" required>

                                            <small class="text-muted">
                                                Recommended: 1920 × 600 (JPG, PNG, WEBP)
                                            </small>
                                        </div>

                                        <div id="desktopPreview"></div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Mobile Banner <sup class="text-danger">*</sup>
                                            </label>

                                            <input type="file" name="mobile_media" id="mobileMediaInput"
                                                class="form-control" accept="image/*" required>

                                            <small class="text-muted">
                                                Recommended: 1080 × 1350 (JPG, PNG, WEBP)
                                            </small>
                                        </div>

                                        <div id="mobilePreview"></div>

                                    </div>

                                </div>

                                <div class="mt-4">
                                    <label class="form-label fw-bold">
                                        Banner URL
                                    </label>

                                    <input type="url" name="url" class="form-control"
                                        placeholder="https://example.com">

                                    <small class="text-muted">
                                        Optional. Redirect URL for the banner.
                                    </small>
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="col-lg-4">

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Banner Settings</h4>
                            </div>

                            <div class="card-body">
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <label class="form-label fw-bold">Status</label>
                                    <input type="hidden" name="status" value="0">

                                    <div class="square-switch">
                                        <input type="checkbox" id="switch-status" name="status" switch="status"
                                            value="1" checked>
                                        <label for="switch-status" data-on-label="Yes" data-off-label="No"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <span class="text-muted">Sort Order</span>
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <input type="number" name="sort_order" class="form-control" value="0"
                                        min="0">
                                </div>
                            </div>

                            <div class="card-body">
                                <span class="text-muted">Display Duration (in seconds)</span>
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <input type="number" name="display_duration" class="form-control" value="3"
                                        min="1" placeholder="Enter duration in seconds">
                                </div>
                                <small class="text-muted">
                                    Specify how long this banner should be displayed before switching to the next banner.
                                </small>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="card-footer text-end">
                <button type="reset" class="btn btn-warning">Clear</button>
                <button type="button" id="createBtn" class="btn btn-success">Save</button>
            </div>

        </div>
    </form>

@endsection

@section('script')
    <script>
        $(document).ready(function() {

            function previewImage(inputId, previewId) {

                $(inputId).on('change', function(e) {

                    let file = e.target.files[0];

                    if (!file) return;

                    let url = URL.createObjectURL(file);

                    $(previewId).html(`
                        <label class="form-label fw-bold mt-2">Preview</label><br>

                        <img src="${url}"
                            class="img-fluid rounded border"
                            style="max-height:200px;">
                    `);

                });

            }

            previewImage('#desktopMediaInput', '#desktopPreview');
            previewImage('#mobileMediaInput', '#mobilePreview');

            $('#createBtn').click(function(e) {
                e.preventDefault();

                let btn = $(this);
                let formData = new FormData($('#createFrm')[0]);

                $.ajax({
                    url: "{{ route('admin.store_banners.create') }}",
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
                        window.location.href = "{{ route('admin.store_banners.index') }}";
                    },
                    error: xhr => {
                        btn.prop('disabled', false);
                        showToastr('error', formatErrorMessage(xhr));
                    }
                });
            });

        });
    </script>
@endsection
