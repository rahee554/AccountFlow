@php
    // fallback option lists — will use pluck only if models exist
    $accountOptions = [];
    $categoryOptions = [];
    try {
        $accountOptions = \ArtflowStudio\AccountFlow\Models\Account::pluck('name','id')->toArray();
    } catch (\Throwable $e) { /* ignore if model missing */ }

    try {
        $categoryOptions = \ArtflowStudio\AccountFlow\Models\Category::pluck('name','id')->toArray();
    } catch (\Throwable $e) { /* ignore if model missing */ }
@endphp

<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::budgets') }}" wire:navigate>Budgets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Create Budget</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::budgets') }}" class="btn btn-soft-secondary" wire:navigate>
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

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="save" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Account <span class="text-danger">*</span></label>
                                <select class="form-select @error('account_id') is-invalid @enderror" wire:model="account_id">
                                    <option value="">-- select account --</option>
                                    @foreach($accountOptions as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" wire:model="category_id">
                                    <option value="">-- select category --</option>
                                    @foreach($categoryOptions as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" wire:model="amount" placeholder="0.00">
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Period</label>
                                <select class="form-select @error('period') is-invalid @enderror" wire:model="period">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Year</label>
                                <input type="number" class="form-control @error('year') is-invalid @enderror" wire:model="year">
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Month</label>
                                <input type="number" min="1" max="12" class="form-control @error('month') is-invalid @enderror" wire:model="month">
                                @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" rows="3" wire:model="description"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::budgets') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Save Budget</span>
                                    <span wire:loading>Saving…</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Preview</h2></div>
                <div class="card-body">
                    @if($amount && $category_id)
                        @php $categoryName = $categoryOptions[$category_id] ?? 'Unknown'; @endphp
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box icon-box-primary"><i data-lucide="target"></i></span>
                            <div>
                                <div class="fs-5 fw-semibold">{{ config('accountflow.currency', 'PKR') }} {{ number_format($amount, 2) }}</div>
                                <div class="text-body-secondary text-size-sm">{{ $accountOptions[$account_id] ?? 'No account selected' }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-soft-primary">{{ Str::title($period) }}</span>
                            <span class="badge badge-soft-secondary">{{ $categoryName }}</span>
                            @if($month || $year)
                                <span class="badge badge-soft-secondary">
                                    @if($month){{ \Carbon\Carbon::create()->month((int) $month)->format('M') }}@endif
                                    {{ $year ? '/ ' . $year : '' }}
                                </span>
                            @endif
                        </div>
                        @if($description)
                            <p class="text-body-secondary text-size-sm mb-0">{{ Str::limit($description, 140) }}</p>
                        @endif
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Fill in the amount and category to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
