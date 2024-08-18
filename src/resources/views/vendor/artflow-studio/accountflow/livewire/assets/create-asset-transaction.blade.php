<div>
        @include(config('accountflow.view_path') . '.blades.dashboard-header')

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-3">
            <label for="account_id" class="form-label">Account</label>
            <select wire:model="account_id" id="account_id" class="form-select">
                <option value="">Select Account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            @error('account_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="asset_id" class="form-label">Asset</label>
            <select wire:model="asset_id" id="asset_id" class="form-select">
                <option value="">Select Asset</option>
                @foreach ($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                @endforeach
            </select>
            @error('asset_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" wire:model="date" id="date" class="form-control">
            @error('date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" wire:model="amount" id="amount" class="form-control" step="0.01">
            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea wire:model="description" id="description" class="form-control"></textarea>
            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            {{ $transaction_id ? 'Update Transaction' : 'Create Transaction' }}
        </button>
    </form>
</div>
