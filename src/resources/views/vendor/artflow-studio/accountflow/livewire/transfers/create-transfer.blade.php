<div>
          @include(config('accountflow.view_path') . '.blades.dashboard-header')
    <div class="px-2 px-md-5 px-lg-10">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header {{ $isEdit ? 'bg-warning text-dark' : 'bg-success text-white' }}">
            <h4 class="mb-0">
                <i class="fas fa-{{ $isEdit ? 'edit' : 'exchange-alt' }} me-2"></i>
                {{ $isEdit ? 'Edit Transfer' : 'Create New Transfer' }}
            </h4>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="addTransfer">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="row g-3">
                            <!-- From Account -->
                            <div class="col-12 col-sm-6">
                                <label for="from_account" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    From Account <span class="text-danger">*</span>
                                </label>
                                <select wire:model="from_account" 
                                        id="from_account"
                                        class="form-select form-select-sm @error('from_account') is-invalid @enderror">
                                    <option value="">Select Source Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} (Balance: PKR {{ number_format($account->balance, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('from_account')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($from_account)
                                    <div class="form-text text-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Current Balance: <strong>PKR {{ number_format($this->fromAccountBalance, 2) }}</strong>
                                    </div>
                                @endif
                            </div>

                            <!-- To Account -->
                            <div class="col-12 col-sm-6">
                                <label for="to_account" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    To Account <span class="text-danger">*</span>
                                </label>
                                <select wire:model="to_account" 
                                        id="to_account"
                                        class="form-select form-select-sm @error('to_account') is-invalid @enderror">
                                    <option value="">Select Destination Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} (Balance: PKR {{ number_format($account->balance, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('to_account')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($to_account)
                                    <div class="form-text text-success">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Current Balance: <strong>PKR {{ number_format($this->toAccountBalance, 2) }}</strong>
                                    </div>
                                @endif
                            </div>

                            <!-- Amount -->
                            <div class="col-12 col-sm-6">
                                <label for="amount" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Transfer Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-primary text-white">PKR</span>
                                    <input type="number" 
                                           wire:model="amount" 
                                           id="amount"
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           placeholder="Enter amount"
                                           step="0.01"
                                           min="0.01">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($amount && $from_account && $amount > $this->fromAccountBalance)
                                    <div class="form-text text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Amount exceeds available balance
                                    </div>
                                @endif
                            </div>

                            <!-- Date -->
                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Transfer Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       wire:model="date" 
                                       id="date"
                                       class="form-control form-control-sm @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Description / Notes
                                </label>
                                <textarea wire:model="description" 
                                          id="description"
                                          class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                          rows="3" 
                                          placeholder="Transfer description or notes"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Fields marked with <span class="text-danger">*</span> are required
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('accountflow::transfers.list') }}" 
                                           class="btn btn-secondary btn-sm px-4" 
                                           wire:navigate>
                                            <i class="fas fa-arrow-left me-1"></i>
                                            {{ $isEdit ? 'Back to List' : 'Cancel' }}
                                        </a>
                                        <button type="submit" 
                                                class="btn btn-{{ $isEdit ? 'warning' : 'success' }} btn-sm px-4"
                                                wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                <i class="fas fa-{{ $isEdit ? 'save' : 'exchange-alt' }} me-1"></i>
                                                {{ $isEdit ? 'Update Transfer' : 'Process Transfer' }}
                                            </span>
                                            <span wire:loading>
                                                <i class="fas fa-spinner fa-spin me-1"></i>
                                                {{ $isEdit ? 'Updating...' : 'Processing...' }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfer Preview -->
    @if($amount && $from_account && $to_account && $from_account != $to_account)
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fas fa-eye me-2"></i>{{ $isEdit ? 'Updated Transfer Preview' : 'Transfer Preview' }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <div class="d-flex flex-column align-items-center">
                        <div class="bg-danger text-white p-3 rounded-circle mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-minus fa-lg"></i>
                        </div>
                        <h6 class="mb-0">From</h6>
                        @php
                            $fromAccountName = $accounts->firstWhere('id', $from_account)->name ?? 'Unknown';
                        @endphp
                        <span class="text-muted">{{ $fromAccountName }}</span>
                        <span class="badge bg-danger">- PKR {{ number_format($amount, 2) }}</span>
                    </div>
                </div>
                
                <div class="col-md-4 text-center">
                    <i class="fas fa-arrow-right fa-2x text-primary"></i>
                    <div class="mt-2">
                        <h5 class="text-primary mb-0">PKR {{ number_format($amount, 2) }}</h5>
                        @if($date)
                            <small class="text-muted">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</small>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="d-flex flex-column align-items-center">
                        <div class="bg-success text-white p-3 rounded-circle mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-plus fa-lg"></i>
                        </div>
                        <h6 class="mb-0">To</h6>
                        @php
                            $toAccountName = $accounts->firstWhere('id', $to_account)->name ?? 'Unknown';
                        @endphp
                        <span class="text-muted">{{ $toAccountName }}</span>
                        <span class="badge bg-success">+ PKR {{ number_format($amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            @if($description)
            <div class="row mt-3">
                <div class="col-12 text-center">
                    <div class="bg-light p-3 rounded">
                        <strong>Description:</strong> {{ $description }}
                    </div>
                </div>
            </div>
            @endif
            
            @if($amount && $from_account && $amount > $this->fromAccountBalance)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Transfer amount exceeds available balance in source account.
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div></div>