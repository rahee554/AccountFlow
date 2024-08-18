<div>
      @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <form wire:submit.prevent="save" class="card p-4 shadow-sm container mt-4">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" wire:model.defer="name" class="form-control form-control-sm @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" wire:model.defer="description" class="form-control form-control-sm @error('description') is-invalid @enderror"></textarea>
            @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="active" class="form-label">Status</label>
            <select id="active" wire:model.defer="active" class="form-select form-select-sm @error('active') is-invalid @enderror">
                <option value="">Select Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            @error('active') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="opening_balance" class="form-label">Opening Balance</label>
            <input type="number" id="opening_balance" wire:model.defer="opening_balance" step="0.01" class="form-control form-control-sm @error('opening_balance') is-invalid @enderror">
            @error('opening_balance') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-sm w-100">Create Account</button>
    </form>
</div>