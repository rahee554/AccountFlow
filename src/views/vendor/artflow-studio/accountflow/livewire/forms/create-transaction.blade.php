<div class="container py-4">
    <form wire:submit.prevent="storeTransaction" class="bg-light p-4 rounded shadow-sm">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="row g-3">
                    <!-- Payment Method -->
                    <div class="col-12 col-sm-6">
                        <label for="payment_method" class="form-label text-uppercase fw-bold text-dark fs-sm">Payment Method</label>
                        <select wire:model="payment_method" class="form-select form-select-sm">
                            <option value="">Select Payment Method</option>
                            @foreach ($payment_methods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Account Selection -->
                    <div class="col-12 col-sm-6">
                        <label for="account_id" class="form-label text-uppercase fw-bold text-dark fs-sm">Select Account</label>
                        <select wire:model="account_id" class="form-select form-select-sm">
                            <option value="">Select Account</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('account_id')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Transaction Type -->
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-uppercase fw-bold text-dark fs-sm">Transaction Type</label>
                        <select wire:change="changeType($event.target.value)" class="form-select form-select-sm">
                            <option value="1" {{ $type == 1 ? 'selected' : '' }}>Income</option>
                            <option value="2" {{ $type == 2 ? 'selected' : '' }} selected>Expense</option>
                        </select>
                    </div>

                   

                    <!-- Category -->
                    <div class="col-12 col-sm-6">
                        <label class="form-label text-uppercase fw-bold text-dark fs-sm">Category</label>
                        <select wire:model="category_id" class="form-select form-select-sm">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div class="col-12 col-sm-6">
                        <label for="date" class="form-label text-uppercase fw-bold text-dark fs-sm">Date</label>
                        <input type="date" wire:model="date" class="form-control form-control-sm">
                        @error('date')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>
                     <!-- Amount -->
                     <div class="col-12 col-sm-6">
                        <label for="amount" class="form-label text-uppercase fw-bold text-dark fs-sm">Amount</label>
                        <input type="number" wire:model="amount" class="form-control form-control-sm" placeholder="Enter Amount">
                        @error('amount')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>


                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label text-uppercase fw-bold text-dark fs-sm">Description</label>
                        <textarea wire:model="description" class="form-control form-control-sm" rows="3" placeholder="Details / Description"></textarea>
                        @error('description')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="col-12 mt-4">
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                            <a href="{{route('accountflow::transactions')}}" type="button" class="btn btn-light btn-sm px-4" wire:navigate>Close</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4">Save Record</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>