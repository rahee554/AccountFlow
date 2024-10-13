@extends(config('accountflow.layout'))
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar  pt-6 pb-2 ">
        <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex align-items-stretch ">
            <!--begin::Toolbar wrapper-->
            <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                    <!--begin::Title-->
                    <!-- Display validation errors -->
                    {{-- @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}
                    <h1 class="page-heading d-flex flex-column justify-content-center text-dark fw-bold fs-3 m-0">
                        Transactions List
                    </h1>
                    <!--end::Title-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="#" class="btn btn-sm btn-flex btn-outline"
                    data-bs-toggle="modal" data-bs-target="#multipleTrxModal">
                    Add Multiple Records
                </a>

                    <a href="#" class="btn btn-sm btn-flex btn-primary"
                        data-bs-toggle="modal" data-bs-target="#transactionModal">
                        Add Record
                    </a>

                   

                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar wrapper-->
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-fluid">
        <!--begin::Content-->

        {{--         
@appDefault('accounts', 'default_payment_method')
Value for 'desired_key': {{ $default_payment_method }} --}}


        <input type="text" class="form-control form-control-sm" id="date_range" placeholder="Select Range">
        @AF_dtable_btns(['search' => true, 'colvis' => true, 'export_btn' => true])
        <div class="border rounded p-md-10 mt-2">
            <table class="table align-middle table-row-dashed fs-6 g-5 px-5" id="trx_dtable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Details</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Account</th>
                        <th>Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
        </div>
        @AF_dtable([
            //Table ID
            'index' => '1',
            'id' => 'trx_dtable',
            'route' => 'accounts.get.trx',
            'cols' => ['unique_id', 'amount', 'details', 'date', 'category', 'account', 'method', 'actions'],
            'cols_class' => [['0', 'text-gray-700'], ['1', 'text-gray-900 fw-bold']],
        ])



        <!--end::Content-->
    </div>
    <!--end::Content container-->

    @include(config('accountflow.view_path') . 'modals.add_transaction')
    @include(config('accountflow.view_path') . 'modals.add_transaction_multiple')
    @include(config('accountflow.view_path') . 'modals.edit_record')
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#date_range').daterangepicker({
                "showDropdowns": true,
                "autoApply": true,

                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year')
                        .endOf('year')
                    ]
                },
                "locale": {
                    "format": "MM/DD/YYYY",
                    "separator": " - ",
                    "applyLabel": "Apply",
                    "cancelLabel": "Clear",
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
                // "startDate": "06/16/2024",
                // "endDate": "06/22/2024",
                "drops": "auto"
            }, function(start, end, label) {
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format(
                    'YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });
        });
    </script>
@endpush


<script>
    $('.delete-btn').on('click', function() {
        const transactionId = $(this).data('id'); // Get the transaction ID from data-id attribute

        // Show SweetAlert confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Make the AJAX request to delete the transaction
                $.ajax({
                    url: `/accounts/delete-transaction/${transactionId}`, // Adjust the URL as needed
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            response.message,
                            'success'
                        );
                        // Optionally, remove the deleted transaction from the UI
                        $(`button[data-id="${transactionId}"]`).closest('tr')
                            .remove(); // Adjust selector based on your HTML structure
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'There was an error deleting the transaction.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>
