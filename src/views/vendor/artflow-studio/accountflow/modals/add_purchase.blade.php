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