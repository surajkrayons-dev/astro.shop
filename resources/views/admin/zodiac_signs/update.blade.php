<div id="update-wrapper">
    <div class="modal-header">
        <h5 class="modal-title">Edit Zodiac Sign</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <form id="updateFrm" enctype="multipart/form-data">
            @csrf

            <div class="row">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Zodiac Name <sup class="text-danger">*</sup></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ $zodiac->name }}"
                               placeholder="Enter Zodiac Name">
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Slug <sup class="text-danger">*</sup></label>
                        <input type="text" name="slug" class="form-control"
                               value="{{ $zodiac->slug }}" readonly>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Icon</label>
                        <input type="file" name="icon" class="form-control">

                        <div class="mt-2">
                            @if($zodiac->icon)
                                <img 
                                    src="{{ asset('storage/zodiac/'.$zodiac->icon) }}" 
                                    width="80"
                                    height="80"
                                    class="img-thumbnail"
                                    onerror="this.onerror=null;this.src='https://placehold.co/80x80';"
                                >
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control"
                                  placeholder="Enter description">{{ $zodiac->description }}</textarea>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label fw-bold">Status</label>
                        <div class="square-switch">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" id="square-status" name="status" value="1"
                                   {{ $zodiac->status ? 'checked' : '' }}>
                            <label for="square-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button id="updateBtn" type="button" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    $('input[name="name"]').on('keyup change', function () {
        let slug = $(this).val()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');

        $('input[name="slug"]').val(slug);
    });

    // Status toggle
    $('#square-status').on('change', function () {
        $(this).siblings('input[type=hidden]').val(this.checked ? 1 : 0);
    });

    // Submit update
    $('#update-wrapper').on('click', '#updateBtn', function (e) {
        e.preventDefault();

        const $btn = $(this);
        let formData = new FormData($('#updateFrm')[0]);

        $.ajax({
            url: "{{ route('admin.zodiac_signs.update', $zodiac->id) }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $btn.prop('disabled', true);
                showToastr('info', 'Updating...');
            },
            success: function (res) {
                showToastr('success', res.message);
                $('#data-table').DataTable().ajax.reload(null, false);
                $('#update-wrapper .close').click();
            },
            error: function (xhr) {
                showToastr('error', formatErrorMessage(xhr));
                $btn.prop('disabled', false);
            }
        });
    });
</script>

