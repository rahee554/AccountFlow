{{-- filepath: d:\Repositories\Al-Emaan_Travels\resources\views\vendor\artflow-studio\accountflow\livewire\budgets\create-budget.blade.php --}}
@php
    // fallback option lists — will use pluck only if models exist
    $accountOptions = [];
    $categoryOptions = [];
    try {
        $accountOptions = \App\Models\AccountFlow\Account::pluck('name','id')->toArray();
    } catch (\Throwable $e) { /* ignore if model missing */ }

    try {
        $categoryOptions = \App\Models\AccountFlow\Category::pluck('name','id')->toArray();
    } catch (\Throwable $e) { /* ignore if model missing */ }
@endphp

<div class="">
    @include(config('accountflow.view_path') . '.blades.dashboard-header')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create Budget</h5>
                    <a href="{{ route('accountflow::budgets') ?? '#' }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="save" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Account <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" wire:model.defer="account_id">
                                    <option value="">-- select account --</option>
                                    @foreach($accountOptions as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" wire:model.defer="category_id">
                                    <option value="">-- select category --</option>
                                    @foreach($categoryOptions as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Amount (USD) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control form-control-sm" wire:model.defer="amount" />
                                @error('amount') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Period</label>
                                <select class="form-select form-select-sm" wire:model.defer="period">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="year" />
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Month</label>
                                <input type="number" min="1" max="12" class="form-control form-control-sm" wire:model.defer="month" />
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control form-control-sm" rows="3" wire:model.defer="description"></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('accountflow::budgets') ?? '#' }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm">Save Budget</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>