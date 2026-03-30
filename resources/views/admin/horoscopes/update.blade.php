@extends('layouts.master')

@section('title') Update Horoscope @endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Update Horoscope</h4>

            <div class="page-title-right">
                <a href="{{ route('admin.horoscopes.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<form id="updateFrm">
    @csrf

    <div class="row">

        {{-- LEFT --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Horoscope Details</h4>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Zodiac</label>
                            <select name="zodiac_id" class="form-control select2-class">
                                @foreach($zodiacs as $zodiac)
                                    <option value="{{ $zodiac->id }}" {{ $horoscope->zodiac_id == $zodiac->id ? 'selected' : '' }}>
                                        {{ $zodiac->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type</label>
                            <select name="type" class="form-control select2-class">
                                @foreach(['today','yesterday','tomorrow','daily','weekly','monthly','yearly'] as $type)
                                    <option value="{{ $type }}" {{ $horoscope->type == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" name="date" class="form-control"
                                   value="{{ $horoscope->date }}">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ $horoscope->title }}">
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ $horoscope->description }}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Love</label>
                            <textarea name="love" class="form-control">{{ $horoscope->love }}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Career</label>
                            <textarea name="career" class="form-control">{{ $horoscope->career }}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Health</label>
                            <textarea name="health" class="form-control">{{ $horoscope->health }}</textarea>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-bold">Finance</label>
                            <textarea name="finance" class="form-control">{{ $horoscope->finance }}</textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="col-lg-4">

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Status</h4>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label fw-bold mb-0">Status</label>

                        <div class="square-switch">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox"
                                   id="square-status"
                                   name="status"
                                   value="1"
                                   {{ $horoscope->status ? 'checked' : '' }}>
                            <label for="square-status" data-on-label="Yes" data-off-label="No"></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lucky Number</label>
                        <input type="text" name="lucky_number" class="form-control" value="{{ $horoscope->lucky_number }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lucky Color</label>
                        <input type="text" name="lucky_color" class="form-control" value="{{ $horoscope->lucky_color }}">
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <button id="updateBtn" class="btn btn-success w-100 mb-2">
                        Update Horoscope
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>
@endsection


@section('script')
<script type="text/javascript">
        $(function() {

            $(document).on('click', '#updateBtn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const formData = new FormData($('#updateFrm')[0]);

                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    url: "{{ route('admin.horoscopes.update', $horoscope->id) }}",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $btn.attr('disabled', true);
                        showToastr('info', 'Updating...');
                    },
                    success: function(response) {
                        showToastr('success', response.message);
                        location.replace('{{ route("admin.horoscopes.index") }}');
                    },
                    error: function(jqXHR, exception) {
                        showToastr('error', formatErrorMessage(jqXHR, exception));
                        $btn.attr('disabled', false);
                    },
                    complete: function() {
                        $btn.attr('disabled', false);
                    }
                });
            });

        });
    </script>
@endsection
