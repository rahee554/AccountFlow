<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="container">

    </div>
    <div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Create Payment Method</h5>
                <a href="{{ route('accountflow::payment-methods') }}" class="btn btn-light btn-sm">Back</a>
            </div>

           <form wire:submit.prevent="save" class="card-body" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label small">Name</label>
        <input
            type="text"
            class="form-control form-control-sm @error('form.name') is-invalid @enderror"
            wire:model.defer="form.name"
            placeholder="Enter payment method name"
        />
        @error('form.name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label small">Info</label>
        <textarea
            class="form-control form-control-sm @error('form.info') is-invalid @enderror"
            wire:model.defer="form.info"
            rows="3"
            placeholder="Optional description or notes"
        ></textarea>
        @error('form.info') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label small">Logo / Icon</label>
        <input
            type="file"
            class="form-control form-control-sm @error('logoUpload') is-invalid @enderror"
            wire:model="logoUpload"
            accept="image/*"
        />
        @error('logoUpload') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        @if ($logoUpload)
            <img src="{{ $logoUpload->temporaryUrl() }}" class="img-thumbnail mt-2" width="120">
        @endif
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label small">Account</label>
            <select
                class="form-select form-select-sm @error('form.account_id') is-invalid @enderror"
                wire:model.defer="form.account_id"
            >
                <option value="">Select account</option>
                @foreach($accounts ?? [] as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            @error('form.account_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label small">Status</label>
            <select
                class="form-select form-select-sm @error('form.status') is-invalid @enderror"
                wire:model.defer="form.status"
            >
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            @error('form.status') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
            <span wire:loading.remove>Save</span>
            <span wire:loading>Saving...</span>
        </button>
        <a href="{{ route('accountflow::payment-methods') }}" class="btn btn-secondary btn-sm">Cancel</a>
    </div>
</form>

        </div>
    </div>
</div>
</div>
