@extends('layouts.master')

@section('title', 'Add Astro Banner')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between">
                <h4>Add Astro Banner</h4>
                <a href="{{ route('admin.astro_banners.index') }}" class="btn btn-primary">
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

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Upload Media <sup class="text-danger">*</sup>
                                    </label>

                                    <input type="file" name="media" id="mediaInput" class="form-control"
                                        accept="image/*,video/mp4,video/webm" required>

                                    <small class="text-muted">
                                        Allowed: JPG, PNG, WEBP, MP4, WEBM (Max 20MB)
                                    </small>
                                </div>

                                <div id="mediaPreview" class="mt-3"></div>


                            </div>
                        </div>

                    </div>

                    <div class="col-lg-4">

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Banner Status</h4>
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
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Sort Order</h4>
                            </div>

                            <div class="card-body">
                                <div class="form-group d-flex justify-content-between align-items-center">
                                    <input type="number" name="sort_order" class="form-control" value="0"
                                        min="0">
                                </div>
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

            $('#mediaInput').on('change', function(e) {

                let file = e.target.files[0];
                let preview = $('#mediaPreview');
                preview.html('');

                if (!file) return;

                let url = URL.createObjectURL(file);

                if (file.type.startsWith('video/')) {

                    preview.html(`
                        <label class="form-label fw-bold mt-2">Preview</label><br>
                        <video width="300" controls>
                            <source src="${url}">
                        </video>
                    `);

                } else {

                    preview.html(`
                        <label class="form-label fw-bold mt-2">Preview</label><br>
                        <img src="${url}" 
                            width="300" 
                            class="img-fluid rounded">
                    `);
                }
            });

            $('#createBtn').click(function(e) {
                e.preventDefault();

                let btn = $(this);
                let formData = new FormData($('#createFrm')[0]);

                $.ajax({
                    url: "{{ route('admin.astro_banners.create') }}",
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
                        window.location.href = "{{ route('admin.astro_banners.index') }}";
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
