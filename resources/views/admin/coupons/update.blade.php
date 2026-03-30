<div id="update-wrapper">

    <div class="modal-header">
        <h5 class="modal-title">Edit Coupon</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <form id="updateFrm">
            @csrf

            <div class="row">

                {{-- Coupon Code --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">
                            Coupon Code <sup class="text-danger fs-5">*</sup> :
                        </label>
                        <input type="text" name="code" class="form-control" value="{{ $coupon->code }}"
                            placeholder="e.g. WELCOME10">
                    </div>
                </div>

                {{-- Discount Type --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">
                            Discount Type <sup class="text-danger fs-5">*</sup> :
                        </label>
                        <select class="form-control select2-update" name="discount_type" id="update_discount_type"
                            data-placeholder="Select Type">

                            <option value=""></option>
                            <option value="flat" {{ $coupon->discount_type == 'flat' ? 'selected' : '' }}>
                                Flat (₹)
                            </option>

                            <option value="percentage" {{ $coupon->discount_type == 'percentage' ? 'selected' : '' }}>
                                Percentage (%)
                            </option>

                        </select>
                    </div>
                </div>

                {{-- Discount Value --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">
                            Discount Value <sup class="text-danger fs-5">*</sup> :
                        </label>
                        <input type="number" step="0.01" name="discount_value" class="form-control"
                            value="{{ $coupon->discount_value }}" placeholder="Flat: 100 | Percentage: 10">
                    </div>
                </div>

                {{-- Minimum Amount --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">
                            Minimum Order Amount
                        </label>
                        <input type="number" step="0.01" name="min_amount" class="form-control"
                            value="{{ $coupon->min_amount }}" placeholder="e.g. 1000">
                    </div>
                </div>

                {{-- Max Discount --}}
                <div class="col-lg-6 {{ $coupon->discount_type == 'percentage' ? '' : 'd-none' }}"
                    id="update-max-discount-wrapper">

                    <div class="form-group">
                        <label class="form-label">
                            Maximum Discount Cap
                        </label>
                        <input type="number" step="0.01" name="max_discount" class="form-control"
                            value="{{ $coupon->max_discount }}" placeholder="e.g. 500 (Only for percentage)">

                        <small class="text-muted">
                            Applicable only for percentage coupons
                        </small>
                    </div>
                </div>

                {{-- Expiry Date --}}
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="form-label">
                            Expiry Date <sup class="text-danger">*</sup>
                        </label>
                        <input type="date" name="expiry_date" class="form-control"
                            value="{{ $coupon->expiry_date }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-lg-3 mt-3">
                    <div class="form-group d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold">Status</label>

                        <input type="hidden" name="status" value="0">

                        <div class="square-switch">
                            <input type="checkbox" id="update-switch-status" name="status" switch="status"
                                value="1" {{ $coupon->status ? 'checked' : '' }}>

                            <label for="update-switch-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button id="updateBtn" type="button" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Close</button>
    </div>

</div>

<script>
    /* Toggle max discount */
    $('#update-wrapper').on('change', '#update_discount_type', function() {

        let type = $(this).val();

        if (type === 'percentage') {
            $('#update-max-discount-wrapper').removeClass('d-none');
        } else {
            $('#update-max-discount-wrapper').addClass('d-none');
            $('#update-wrapper input[name="max_discount"]').val('');
        }

    });


    /* Select2 for modal */
    $('#update-wrapper .select2-update').select2({
        width: '100%',
        allowClear: true,
        dropdownParent: $('#update-wrapper')
    });


    /* Submit Update */
    $('#update-wrapper').on('click', '#updateBtn', function(e) {

        e.preventDefault();

        const $btn = $(this);
        let formData = new FormData($('#updateFrm')[0]);

        $.ajax({
            url: "{{ route('admin.coupons.update', $coupon->id) }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            beforeSend: function() {
                $btn.prop('disabled', true);
                showToastr('info', 'Updating...');
            },

            success: function(response) {
                showToastr('success', response.message);
                $('#data-table').DataTable().ajax.reload(null, false);
                $('#update-wrapper .close').click();
            },

            error: function(xhr) {
                showToastr('error', formatErrorMessage(xhr));
                $btn.prop('disabled', false);
            }

        });

    });
</script>
