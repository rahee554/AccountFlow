<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::assets.transactions') }}" wire:navigate>Asset Transactions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $transaction_id ? 'Edit' : 'Add' }}</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>{{ $transaction_id ? 'Edit Asset Transaction' : 'Add Asset Transaction' }}</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::assets.transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to List
            </a>
        </div>
    </div>

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
                            <div class="col-12 col-sm-6">
                                <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                <select wire:model="account_id" id="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="asset_id" class="form-label">Asset <span class="text-danger">*</span></label>
                                <select wire:model="asset_id" id="asset_id" class="form-select @error('asset_id') is-invalid @enderror">
                                    <option value="">Select Asset</option>
                                    @foreach ($assets as $asset)
                                        <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                                    @endforeach
                                </select>
                                @error('asset_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" id="date" class="form-control @error('date') is-invalid @enderror">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" wire:model="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" placeholder="0.00">
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea wire:model="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::assets.transactions') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>{{ $transaction_id ? 'Update Transaction' : 'Create Transaction' }}</span>
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
                        @php $assetName = collect($assets)->firstWhere('id', (int) $asset_id)->name ?? 'Unknown asset'; @endphp
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box icon-box-primary"><i data-lucide="receipt"></i></span>
                            <div>
                                <div class="fs-5 fw-semibold">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($amount, 2) }}</div>
                                <div class="text-body-secondary text-size-sm">{{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '' }}</div>
                            </div>
                        </div>
                        <span class="badge badge-soft-secondary">{{ $assetName }}</span>
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Fill in the amount and asset to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
