<div id="import-wrapper">
    <div class="modal-header">
        <h5 class="modal-title">Import Pin Codes xlsx</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <form id="xlsxFrm">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="name" class="form-label">Choose xlsx File <a href="{{ route('admin.pin_codes.import.xlsx.download.sample') }}" class="btn btn-sm btn-link">(Download Xlsx)</a></label>
                        <div>
                            <input type="file" name="xlsx_file" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer text-right">
        <button id="importBtn" type="button" class="btn btn-success waves-effect waves-light">Import Xlsx</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    $('#import-wrapper').on('click', '#importBtn', function (e) {
        e.preventDefault();
        const $btn = $(this);

        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: "{{ route('admin.pin_codes.import.xlsx.data') }}",
            data: new FormData($('#xlsxFrm')[0]),
            processData: false,
            contentType: false,
            beforeSend: () => {
                $btn.attr('disabled', true);
                showToastr();
            },
            error: (jqXHR, exception) => {
                $btn.attr('disabled', false);
                showToastr('error', formatErrorMessage(jqXHR, exception));
            },
            success: response => {
                $btn.attr('disabled', false);
                showToastr('success', response.message);
                reloadTable('data-table');
                $('#import-wrapper .close').click();
            }
        });
    });
</script>
