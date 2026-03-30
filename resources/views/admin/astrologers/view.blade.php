<div class="modal-header">
    <h5 class="modal-title">Astrologer Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div class="row">

        <!-- PROFILE IMAGE -->
        <div class="col-md-3 text-center">
            <img src="{{ $astro->profile_image_url }}" class="img-fluid rounded shadow"
                style="width: 80%; height: 240px; object-fit: cover;">
            <h5 class="mt-3 text-primary">{{ $astro->name }}</h5>
            <p class="text-muted">{{ $astro->code }}</p>
        </div>

        <!-- BASIC DETAILS -->
        <div class="col-md-3">
            <h5 class="text-primary mb-2">Basic Information</h5>
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Astrologer Code</th>
                    <td>{{ $astro->code }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $astro->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $astro->email }}</td>
                </tr>
                <tr>
                    <th>Country Code</th>
                    <td>{{ $astro->country_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Mobile</th>
                    <td>{{ $astro->mobile ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>DOB</th>
                    <td>{{ $astro->dob ? $astro->dob->format('d M Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td>{{ $astro->gender ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Experience</th>
                    <td>{{ $astro->experience }} years</td>
                </tr>
                <tr>
                    <th>Daily Hours</th>
                    <td>{{ $astro->daily_available_hours }} hrs</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="{{ $astro->status ? 'text-success' : 'text-danger' }}">
                            {{ $astro->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- EXPERTISE & PRICING -->
        <div class="col-md-3">
            <h5 class="text-primary mb-2">Expertise & Pricing</h5>
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Expertise</th>
                    <td>
                        @if (!empty($astro->expertise))
                            @foreach ($astro->expertise as $skill)
                                <span class="badge bg-info text-dark">{{ ucfirst($skill) }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Category</th>
                    <td>
                        @if (!empty($astro->category))
                            @foreach ($astro->category as $cat)
                                <span class="badge bg-info text-dark">{{ ucfirst($cat) }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Astro Education</th>
                    <td>
                        @if (!empty($astro->astro_education))
                            @foreach ($astro->astro_education as $edu)
                                <span class="badge bg-info text-dark">{{ ucfirst($edu) }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Languages</th>
                    <td>
                        @if (!empty($astro->languages))
                            @foreach ($astro->languages as $lang)
                                <span class="badge bg-success text-dark">{{ ucfirst($lang) }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </td>
                </tr>

                <tr>
                    <th>Call Price</th>
                    <td>₹ {{ $astro->call_price }} / min</td>
                </tr>
                <tr>
                    <th>Chat Price</th>
                    <td>₹ {{ $astro->chat_price }} / min</td>
                </tr>

                <tr>
                    <th>Id Proof</th>
                    <td>{{ $astro->id_proof ?? 'N/A' }}</td>
                </tr>

                <tr>
                    <th>Certificate</th>
                    <td>{{ $astro->certificate ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- WALLET / EARNINGS SUMMARY -->
        <div class="col-md-3">
            <h5 class="text-primary mb-2">Earnings Summary</h5>

            <table class="table table-bordered table-striped">
                <tr>
                    <th>Wallet Balance</th>
                    <td>₹ {{ number_format($astro->wallet->balance ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Earned</th>
                    <td>₹ {{ number_format($astro->wallet->total_earned ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Withdrawn</th>
                    <td>₹ {{ number_format($astro->wallet->total_withdrawn ?? 0, 2) }}</td>
                </tr>

                <tr>
                    <th>Total Call Earnings</th>
                    <td>₹ {{ number_format($call_history->sum('amount'), 2) }}</td>
                </tr>
                <tr>
                    <th>Total Chat Earnings</th>
                    <td>₹ {{ number_format($chat_history->sum('amount'), 2) }}</td>
                </tr>

                <tr>
                    <th>Total Call Minutes</th>
                    <td>{{ floor($call_history->sum('duration') / 60) }} mins</td>
                </tr>
                <tr>
                    <th>Total Chat Minutes</th>
                    <td>{{ floor($chat_history->sum('duration') / 60) }} mins</td>
                </tr>
            </table>
        </div>

    </div>

    <hr>

    <div class="row mb-3">
        <div class="col-md-3">
            <label class="fw-bold">Filter Earnings By Date :</label>
            <input type="text" id="earnings_date_range" class="form-control" />
        </div>
    </div>

    <!-- History DETAILS -->
    <div class="row">

        <!--<div class="col-md-6">-->
        <!--    <h5 class="text-primary">Call History</h5>-->
        <!--    <div style="max-height: 300px; overflow-y: auto;">-->
        <!--        <table class="table table-bordered">-->
        <!--            <thead>-->
        <!--                <tr>-->
        <!--                    <th>User</th>-->
        <!--                    <th>Start</th>-->
        <!--                    <th>End</th>-->
        <!--                    <th>Duration</th>-->
        <!--                    <th>Amount (₹)</th>-->
        <!--                    <th>Status</th>-->
        <!--                </tr>-->
        <!--            </thead>-->
        <!--            <tbody id="call_earnings_body">-->
        <!--                @forelse($call_history as $c)
-->
        <!--                <tr>-->
        <!--                    <td>-->
        <!--                        <b>{{ $c->user->code }}</b><br>-->
        <!--                        {{ $c->user->name }}-->
        <!--                    </td>-->
        <!--                    <td>{{ $c->started_at }}</td>-->
        <!--                    <td>{{ $c->ended_at }}</td>-->
        <!--                    <td>{{ $c->duration }} sec</td>-->
        <!--                    <td>₹ {{ number_format($c->amount, 2) }}</td>-->
        <!--                    <td>{{ ucfirst($c->status) }}</td>-->
        <!--                </tr>-->
    <!--                @empty-->
        <!--                <tr>-->
        <!--                    <td colspan="6" class="text-muted text-center">No call history</td>-->
        <!--                </tr>-->
        <!--
@endforelse-->
        <!--            </tbody>-->
        <!--        </table>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="col-md-6">
            <h5 class="text-primary">Call History</h5>

            <div class="table-responsive border rounded" style="max-height: 230px; overflow-y: auto;">

                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>User</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                            <th>Amount (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($call_history as $c)
                            <tr>
                                <td>
                                    <b>{{ $c->user->code }}</b><br>
                                    {{ $c->user->name }}
                                </td>
                                <td>{{ $c->started_at }}</td>
                                <td>{{ $c->ended_at }}</td>
                                <td>{{ $c->duration }} sec</td>
                                <td>₹ {{ number_format($c->amount, 2) }}</td>
                                <td>{{ ucfirst($c->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No call history
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

        <!--<div class="col-md-6">-->
        <!--    <h5 class="text-primary">Chat History</h5>-->
        <!--    <div style="max-height: 300px; overflow-y: auto;">-->
        <!--        <table class="table table-bordered">-->
        <!--            <thead>-->
        <!--                <tr>-->
        <!--                    <th>User</th>-->
        <!--                    <th>Start</th>-->
        <!--                    <th>End</th>-->
        <!--                    <th>Duration</th>-->
        <!--                    <th>Amount (₹)</th>-->
        <!--                    <th>Status</th>-->
        <!--                </tr>-->
        <!--            </thead>-->
        <!--            <tbody id="chat_earnings_body">-->
        <!--                @forelse($chat_history as $ch)
-->
        <!--                <tr>-->
        <!--                    <td>-->
        <!--                        <b>{{ $ch->user->code }}</b><br>-->
        <!--                        {{ $ch->user->name }}-->
        <!--                    </td>-->
        <!--                    <td>{{ $ch->started_at }}</td>-->
        <!--                    <td>{{ $ch->ended_at }}</td>-->
        <!--                    <td>{{ $ch->duration }} sec</td>-->
        <!--                    <td>₹ {{ number_format($ch->amount, 2) }}</td>-->
        <!--                    <td>{{ ucfirst($ch->status) }}</td>-->
        <!--                </tr>-->
    <!--                @empty-->
        <!--                <tr>-->
        <!--                    <td colspan="6" class="text-muted text-center">No chat history</td>-->
        <!--                </tr>-->
        <!--
@endforelse-->
        <!--            </tbody>-->
        <!--        </table>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="col-md-6">
            <h5 class="text-primary">Chat History</h5>

            <div class="table-responsive border rounded" style="max-height: 230px; overflow-y: auto;">

                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>User</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                            <th>Amount (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($chat_history as $ch)
                            <tr>
                                <td>
                                    <b>{{ $ch->user->code }}</b><br>
                                    {{ $ch->user->name }}
                                </td>
                                <td>{{ $ch->started_at }}</td>
                                <td>{{ $ch->ended_at }}</td>
                                <td>{{ $ch->duration }} sec</td>
                                <td>₹ {{ number_format($ch->amount, 2) }}</td>
                                <td>{{ ucfirst($ch->status) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No chat history
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>

    <hr>

    <!-- RATINGS RECEIVED -->
    <div class="row mt-3">
        <div class="col-md-12">
            <h5 class="text-primary">Ratings & Reviews Received</h5>

            <!-- Summary -->
            <p>
                <strong>Total Ratings: </strong> {{ $astro->rating_count }}<br>
                <strong>Average Rating: </strong>
                {{ number_format($astro->rating, 1) }}
                <span class="text-warning">
                    @for ($i = 1; $i <= 5; $i++)
                        {!! $i <= round($astro->rating) ? '&#9733;' : '&#9734;' !!}
                    @endfor
                </span>
            </p>

            <!-- Rating Breakdown -->
            @php
                // Prepare rating distribution (1 → 5)
                $rating_distribution = [
                    5 => $latest_reviews->where('rating', 5)->count(),
                    4 => $latest_reviews->where('rating', 4)->count(),
                    3 => $latest_reviews->where('rating', 3)->count(),
                    2 => $latest_reviews->where('rating', 2)->count(),
                    1 => $latest_reviews->where('rating', 1)->count(),
                ];
            @endphp

            <h6 class="text-primary">Rating Breakdown</h6>

            <table class="table table-sm mb-3">
                @foreach ($rating_distribution as $star => $count)
                    @php
                        $percent = $astro->rating_count > 0 ? ($count / $astro->rating_count) * 100 : 0;
                    @endphp
                    <tr>
                        <td width="80px">
                            @for ($i = 1; $i <= $star; $i++)
                                <span class="text-warning">&#9733;</span>
                            @endfor
                        </td>
                        <td width="40px">{{ $count }}</td>
                        <td>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $percent }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>

            <!-- Review List (Scrollable) -->
            <!-- <h6 class="text-primary">Latest Reviews</h6>

            <div style="max-height: 350px; overflow-y: auto;">
                @forelse($latest_reviews as $r)
                    <div class="border p-2 mb-2 rounded">
                        <strong>User:</strong> {{ $r->user->name }} <br>
                        <strong>Rating:</strong>
                        <span class="text-warning">
                            @for ($i = 1; $i <= 5; $i++)
{!! $i <= $r->rating ? '&#9733;' : '&#9734;' !!}
@endfor
                        </span><br>

                        <strong>Review:</strong> {{ $r->review ?? 'No comment' }}<br>

                        <small class="text-muted">
                            {{ $r->created_at->format('d M Y h:i A') }}
                        </small>
                    </div>
            @empty
                    <p class="text-muted">No reviews yet.</p>
                @endforelse
            </div> -->

        </div>
    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

<script>
    function initAstroEarningsFilter() {

        // अगर input exist नहीं तो return कर दो
        if (!$('#earnings_date_range').length) {
            return;
        }

        // पहले से initialized हो तो destroy कर दो
        if ($('#earnings_date_range').data('daterangepicker')) {
            $('#earnings_date_range').data('daterangepicker').remove();
        }

        let datePickerRanges = {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 15 Days': [moment().subtract(14, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [
                moment().subtract(1, 'month').startOf('month'),
                moment().subtract(1, 'month').endOf('month')
            ]
        };

        let datePickerLocale = {
            format: 'YYYY-MM-DD',
            applyLabel: 'Apply',
            cancelLabel: 'Cancel',
            customRangeLabel: 'Custom'
        };

        $('#earnings_date_range').daterangepicker({
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            ranges: datePickerRanges,
            locale: datePickerLocale
        }, function(start, end) {
            loadEarningsData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });
    }

    function loadEarningsData(startDate, endDate) {

        $.ajax({
            url: "{{ route('admin.astrologers.astro.earnings.filter') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: "{{ $astro->id }}",
                start_date: startDate,
                end_date: endDate
            },
            success: function(res) {

                /* ================= CALL ================= */
                let callHtml = '';
                if (res.call_history.length) {
                    res.call_history.forEach(r => {
                        callHtml += `
                            <tr>
                                <td><b>${r.user_code}</b><br>${r.user_name}</td>
                                <td>${r.started_at}</td>
                                <td>${r.ended_at}</td>
                                <td>${r.duration} sec</td>
                                <td>₹ ${r.amount}</td>
                                <td>${r.status}</td>
                            </tr>
                        `;
                    });
                } else {
                    callHtml = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No call history
                            </td>
                        </tr>
                    `;
                }
                $('#call_earnings_body').html(callHtml);

                /* ================= CHAT ================= */
                let chatHtml = '';
                if (res.chat_history.length) {
                    res.chat_history.forEach(r => {
                        chatHtml += `
                            <tr>
                                <td><b>${r.user_code}</b><br>${r.user_name}</td>
                                <td>${r.started_at}</td>
                                <td>${r.ended_at}</td>
                                <td>${r.duration} sec</td>
                                <td>₹ ${r.amount}</td>
                                <td>${r.status}</td>
                            </tr>
                        `;
                    });
                } else {
                    chatHtml = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No chat history
                            </td>
                        </tr>
                    `;
                }
                $('#chat_earnings_body').html(chatHtml);
            }
        });
    }

    $(document).ready(function() {
        initAstroEarningsFilter();
    });
</script>
