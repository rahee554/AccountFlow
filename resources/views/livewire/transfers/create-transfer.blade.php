<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::transfers.list') }}" wire:navigate>Transfers</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }}</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Transfer' : 'Add Transfer' }}</h1></div>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::transfers.list') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    @endunless

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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="addTransfer">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="from_account" class="form-label">
                                    From Account <span class="text-danger">*</span>
                                </label>
                                <select wire:model="from_account" id="from_account"
                                    class="form-select @error('from_account') is-invalid @enderror">
                                    <option value="">Select Source Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($account->balance, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('from_account')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($from_account)
                                    <div class="form-text">
                                        <i data-lucide="wallet"></i>
                                        Current balance: {{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($this->fromAccountBalance, 2) }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="to_account" class="form-label">
                                    To Account <span class="text-danger">*</span>
                                </label>
                                <select wire:model="to_account" id="to_account"
                                    class="form-select @error('to_account') is-invalid @enderror">
                                    <option value="">Select Destination Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($account->balance, 2) }})</option>
                                    @endforeach
                                </select>
                                @error('to_account')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($to_account)
                                    <div class="form-text text-success">
                                        <i data-lucide="wallet"></i>
                                        Current balance: {{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($this->toAccountBalance, 2) }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="amount" class="form-label">
                                    Transfer Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" wire:model="amount" id="amount"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        placeholder="0.00" step="0.01" min="0.01">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($amount && $from_account && $amount > $this->fromAccountBalance)
                                    <div class="form-text text-danger">
                                        <i data-lucide="triangle-alert"></i> Amount exceeds available balance
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label">
                                    Transfer Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" wire:model="date" id="date"
                                    class="form-control @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description / Notes</label>
                                <textarea wire:model="description" id="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Transfer description or notes"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::transfers.list') }}" class="btn btn-soft-secondary" wire:navigate>
                                    {{ $isEdit ? 'Back to List' : 'Cancel' }}
                                </a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>{{ $isEdit ? 'Update Transfer' : 'Process Transfer' }}</span>
                                    <span wire:loading>{{ $isEdit ? 'Updating…' : 'Processing…' }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Preview</h2></div>
                <div class="card-body">
                    @if($amount && $from_account && $to_account && $from_account != $to_account)
                        @php
                            $fromAccountName = $accounts->firstWhere('id', $from_account)->name ?? 'Unknown';
                            $toAccountName = $accounts->firstWhere('id', $to_account)->name ?? 'Unknown';
                        @endphp
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box icon-box-primary"><i data-lucide="repeat"></i></span>
                            <div>
                                <div class="fs-5 fw-semibold">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($amount, 2) }}</div>
                                <div class="text-body-secondary text-size-sm">{{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '' }}</div>
                            </div>
                        </div>
                        <ul class="list-divided mb-3">
                            <li class="list-row d-flex justify-content-between">
                                <span class="text-body-secondary">From</span>
                                <span class="fw-semibold">{{ $fromAccountName }}</span>
                            </li>
                            <li class="list-row d-flex justify-content-between">
                                <span class="text-body-secondary">To</span>
                                <span class="fw-semibold">{{ $toAccountName }}</span>
                            </li>
                        </ul>
                        @if($amount > $this->fromAccountBalance)
                            <div class="alert alert-warning mb-0 py-2">
                                <i data-lucide="triangle-alert"></i>
                                Transfer amount exceeds the available balance in the source account.
                            </div>
                        @endif
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Select both accounts and an amount to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
