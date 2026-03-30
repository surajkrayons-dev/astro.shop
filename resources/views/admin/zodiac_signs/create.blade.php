<div id="create-wrapper">
    <div class="modal-header">
        <h5 class="modal-title">Add Zodiac Sign</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <form id="addFrm" enctype="multipart/form-data">
            @csrf

            <div class="row">

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Zodiac Name <sup class="text-danger">*</sup></label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Zodiac Name">
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Slug <sup class="text-danger">*</sup></label>
                        <input type="text" name="slug" class="form-control" placeholder="e.g. Auto generated from name" readonly>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label">Icon</label>
                        <input type="file" name="icon" class="form-control">
                        <small class="text-muted"><b>Example::</b> image size - 128x128.</small>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="description" class="form-label fw-bold">Description :</label>
                        <textarea name="description" id="description" name="description" class="form-control" placeholder="Enter Your description..."></textarea>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="form-label fw-bold">Status <sup class="text-danger">*</sup></label>
                        <div class="square-switch">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" id="square-status" name="status" value="1" checked>
                            <label for="square-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button id="createBtn" type="button" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    // Auto generate slug from name
    $('input[name="name"]').on('keyup change', function () {
        let slug = $(this).val()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')  // remove special chars
            .replace(/\s+/g, '-')          // spaces → dash
            .replace(/-+/g, '-');          // multiple dashes

        $('input[name="slug"]').val(slug);
    });

    $('#square-status').on('change', function () {
        if ($(this).prop('checked')) {
            $(this).siblings('input[type=hidden]').val(1);
        } else {
            $(this).siblings('input[type=hidden]').val(0);
        }
    });

    $('#create-wrapper').on('click', '#createBtn', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const formData = new FormData($('#addFrm')[0]);

        $.ajax({
            url: "{{ route('admin.zodiac_signs.create') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $btn.prop('disabled', true);
                showToastr('info', 'Saving...');
            },
            success: function (response) {
                showToastr('success', response.message);
                $('#data-table').DataTable().ajax.reload(null, false);
                $('#create-wrapper .close').click();
            },
            error: function (xhr) {
                showToastr('error', formatErrorMessage(xhr));
                $btn.prop('disabled', false);
            }
        });
    });
</script>

