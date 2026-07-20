@extends('layouts.master')

@section('title', 'Update Store Banner')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between">
                <h4>Update Store Banner</h4>
                <a href="{{ route('admin.store_banners.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <form id="updateFrm" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-body">

                <div class="row">

                    {{-- LEFT --}}
                    <div class="col-lg-8">

                        <div class="card mb-3">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Desktop Banner
                                            </label>

                                            <input type="file" name="desktop_media" id="desktopMediaInput"
                                                class="form-control" accept="image/*">

                                            <small class="text-muted">
                                                Leave empty to keep existing desktop banner.
                                            </small>
                                        </div>

                                        <label class="form-label fw-bold">Current Desktop Banner</label>

                                        <div id="desktopExistingPreview">
                                            @if (!empty($banner->media['desktop']))
                                                <img src="{{ asset('storage/' . $banner->media['desktop']) }}"
                                                    class="img-fluid rounded border" style="max-height:220px;">
                                            @else
                                                <p class="text-muted">No desktop banner.</p>
                                            @endif
                                        </div>

                                        <div id="desktopNewPreview" class="mt-3"></div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Mobile Banner
                                            </label>

                                            <input type="file" name="mobile_media" id="mobileMediaInput"
                                                class="form-control" accept="image/*">

                                            <small class="text-muted">
                                                Leave empty to keep existing mobile banner.
                                            </small>
                                        </div>

                                        <label class="form-label fw-bold">Current Mobile Banner</label>

                                        <div id="mobileExistingPreview">
                                            @if (!empty($banner->media['mobile']))
                                                <img src="{{ asset('storage/' . $banner->media['mobile']) }}"
                                                    class="img-fluid rounded border" style="max-height:220px;">
                                            @else
                                                <p class="text-muted">No mobile banner.</p>
                                            @endif
                                        </div>

                                        <div id="mobileNewPreview" class="mt-3"></div>

                                    </div>

                                </div>

                                <div class="mt-4">
                                    <label class="form-label fw-bold">
                                        Banner URL
                                    </label>

                                    <input type="url" name="url" class="form-control"
                                        value="{{ old('url', $banner->url) }}" placeholder="https://example.com">

                                    <small class="text-muted">
                                        Optional. Redirect URL for the banner.
                                    </small>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- RIGHT --}}
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
                                        <input type="checkbox" id="square-status" name="status" switch="status"
                                            value="1" {{ $banner->status ? 'checked' : '' }}>

                                        <label for="square-status" data-on-label="Yes" data-off-label="No"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <span class="text-muted">Sort Order</span>
                                <div class="form-group d-flex justify-content-between align-items-center">

                                    <input type="number" name="sort_order" value="{{ $banner->sort_order }}"
                                        class="form-control" min="0">
                                </div>
                            </div>

                            <div class="card-body">
                                <span class="text-muted">Display Duration (in seconds)</span>
                                <div class="form-group d-flex justify-content-between align-items-center">

                                    <input type="number" name="display_duration" value="{{ $banner->display_duration }}"
                                        class="form-control" min="1" placeholder="Enter duration in seconds">
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

            function previewImage(inputId, previewId) {

                $(inputId).on('change', function(e) {

                    let file = e.target.files[0];

                    if (!file) return;

                    let url = URL.createObjectURL(file);

                    $(previewId).html(`
                        <label class="form-label fw-bold mt-3">
                            New Preview
                        </label>

                        <br>

                        <img src="${url}"
                            class="img-fluid rounded border"
                            style="max-height:220px;">
                    `);

                });

            }

            previewImage('#desktopMediaInput', '#desktopNewPreview');
            previewImage('#mobileMediaInput', '#mobileNewPreview');

            // Update AJAX
            $('#updateBtn').click(function() {

                let btn = $(this);
                let formData = new FormData($('#updateFrm')[0]);

                $.ajax({
                    url: "{{ route('admin.store_banners.update', $banner->id) }}",
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
