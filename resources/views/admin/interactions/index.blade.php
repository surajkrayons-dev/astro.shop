@extends('layouts.master')

@section('title') Interactions @endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">

            <h4 class="mb-sm-0 font-size-18">Astrologer–User Interactions</h4>

        </div>
    </div>
</div>

<!-- FILTER SECTION -->
<div class="row">
    <div class="col-12">

        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Filter</h4>

                <button id="reset-filter-btn" class="btn btn-light">
                    <i class="fa fa-undo"></i> Reset
                </button>
            </div>

            <div class="card-body">

                <div class="row">

                    <!-- Astrologer Filter -->
                    <div class="col">
                        <label class="form-label fw-bold">Astrologer</label>
                        <select id="astrologer_id" class="form-control select2-class2"
                            data-placeholder="Select Astrologer">
                            <option value=""></option>
                            @foreach(\App\Models\User::where('type','astro')->get() as $astro)
                            <option value="{{ $astro->id }}">
                                {{ $astro->code }} - {{ $astro->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div class="col">
                        <label class="form-label fw-bold">User</label>
                        <select id="user_id" class="form-control select2-class2" data-placeholder="Select User">
                            <option value=""></option>
                            @foreach(\App\Models\User::where('type','user')->get() as $u)
                            <option value="{{ $u->id }}">
                                {{ $u->code }} - {{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label fw-bold">Interaction Type</label>
                        <select id="interaction_type" class="form-control select2-class2"
                            data-placeholder="Select Type">
                            <option value=""></option>
                            <option value="CHAT">Chat</option>
                            <option value="CALL">Call</option>
                        </select>
                    </div>

                    <!-- Country Filter -->
                    <div class="col">
                        <label class="form-label fw-bold">Country</label>
                        <select id="country" class="form-control select2-class2" data-placeholder="Select Country">
                            <option value=""></option>
                        </select>
                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

<!-- TABLE SECTION -->
<div class="row">
    <div class="col-12">
        <div class="card border">

            <div class="card-body">

                <table id="interaction-table" class="table table-bordered dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>Astrologer</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Country</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>

            </div>

        </div>
    </div>
</div>

@endsection

@section('script')

<script type="text/javascript">
$(document).ready(function() {

    const reloadTable = () => {
        $('#interaction-table').DataTable().ajax.reload();
    };

    // DATATABLE
    $('#interaction-table').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
            url: '{{ route("admin.interactions.list") }}',
            data: function(d) {
                d.astrologer_id = $('#astrologer_id').val();
                d.user_id = $('#user_id').val();
                d.interaction_type = $('#interaction_type').val();
                d.country = $('#country').val();
            }
        },

        columns: [{
                data: 'astro_name',
                name: 'astro_name'
            },
            {
                data: 'user_name',
                name: 'user_name'
            },
            {
                data: 'interaction_type'
            },
            {
                data: 'country',
                name: 'country'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: null,
                className: "text-center",
                render: function(row) {
                    return `
                        <a href="javascript:void(0);"
                        data-href="{{ route('admin.interactions.view') }}/${row.id}?type=${row.interaction_type}"
                        class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal"
                        data-target="#xlRemoteModal">
                        <i class="mdi mdi-eye font-size-16"></i>
                        </a>
                    `;
                }

            }
        ]
    });

    // Filter Change
    $('#astrologer_id, #user_id, #country, #interaction_type').on('change', function() {
        reloadTable();
    });

    // Reset button
    $('#reset-filter-btn').on('click', function() {
        $('#astrologer_id').val('').trigger('change');
        $('#user_id').val('').trigger('change');
        $('#interaction_type').val('').trigger('change');
        $('#country').val('').trigger('change');
        reloadTable();
    });

});
</script>
<script>
fetch('https://restcountries.com/v3.1/all?fields=name,idd')
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('country');

        data.forEach(c => {
            if (c.idd?.root && c.idd?.suffixes) {
                c.idd.suffixes.forEach(s => {
                    const code = c.idd.root + s; // +91
                    const opt = document.createElement('option');
                    opt.value = code;
                    opt.textContent = `${c.name.common} (${code})`;
                    select.appendChild(opt);
                });
            }
        });
    });
</script>


@endsection