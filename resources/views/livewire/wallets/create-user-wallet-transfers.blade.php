<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::users.wallets') }}" wire:navigate>User Wallets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Wallet Transfer</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Wallet Transfer</h1></div>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::users.wallets') }}" class="btn btn-soft-secondary" wire:navigate>
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
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Movement Type</label>
                                <div class="nav nav-segmented w-100">
                                    <button type="button" class="nav-link flex-fill @if($mode === 'top-up') active @endif" wire:click="$set('mode', 'top-up')">
                                        <i data-lucide="plus"></i> Top Up
                                    </button>
                                    <button type="button" class="nav-link flex-fill @if($mode === 'settle') active @endif" wire:click="$set('mode', 'settle')">
                                        <i data-lucide="minus"></i> Settle
                                    </button>
                                    <button type="button" class="nav-link flex-fill @if($mode === 'transfer') active @endif" wire:click="$set('mode', 'transfer')">
                                        <i data-lucide="repeat"></i> Transfer
                                    </button>
                                </div>
                                <div class="form-text">
                                    @if($mode === 'top-up')
                                        Hand business money to a person's wallet.
                                    @elseif($mode === 'settle')
                                        Record a person returning money to a business account.
                                    @else
                                        Move money directly from one person's wallet to another's.
                                    @endif
                                </div>
                            </div>

                            @if($mode === 'transfer')
                                <div class="col-12 col-sm-6">
                                    <label for="from_user" class="form-label">
                                        From <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="from_user" id="from_user"
                                        class="form-select @error('from_user') is-invalid @enderror">
                                        <option value="">Select Person</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_user')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="to_user" class="form-label">
                                        To <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="to_user" id="to_user"
                                        class="form-select @error('to_user') is-invalid @enderror">
                                        <option value="">Select Person</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_user')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <div class="col-12 col-sm-6">
                                    <label for="user_id" class="form-label">
                                        Team Member <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="user_id" id="user_id"
                                        class="form-select @error('user_id') is-invalid @enderror">
                                        <option value="">Select Person</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="account_id" class="form-label">Business Account</label>
                                    <select wire:model="account_id" id="account_id"
                                        class="form-select @error('account_id') is-invalid @enderror">
                                        <option value="">Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-12 col-sm-6">
                                <label for="amount" class="form-label">
                                    Amount <span class="text-danger">*</span>
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
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" wire:model="date" id="date"
                                    class="form-control @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($mode !== 'transfer')
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea wire:model="description" id="description"
                                        class="form-control @error('description') is-invalid @enderror"
                                        rows="3" placeholder="Notes about this movement"></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::users.wallets') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Save</span>
                                    <span wire:loading>Saving…</span>
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
                    @if($amount)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box icon-box-{{ $mode === 'settle' ? 'danger' : ($mode === 'transfer' ? 'primary' : 'success') }}">
                                <i data-lucide="{{ $mode === 'top-up' ? 'plus' : ($mode === 'settle' ? 'minus' : 'repeat') }}"></i>
                            </span>
                            <div>
                                <div class="fs-5 fw-semibold">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($amount, 2) }}</div>
                                <div class="text-body-secondary text-size-sm">{{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '' }}</div>
                            </div>
                        </div>

                        @if($mode === 'transfer')
                            @php
                                $fromName = $users->firstWhere('id', $from_user)->name ?? 'Unknown';
                                $toName = $users->firstWhere('id', $to_user)->name ?? 'Unknown';
                            @endphp
                            <ul class="list-divided mb-0">
                                <li class="list-row d-flex justify-content-between">
                                    <span class="text-body-secondary">From</span>
                                    <span class="fw-semibold">{{ $fromName }}</span>
                                </li>
                                <li class="list-row d-flex justify-content-between">
                                    <span class="text-body-secondary">To</span>
                                    <span class="fw-semibold">{{ $toName }}</span>
                                </li>
                            </ul>
                        @else
                            @php
                                $personName = $users->firstWhere('id', $user_id)->name ?? 'Unknown';
                                $accountName = $accounts->firstWhere('id', $account_id)->name ?? null;
                            @endphp
                            <ul class="list-divided mb-0">
                                <li class="list-row d-flex justify-content-between">
                                    <span class="text-body-secondary">Person</span>
                                    <span class="fw-semibold">{{ $personName }}</span>
                                </li>
                                @if($accountName)
                                    <li class="list-row d-flex justify-content-between">
                                        <span class="text-body-secondary">Account</span>
                                        <span class="fw-semibold">{{ $accountName }}</span>
                                    </li>
                                @endif
                                <li class="list-row d-flex justify-content-between">
                                    <span class="text-body-secondary">Direction</span>
                                    <span class="badge badge-soft-{{ $mode === 'settle' ? 'danger' : 'success' }}">
                                        {{ $mode === 'settle' ? 'Into business account' : 'Into wallet' }}
                                    </span>
                                </li>
                            </ul>
                        @endif
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Fill in the amount to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
