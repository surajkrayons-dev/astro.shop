<div id="create-wrapper">
    <div class="modal-header">
        <h5 class="modal-title">Add Coupon</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <form id="addFrm">
            @csrf

            <div class="row">

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Coupon Code <sup class="text-danger fs-5">*</sup> :</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. WELCOME10">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Discount Type <sup class="text-danger fs-5">*</sup> :</label>
                        <select class="form-control select2-class2" name="discount_type" id="modal_discount_type"
                            data-placeholder="Select Type">
                            <option value=""></option>
                            <option value="flat">Flat (₹)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Discount Value <sup class="text-danger fs-5">*</sup> :</label>
                        <input type="number" step="0.01" name="discount_value" class="form-control"
                            placeholder="Flat: 100 | Percentage: 10">
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Minimum Order Amount</label>
                        <input type="number" step="0.01" name="min_amount" class="form-control"
                            placeholder="e.g. 1000">
                    </div>
                </div>

                <div class="col-lg-6 d-none" id="max-discount-wrapper">
                    <div class="form-group">
                        <label class="form-label">Maximum Discount Cap</label>
                        <input type="number" step="0.01" name="max_discount" class="form-control"
                            placeholder="e.g. 500 (Only for percentage)">
                        <small class="text-muted">Applicable only for percentage coupons</small>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">Expiry Date <sup class="text-danger">*</sup></label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>

                <div class="col-lg-2 p-0 mt-3 ms-2">
                    <div class="form-group d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold">Status</label>
                        <input type="hidden" name="status" value="0">

                        <div class="square-switch">
                            <input type="checkbox" id="switch-status" name="status" switch="status" value="1"
                                checked>
                            <label for="switch-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mt-3">
                    <div class="form-group d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold">Visible On Frontend</label>

                        <input type="hidden" name="is_visible" value="0">

                        <div class="square-switch">
                            <input type="checkbox" id="switch-visible" name="is_visible" switch="info" value="1"
                                checked>

                            <label for="switch-visible" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button id="createBtn" type="button" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    $('#create-wrapper').on('change', '#modal_discount_type', function() {
        let type = $(this).val();
        if (type === 'percentage') {
            $('#max-discount-wrapper').removeClass('d-none');
        } else {
            $('#max-discount-wrapper').addClass('d-none');
            $('input[name="max_discount"]').val('');
        }
    });

    $('#create-wrapper .select2-class2').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder');
        },
        allowClear: true,
        dropdownParent: $('#create-wrapper')
    });

    $('#create-wrapper').on('click', '#createBtn', function(e) {
        e.preventDefault();

        const $btn = $(this);
        const formData = new FormData($('#addFrm')[0]);

        $.ajax({
            url: "{{ route('admin.coupons.create') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $btn.prop('disabled', true);
                showToastr('info', 'Saving...');
            },
            success: function(response) {
                showToastr('success', response.message);
                $('#data-table').DataTable().ajax.reload(null, false);
                $('#create-wrapper .close').click();
            },
            error: function(xhr) {
                showToastr('error', formatErrorMessage(xhr));
                $btn.prop('disabled', false);
            }
        });
    });
</script>
