<div id="create-city-wrapper">
    <div class="modal-header">
        <h5 class="modal-title">Add City</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <form id="addFrm">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="name" class="form-label">City Name <sup class="text-danger fs-5">*</sup> :</label>
                        <input type="text" name="city_name" id="name" class="form-control" placeholder="Enter City Name"/>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="country" class="form-label">Country <sup class="text-danger fs-5">*</sup> :</label>
                        <select class="form-control" name="country" id="country" data-placeholder="Choose Country">
                            <option value=""></option> 
                            @if ($countries->isNotEmpty())
                                @foreach ($countries as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="state" class="form-label">State <sup class="text-danger fs-5">*</sup> :</label>
                        <select class="form-control" name="state" id="state" data-placeholder="Choose State">
                            <option value=""></option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="name" class="form-label fw-bold">Status <sup class="text-danger fs-5">*</sup> :</label>
                        <div class="square-switch">
                            <input type="checkbox" id="square-status" switch="status" name="status" value="1" checked />
                            <label for="square-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>   
                    </div>
                </div>  
            </div> 
        </form>
    </div>
    <div class="modal-footer text-right">
        <button id="createBtn" type="button" class="btn btn-success waves-effect waves-light">Save Changes</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    initSelect2Custom('#create-city-wrapper [name="country"]', '#create-city-wrapper');
    initSelect2Custom('#create-city-wrapper [name="state"]', '#create-city-wrapper');
    
    $('#create-city-wrapper').on('change', '[name="country"]', function (e) {
        let val = $(this).val();
        $('#create-city-wrapper [name="state"]').html('<option value=""></option>').trigger('change');
        
        if (val == '') {
            return false;
        }

        $.get(`{{ route("admin.states.country_wise.list") }}/${val}`, response => {
            let html = '<option value=""></option>';
            response.forEach(el => html += `<option value="${el.id}">${el.name}</option>`);
            $('#create-city-wrapper [name="state"]').html(html).trigger('change');
        }, 'json');
    });

        
    $('#create-city-wrapper').on('click', '#createBtn', function (e) {
        e.preventDefault();
        const $btn = $(this);
        
        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: "{{ route('admin.cities.create') }}",
            data: $('#addFrm').serialize(),
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
                $('#create-city-wrapper .close').click();
            }
        });
    });
</script>