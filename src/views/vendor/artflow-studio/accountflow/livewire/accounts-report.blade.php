<div class="container">

    <div class="d-flex justify-content-between">
        <h3>Records</h3>
        <div class="w-200px">
            <input type="text" class="form-control form-control-sm" id="datarange" wire:model="dateRange">
        </div>


    </div>

    {{-- <div wire:loading>
        Loading...
    </div> --}}


    <div class="accordion" id="accordionExample">
        
        @foreach ($parentCategories as $index => $parent)
            @if ($parent->total_amount > 0)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $index }}">
                        <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $index }}" aria-expanded="false"
                            aria-controls="collapse-{{ $index }}">
                            <div class="d-flex align-items-center w-100">
                                @if ($parent->icon ?? false)
                                    <img src="{{ asset(config('accountflow.asset_path') . 'icons/accounts_icons/' . $parent->icon) }}"
                                        alt="" class="rounded-circle me-3"
                                        style="width:30px;height:30px;object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3"
                                        style="width:30px;height:30px;font-size:20px;">
                                        {{ strtoupper(substr($parent->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="flex-grow-1 fs-4 text-start">
                                    {{ $parent->name }}
                                </div>
                                <div class="fs-6 me-1" style="margin-right:5px;">
                                    <span class="text-{{ $parent->flow_type == 1 ? 'success' : 'danger' }}">
                                        {{ number_format($parent->total_amount, 2) }}
                                    </span>
                                </div>
                            </div>

                        </button>
                    </h2>
                    <div id="collapse-{{ $index }}" class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $index }}" data-bs-parent="#accordionExample">
                        <div class="accordion-body mx-5">
                            @foreach ($categories->where('parent_id', $parent->id)->where('total_amount', '>', 0) as $child)
                                <div class="d-flex align-items-center border-bottom py-2">
                                    @if ($child->icon ?? false)
                                        <img src="{{ asset(config('accountflow.asset_path') . 'icons/accounts_icons/' . $child->icon) }}"
                                            alt="" class="rounded-circle me-2"
                                            style="width:20px;height:20px;object-fit:cover;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-2"
                                            style="width:20px;height:20px;font-size:16px;">
                                            {{ strtoupper(substr($child->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        {{ $child->name }}
                                    </div>
                                    <div>
                                        {{ number_format($child->total_amount, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

    <div>Total :  </div>
     <!-- Display the total sum after the foreach loop -->
     <div class="accordion-item">
        <div class="accordion-body text-end">
            <strong>Total Amount (Date Range: {{ $startDate }} to {{ $endDate }}): </strong>
            <span>{{ number_format($totalAmount, 2) }}</span>
        </div>
    </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush
@push('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>


    <script>
        $('#datarange').daterangepicker({
            "showDropdowns": true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')]
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
                "daysOfWeek": [
                    "Su",
                    "Mo",
                    "Tu",
                    "We",
                    "Th",
                    "Fr",
                    "Sa"
                ],
                "monthNames": [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                ],
                "firstDay": 1
            },
            "alwaysShowCalendars": true,
            "startDate": "04/21/2025",
            "endDate": "04/27/2025",
            "opens": "left"
        }, function(start, end, label) {
            // Update the Livewire component dateRange when a new date is selected
            @this.set('dateRange', start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
        });
    </script>
@endpush
