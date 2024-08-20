@php
    $accounts = App\Models\AccountFlow\Account::where('status', 1)->get();
@endphp

<div class="modal fade" tabindex="-1" id="accounts_transfer">
    <div class="modal-dialog modal-lg">
        <form id="addTransfers">
            @csrf
            <div class="modal-content">
                <div class="modal-header py-5">
                    <h5 class="modal-title"> Add Transfers</h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span class="svg-icon"><i class="fas fa-times"></i></span>
                    </div>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-12">
                            <div class="w-100 bg-success p-5 text-white fs-2 rounded">
                                <input type="number" name="amount"
                                    class="form-control form-control-transparent text-white fs-1 fw-bolder text-center placeholder-white"
                                    placeholder="Rs : 000.00" autofocus required>
                            </div>
                        </div>
                        <div class="py-5 col-12 col-md-4">
                            <label for="date" class="text-uppercase fw-bolder text-gray-600">Date</label>
                            <input type="text" name="date" class="form-control text-dark fw-bolder"
                                placeholder="Transaction Date" id="income-date">
                        </div>

                        <div class="py-5 col-12 col-md-4 income_expense_categories" id="income_categories">
                            <label for="category" class="text-uppercase fw-bolder text-gray-600">From Acount</label>
                            <select class="form-select" name="from_account" data-control="select2"
                                data-dropdown-parent="#accounts_transfer" data-hide-search="false"
                                data-placeholder="Select an option">

                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $account->id == 1 ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="py-5 col-12 col-md-4 income_expense_categories" id="income_categories">
                            <label for="category" class="text-uppercase fw-bolder text-gray-600">To Acount</label>
                            <select class="form-select" name="to_account" data-control="select2"
                                data-dropdown-parent="#accounts_transfer" data-hide-search="false"
                                data-placeholder="Select an option">

                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $account->id == 1 ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>



                        <div class="py-5 col-12">
                            <textarea name="description" id="" cols="30" rows="3" class="form-control"
                                placeholder="Details / Description"></textarea>
                        </div>

                    </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
        </form>
    </div>
</div>


@AF_AjaxForm([
    'id' => 'addTransfers',
    'route' => 'accounts.add.transfer',
    'method' => 'post',
    'logType' => 'swal',
    'onSuccess' => [
        'log' => 'swal', // options: 'swal', 'alert', 'console'
        'reload' => false, // true or false
        'dtable' => 'transfers_dtable', // ID of the DataTable to reinitialize
    ],
])
