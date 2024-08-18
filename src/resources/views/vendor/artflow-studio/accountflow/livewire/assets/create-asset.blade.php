<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <form wire:submit.prevent="storeAsset" class="card p-4">
        <div class="mb-3">
            <label for="name" class="form-label">Asset Name</label>
            <input type="text" id="name" class="form-control" wire:model.defer="name">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="value" class="form-label">Value</label>
            <input type="number" id="value" class="form-control" wire:model.defer="value">
            @error('value') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select id="category" class="form-select" wire:model.defer="category" required>
                <option value="">Select Category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" class="form-select" wire:model.defer="status">
                <option value="">Select Status</option>
                <option value="1">Active</option>
                <option value="2">Inactive</option>
            </select>
            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Acquisition Date</label>
            <input type="date" id="date" class="form-control" wire:model.defer="date">
            @error('date') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" class="form-control" wire:model.defer="description"></textarea>
            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <!-- ...existing code... -->
        <button type="submit" class="btn btn-primary">Create Asset</button>
    </form>
</div>