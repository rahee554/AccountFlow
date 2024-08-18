<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Financial Summary</h3>
        <div class="w-200px">
            <input type="text" class="form-control form-control-sm" id="datarange" wire:model="dateRange">
        </div>
    </div>

    {{-- Display any error messages --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="text-center my-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Show message if no data --}}
    @php
        $visibleParents = $parentCategories->filter(fn($p) => (($p->net ?? 0) != 0));
    @endphp
    @if($visibleParents->count() == 0)
        <div class="alert alert-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> No Data Found</h5>
            <p>No transactions found for the selected date range.</p>
            <small class="text-muted">Try selecting a different date range or check if you have any transactions
                recorded.</small>
        </div>
    @endif

    <div class="accordion" id="accordionExample">
        @foreach ($visibleParents as $index => $parent)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-{{ $parent->id }}">
                    <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $parent->id }}" aria-expanded="false"
                        aria-controls="collapse-{{ $parent->id }}">
                        <div class="d-flex align-items-center w-100">
                            @if ($parent->icon ?? false)
                                <img src="{{ asset(config('accountflow.asset_path') . 'icons/accounts_icons/' . $parent->icon) }}"
                                    alt="" class="rounded-circle me-3" style="width:30px;height:30px;object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3"
                                    style="width:30px;height:30px;font-size:20px;">
                                    {{ strtoupper(substr($parent->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-grow-1 fs-4 text-start">
                                {{ $parent->name }}
                            </div>
                            <div class="fs-6 me-1" style="margin-right:5px; text-align:right;">
                                @php $pnet = $parent->net ?? 0; @endphp
                                <div>
                                    <span class="fw-semibold text-{{ $pnet >= 0 ? 'success' : 'danger' }}">
                                        {{ $pnet >= 0 ? '+' : '-' }} {{ config('accountflow.currency', 'Rs.') }} {{ number_format(abs($pnet), 2) }}
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    Inc: {{ number_format($parent->total_income ?? 0, 2) }} • Exp: {{ number_format($parent->total_expense ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse-{{ $parent->id }}" class="accordion-collapse collapse"
                    aria-labelledby="heading-{{ $parent->id }}" data-bs-parent="#accordionExample">
                    <div class="accordion-body mx-5">
                        @php
                            $childCategories = $categories->where('parent_id', $parent->id)->filter(fn($c) => (($c->net ?? 0) != 0));
                        @endphp

                        @if($childCategories->count() > 0)
                            @foreach ($childCategories as $child)
                                <div class="d-flex align-items-center border-bottom py-2">
                                    @if ($child->icon ?? false)
                                        <img src="{{ asset(config('accountflow.asset_path') . 'icons/accounts_icons/' . $child->icon) }}"
                                            alt="" class="rounded-circle me-2" style="width:20px;height:20px;object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2"
                                            style="width:20px;height:20px;font-size:12px;">
                                            {{ strtoupper(substr($child->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        {{ $child->name }}
                                    </div>
                                    @php $cnet = $child->net ?? 0; @endphp
                                    <div class="fw-bold text-{{ $cnet >= 0 ? 'success' : 'danger' }} text-end">
                                        {{ $cnet >= 0 ? '+' : '-' }} {{ config('accountflow.currency', 'Rs.') }} {{ number_format(abs($cnet), 2) }}
                                        <div class="small text-muted">Inc: {{ number_format($child->total_income ?? 0,2) }} • Exp: {{ number_format($child->total_expense ?? 0,2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted text-center py-3">
                                <i class="fas fa-info-circle"></i> No subcategory transactions found for this period.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Display the total sum -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="card-title mb-0">Summary</h5>
                    <small class="text-muted">
                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    </small>
                </div>
                <div class="col-md-4 text-end">
                    <h4 class="mb-0">
                        <span class="badge bg-primary fs-6">
                            Total: {{ number_format($totalAmount, 2) }}
                        </span>
                    </h4>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

@push('scripts')
 
    <script>
        $(document).ready(function () {
            $('#datarange').daterangepicker({
                "showDropdowns": true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                "locale": {
                    "format": "MM/DD/YYYY",
                    "separator": " - ",
                    "applyLabel": "Apply",
                    "cancelLabel": "Cancel",
                    "fromLabel": "From",
                    "toLabel": "To",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
                    "monthNames": [
                        "January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"
                    ],
                    "firstDay": 1
                },
                "alwaysShowCalendars": true,
                "startDate": moment().startOf('month'), // Fixed: Use current month instead of future date
                "endDate": moment().endOf('month'),     // Fixed: Use current month instead of future date
                "opens": "left"
            }, function (start, end, label) {
                @this.set('dateRange', start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
            });
        });
    </script>
@endpush