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

                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        Upload Media (Image / Video)
                                    </label>

                                    <input type="file" name="media" id="mediaInput" class="form-control">

                                    <small class="text-muted">
                                        Allowed: JPG, PNG, WEBP, MP4, WEBM (Max 20MB)
                                    </small>
                                </div>

                                {{-- Existing Preview --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Current Media</label>

                                    <div id="existingPreview">
                                        @if ($banner->media)

                                            @php
                                                $media = $banner->media;
                                                $url = asset('storage/' . $media['path']);
                                            @endphp

                                            @if ($media['type'] === 'video')
                                                <video width="300" controls>
                                                    <source src="{{ $url }}">
                                                </video>
                                            @else
                                                <img src="{{ $url }}" width="300" class="img-fluid rounded">
                                            @endif
                                        @else
                                            <p class="text-muted">No media uploaded.</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- New Preview --}}
                                <div id="newPreview" class="mt-3"></div>



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
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Sort Order</h4>
                            </div>

                            <div class="card-body">
                                <div class="form-group d-flex justify-content-between align-items-center">

                                    <input type="number" name="sort_order" value="{{ $banner->sort_order }}"
                                        class="form-control" min="0">
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

            // Preview new media (image/video)
            $('#mediaInput').on('change', function(e) {

                let file = e.target.files[0];
                let preview = $('#newPreview');
                preview.html('');

                if (!file) return;

                let url = URL.createObjectURL(file);

                if (file.type.startsWith('video/')) {
                    preview.html(`
                <label class="form-label fw-bold mt-3">New Preview</label><br>
                <video width="300" controls>
                    <source src="${url}">
                </video>
            `);
                } else {
                    preview.html(`
                <label class="form-label fw-bold mt-3">New Preview</label><br>
                <img src="${url}" width="300" class="img-fluid rounded">
            `);
                }
            });

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
