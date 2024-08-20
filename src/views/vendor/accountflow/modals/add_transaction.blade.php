@php
    $income_categories = App\Models\AccountFlow\Category::select('id', 'name')
        ->where('flow_type', 1)
        ->where('status', 1)
        ->WhereNotNull('parent_id')
        ->get();

    $expense_categories = App\Models\AccountFlow\Category::where('flow_type', 2)
        ->where('status', 1)
        ->WhereNotNull('parent_id')
        ->get();

    $payment_methods = App\Models\AccountFlow\PaymentMethod::where('status', 1)->get();
    $accounts = App\Models\AccountFlow\Account::where('status', 1)->get();
    $loanUsers = App\Models\AccountFlow\LoanUser::get();
@endphp

<div class="modal fade" tabindex="-1" id="transactionModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-5">
                <h5 class="modal-title"> Add Record / Transaction</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon"><i class="fas fa-times"></i></span>
                </div>
            </div>

            <div class="modal-body">
                <div class=" mx-n7 mt-n7 p-7">
                    <div class="d-flex justify-content-center">
                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#income_tab"><i
                                        class="fad fa-badge-check fs-4 me-2"></i> Income</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#expense_tab"><i
                                        class="fad fa-receipt fs-4 me-2"></i> Expense</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#purchase_tab"><i
                                        class="fad fa-money-check-edit"></i> Purchase</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#loan_tab"><i
                                        class="fad fa-landmark"></i> Loan</a>
                            </li>
                        </ul>
                    </div>
                    <div class="row">

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane row fade" id="income_tab" role="tabpanel">
                                <form id="addIncome">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="w-100 bg-success p-5 text-white fs-2 rounded">
                                                <input type="number" name="amount"
                                                    class="form-control form-control-transparent text-white fs-1 fw-bolder text-center placeholder-white"
                                                    placeholder="Rs : 000.00" autofocus>
                                            </div>
                                        </div>



                                        <div class="py-5 col-12 col-md-6">
                                            <label for="type" class="text-uppercase fw-bolder text-gray-600">Payment
                                                Method</label>
                                            <select name="payment_method" class="form-select" id="kt_docs_select2_users"
                                                data-control="select2" data-dropdown-parent="#transactionModal"
                                                data-placeholder="Select Category" data-hide-search="true">


                                                <option value=""></option>
                                                @foreach ($payment_methods as $method)
                                                    <option value="{{ $method->id }}"
                                                        {{ $method->id == 1 ? 'selected' : '' }}>{{ $method->name }}
                                                    </option>
                                                @endforeach


                                            </select>
                                        </div>
                                        <div class="py-5 col-12 col-md-6 income_expense_categories"
                                            id="income_categories">
                                            <label for="category" class="text-uppercase fw-bolder text-gray-600">Select
                                                Account</label>
                                            <select class="form-select" name="account_id" data-control="select2"
                                                data-dropdown-parent="#transactionModal" data-hide-search="false"
                                                data-placeholder="Select an option">

                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        {{ $account->id == 1 ? 'selected' : '' }}>
                                                        {{ $account->name }}
                                                    </option>
                                                @endforeach

                                            </select>

                                        </div>
                                        <div class="py-5 col-12 col-md-6 income_expense_categories"
                                            id="income_categories">
                                            <label for="category"
                                                class="text-uppercase fw-bolder text-gray-600">Category</label>
                                            <select class="form-select" name="category_id" data-control="select2"
                                                data-dropdown-parent="#transactionModal"
                                                data-placeholder="Select an option">

                                                <option value=""></option>
                                                @foreach ($income_categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="py-5 col-12 col-md-6">
                                            <label for="date"
                                                class="text-uppercase fw-bolder text-gray-600">Date</label>
                                            <input type="text" name="date"
                                                class="form-control text-dark fw-bolder" placeholder="Transaction Date"
                                                id="incomeDate">
                                        </div>
                                        @push('scripts')
                                        <script>
                                            $("#incomeDate").flatpickr({
                                                defaultDate: new Date(),
                                                dateFormat: "Y-m-d",
                                                allowInput: true
                                            });
                                        </script>
                                        @endpush
                                       
                                        <div class="py-5 col-12">
                                            <textarea name="description" id="" cols="30" rows="3" class="form-control"
                                                placeholder="Details / Description"></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" id="submit"
                                                class="btn btn-primary">Save</button>
                                        </div>
                                    </div>

                                </form>

                            </div>
                            <div class="tab-pane fade show active" id="expense_tab" role="tabpanel">
                                <form id="addExpense" action="{{ route('accounts.add.expense') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="w-100 bg-danger p-5 text-white fs-2 rounded">
                                                <input type="number" name="amount"
                                                    class="form-control form-control-transparent text-white fs-1 fw-bolder text-center placeholder-white"
                                                    placeholder="Rs : 000.00" autofocus>
                                            </div>
                                        </div>

                                        <input class="btn-check income_expense" type="hidden" name="type"
                                            value="2" />

                                        <div class="py-5 col-12 col-md-6">
                                            <label for="type"
                                                class="text-uppercase fw-bolder text-gray-600">Payment
                                                Method</label>
                                            <select name="payment_method" class="form-select"
                                                id="kt_docs_select2_users" data-control="select2"
                                                data-dropdown-parent="#transactionModal"
                                                data-placeholder="Select Category" data-hide-search="true">


                                                <option value=""></option>
                                                @foreach ($payment_methods as $method)
                                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                                @endforeach


                                            </select>
                                        </div>
                                        <div class="py-5 col-12 col-md-6 income_expense_categories"
                                            id="income_categories">
                                            <label for="category"
                                                class="text-uppercase fw-bolder text-gray-600">Select
                                                Account</label>
                                            <select class="form-select" name="account_id" data-control="select2"
                                                data-dropdown-parent="#transactionModal" data-hide-search="false"
                                                data-placeholder="Select an option">

                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        {{ $account->id == 1 ? 'selected' : '' }}>
                                                        {{ $account->name }}
                                                    </option>
                                                @endforeach

                                            </select>

                                        </div>
                                        <div class="py-5 col-12 col-md-6 income_expense_categories"
                                            id="expense_categories">
                                            <label for="category"
                                                class="text-uppercase fw-bolder text-gray-600">Category</label>
                                            <select class="form-select" name="account_category_id"
                                                data-control="select2" data-dropdown-parent="#transactionModal"
                                                data-placeholder="Select an option">
                                                @foreach ($expense_categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="py-5 col-12 col-md-6">
                                            <label for="date"
                                                class="text-uppercase fw-bolder text-gray-600">Date</label>
                                            <input type="text" name="date" class="form-control  "
                                                placeholder="Transaction Date" id="expense-date">
                                        </div>
                                        <div class="py-5 col-12">
                                            <textarea name="description" id="" cols="30" rows="3" class="form-control"
                                                placeholder="Details / Description"></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"
                                                name="expensetab">Save</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                            <div class="tab-pane fade " id="purchase_tab" role="tabpanel">
                                <form id="purchaseForm" action="{{ route('accounts.add.purchase') }}"
                                    method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="amount" class="text-uppercase fw-bolder text-gray-600">Item
                                                Name</label>
                                            <input type="text" name="item_name" class="form-control"
                                                placeholder="Enter Item Name Here" autofocus="">
                                        </div>
                                        <div class="py-5 col-12 col-md-6 income_expense_categories"
                                            id="expense_categories">
                                            <label for="category"
                                                class="text-uppercase fw-bolder text-gray-600">Category</label>
                                            <select class="form-select" name="account_category_id"
                                                data-dropdown-parent="#transactionModal" data-control="select2"
                                                data-placeholder="Select an option">
                                                @foreach ($expense_categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="date"
                                                class="text-uppercase fw-bolder text-gray-600">Date</label>
                                            <input type="text" name="purchase_date" class="form-control   "
                                                placeholder="Purchase Date" id="purchase-date">
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="type"
                                                class="text-uppercase fw-bolder text-gray-600">Purchase
                                                Type</label>
                                            <select class="form-select" name="purchase_type" id="purchaseType"
                                                data-dropdown-parent="#transactionModal" data-control="select2"
                                                data-hide-search="true">
                                                <option value="0">Cash Payment</option>
                                                <option value="1">Installment</option>
                                            </select>
                                        </div>
                                        <div class="installment_section row">
                                            <div class="py-5 col-12 col-md-6">
                                                <label for="type"
                                                    class="text-uppercase fw-bolder text-gray-600">Number of
                                                    Installments</label>
                                                <input type="number" name="installments"
                                                    class="form-control   text-dark fw-bolder"
                                                    placeholder="eg: 5 Installments">
                                            </div>
                                            <div class="py-5 col-12 col-md-6">
                                                <label for="type"
                                                    class="text-uppercase fw-bolder text-gray-600">Repayment
                                                    Timeframe</label>
                                                <select class="form-select" name="installment_type" id=""
                                                    data-dropdown-parent="#transactionModal" data-control="select2"
                                                    data-hide-search="true">
                                                    <option value="0">Monthly</option>
                                                    <option value="1">Quarterly</option>
                                                    <option value="2">Half Yearly</option>
                                                    <option value="2">Annually</option>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="py-5 col-12 col-md-6">
                                            <label for="total_amount"
                                                class="text-uppercase fw-bolder text-gray-600">Total
                                                Amount</label>
                                            <input type="number" name="amount"
                                                class="form-control   text-dark fw-bolder" placeholder="Rs:00"
                                                autofocus="">
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="total_amount"
                                                class="text-uppercase fw-bolder text-success">Amount
                                                Paid</label>
                                            <input type="number" name="amount_paid"
                                                class="form-control text-dark fw-bolder" placeholder="Rs:00"
                                                autofocus="">
                                        </div>
                                        <div class="py-5 col-12 col-md-6 income_expense_categories"
                                            id="income_categories">
                                            <label for="category"
                                                class="text-uppercase fw-bolder text-gray-600">Select
                                                Account</label>
                                            <select class="form-select" name="account_id" data-control="select2"
                                                data-dropdown-parent="#transactionModal" data-hide-search="false"
                                                data-placeholder="Select an option">

                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        {{ $account->id == 1 ? 'selected' : '' }}>
                                                        {{ $account->name }}
                                                    </option>
                                                @endforeach

                                            </select>

                                        </div>
                                    </div>
                                    <div class="py-5 col-12">
                                        <textarea name="description" id="" cols="30" rows="3" class="form-control"
                                            placeholder="Details / Description" spellcheck="false"></textarea>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-light"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary"
                                            name="purchasetab">Save</button>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="loan_tab" role="tabpanel">
                                <!--begin :: Loan Section-->
                                <form id="loanForm" action="{{ route('accounts.add.loan') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="type"
                                                class="text-uppercase fw-bolder text-gray-600">Date</label>
                                            <input type="text" name="date"
                                                class="form-control   text-dark fw-bolder" placeholder="Select a Date"
                                                id="loan-date">
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="lender_name"
                                                class="text-uppercase fw-bolder text-gray-600">Loan Type</label>
                                            <select name="loan_type" id="" class="form-select"
                                                data-control="select2" data-hide-search="true">
                                                <option value="lended">Lended (اُدھار دینا)</option>
                                                <option value="borrowed">Borrowed(اُدھار لینا)</option>
                                            </select>
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="total_amount"
                                                class="text-uppercase fw-bolder text-gray-600">Name</label>
                                            <input type="text" name="name"
                                                class="form-control   text-dark fw-bolder" placeholder="name"
                                                autofocus="">
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="lender_name"
                                                class="text-uppercase fw-bolder text-gray-600">Loan User Name</label>
                                            <select class="form-select" name="account_category_id"
                                                data-dropdown-parent="#transactionModal" data-control="select2"
                                                data-placeholder="Select an option">
                                                @foreach ($loanUsers as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="total_amount"
                                                class="text-uppercase fw-bolder text-gray-600">Total
                                                Amount</label>
                                            <input type="text" name="amount"
                                                class="form-control text-dark fw-bolder"
                                                placeholder="Enter Lender Name Here">
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="lender_name"
                                                class="text-uppercase fw-bolder text-gray-600">Return
                                                on Investment - ROI</label>
                                            <input type="text" name="roi"
                                                class="form-control   text-dark fw-bolder"
                                                placeholder="Enter Lender Name Here" autofocus="" id="roi">
                                        </div>

                                        <div class="py-5 col-12 col-md-6">
                                            <label for="type"
                                                class="text-uppercase fw-bolder text-gray-600">Number of
                                                Installments</label>
                                            <input type="number" name="installments"
                                                class="form-control   text-dark fw-bolder"
                                                placeholder="eg: 5 Installments">
                                        </div>
                                        <div class="py-5 col-12 col-md-6">
                                            <label for="type"
                                                class="text-uppercase fw-bolder text-gray-600">Repayment
                                                Type</label>
                                            <select class="form-select" name="installment_type" id=""
                                                data-dropdown-parent="#transactionModal" data-control="select2"
                                                data-hide-search="true">
                                                <option value="1">Monthly</option>
                                                <option value="2">Quarterly</option>
                                                <option value="3">Half Yearly</option>
                                                <option value="4">Annually</option>
                                            </select>
                                        </div>

                                        <div class="py-5 col-12">
                                            <textarea name="description" id="" cols="30" rows="3" class="form-control"
                                                placeholder="Loan Amount Details / Description" spellcheck="false"></textarea>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-light"
                                                data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary"
                                                name="loantab">Save</button>
                                        </div>
                                    </div>
                                </form>
                                <!--end :: Purchase Section-->
                            </div>
                        </div>

                    </div>

                </div>

                <!--begin :: Purchase Section-->

                <!--end :: Purchase Section-->

            </div>
            {{-- <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div> --}}

        </div>
    </div>
</div>


@push('modal.js')
    <script>
        $(document).ready(function() {
            // Initial check for visibility
            toggleInstallmentSection();
            // Handle change event of the purchase type select
            $("#purchaseType").change(function() {
                toggleInstallmentSection();
            });

            function toggleInstallmentSection() {
                // Get the selected value
                var selectedValue = $("#purchaseType").val();
                // Toggle visibility based on the selected value
                if (selectedValue === "1") {
                    $(".installment_section").show();
                } else {
                    $(".installment_section").hide();
                }
            }
        });
    </script>


    {{-- <script>
        $(document).ready(function() {
            $('#addIncome').submit(function(e) {
                e.preventDefault();

                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('accounts.add.income') }}",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log(response.message);
                        // Handle success scenario (e.g., show success message)

                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            console.log(errors);
                            // Handle validation errors
                        } else {
                            console.log('Error occurred. Please try again.');
                            console.log(xhr.responseText); // Log full error response
                        }
                    }
                });
            });
        });
    </script> --}}
    @AF_AjaxForm([
        'id' => 'addIncome',
        'route' => 'accounts.add.income',
        'method' => 'post',
        'logType' => 'swal',
        'onSuccess' => [
            'log' => 'swal', // options: 'swal', 'alert', 'console'
            'reload' => true, // true or false
            //'dtable' => 'myDataTable' // ID of the DataTable to reinitialize
        ],
    ])
@endpush
