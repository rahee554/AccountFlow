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