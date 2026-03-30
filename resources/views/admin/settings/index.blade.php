@extends('layouts.master')

@section('title') General Configuration @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">General Configuration</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <form id="updateFrm">
                @csrf
                <input type="hidden" name="is_site_logo_file_removed" value="0">
                <input type="hidden" name="is_site_favicon_file_removed" value="0">

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Company Details</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="company_name" class="form-label fw-bold">Company Name <sup class="text-danger fs-5">*</sup> :</label>
                                            <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Enter Company Name" value="{{ $settings['company_name'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="company_email" class="form-label fw-bold">Company Email <sup class="text-danger fs-5">*</sup> :</label>
                                            <input type="text" id="company_email" name="company_email" class="form-control" placeholder="Enter Company Email" value="{{ $settings['company_email'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="company_contact_no" class="form-label fw-bold">Company Contact No <sup class="text-danger fs-5">*</sup> :</label>
                                            <input type="text" id="company_contact_no" name="company_contact_no" class="form-control" placeholder="Enter Company Contact No" value="{{ $settings['company_contact_no'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="company_gst_settings" class="form-label fw-bold">GST Settings: <sup class="text-danger fs-5">*</sup> :</label>
                                            <input type="text" id="company_gst_settings" name="company_gst_settings" class="form-control" placeholder="Enter GST Settings Comma Seperated" value="{{ $settings['company_gst_settings'] }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="company_address" class="form-label fw-bold">Company Address: <sup class="text-danger fs-5">*</sup> :</label>
                                            <textarea id="company_address" name="company_address" class="form-control" placeholder="Enter Company Address">{{ $settings['company_address'] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="company_terms_conditions" class="form-label fw-bold">Terms & Conditions: <sup class="text-danger fs-5">*</sup> :</label>
                                            <textarea id="company_terms_conditions" name="company_terms_conditions" class="form-control" placeholder="Enter Terms & Conditions" rows="5">{{ $settings['company_terms_conditions'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">SMTP Detaills</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="smtp_host" class="form-label fw-bold">SMTP Host :</label>
                                            <input type="text" id="smtp_host" name="smtp_host" class="form-control" placeholder="Enter SMTP Host" value="{{ $settings['smtp_host'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="smtp_port" class="form-label fw-bold">SMTP Port :</label>
                                            <input type="text" id="smtp_port" name="smtp_port" class="form-control" placeholder="Enter SMTP Port" value="{{ $settings['smtp_port'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="smtp_username" class="form-label fw-bold">SMTP Username :</label>
                                            <input type="text" id="smtp_username" name="smtp_username" class="form-control" placeholder="Enter SMTP Username" value="{{ $settings['smtp_username'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="smtp_password" class="form-label fw-bold">SMTP Password :</label>
                                            <input type="text" id="smtp_password" name="smtp_password" class="form-control" placeholder="Enter SMTP Password" value="{{ $settings['smtp_password'] }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="smtp_encryption" class="form-label fw-bold">SMTP Encryption :</label>
                                            <input type="text" id="smtp_encryption" name="smtp_encryption" class="form-control" placeholder="Enter SMTP Encryption" value="{{ $settings['smtp_encryption'] }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Site Logo <sup class="text-danger fs-5">*</sup></h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12l">
                                        <div class="form-group mb-0">
                                            <input type="file" name="site_logo" id="input-file-now" class="dropify" data-default-file="{{ $settings['site_logo'] ? url("uploads/images/{$settings['site_logo']}") : '' }}" />
                                            <small class="text-muted"><b>Example:</b> Image size - 250x55 </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Site Favicon <sup class="text-danger fs-5">*</sup></h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group mb-0">
                                            <input type="file" name="site_favicon" id="input-file-now" class="dropify" data-default-file="{{ $settings['site_favicon'] ? url("uploads/images/{$settings['site_favicon']}") : '' }}" />
                                            <small class="text-muted"><b>Example:</b> Image size - 128x128 </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (Can::access('system_settings', 'update'))
                        <div class="col-lg-12">
                            <div class="card action-btn text-end">
                                <div class="card-body p-2">
                                    <button id="updateBtn" type="button" class="btn btn-success waves-effect waves-light">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        $(function() {
            $('.dropify').dropify().on('dropify.afterClear', function(event, element) {
                const elName = $(element.element).attr('name');
                $(`[name="is_${elName}_file_removed"]`).val('1');
            });

            $(document).on('click','#updateBtn', function(e) {
                e.preventDefault();
                const $btn = $(this);

                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    url: "{{ route('admin.settings.update') }}",
                    data: new FormData($('#updateFrm')[0]),
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
                    }
                });
            });
        });
    </script>
@endsection
