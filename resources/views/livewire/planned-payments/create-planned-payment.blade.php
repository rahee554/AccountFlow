<div
    x-data="{ recurring: @entangle('recurring'), autoPost: @entangle('auto_post'), scheduleType: @entangle('schedule_type') }">
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::planned-payments') }}" wire:navigate>Planned Payments</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Add Planned Payment</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::planned-payments') }}" class="btn btn-soft-secondary" wire:navigate>
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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="account_id" class="form-label">Account</label>
                                <select wire:model="account_id" id="account_id"
                                    class="form-select @error('account_id') is-invalid @enderror">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Payment Name</label>
                                <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Payment name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select wire:model="category_id" id="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" wire:model="amount" id="amount"
                                        class="form-control @error('amount') is-invalid @enderror" min="0" step="0.01"
                                        placeholder="0.00">
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Recurring</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="recurring" x-model="recurring"
                                        id="recurring">
                                    <label class="form-check-label" for="recurring">Recurring Payment</label>
                                </div>
                                @error('recurring') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Auto Post</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="auto_post" id="auto_post">
                                    <label class="form-check-label" for="auto_post">Enable Auto Post</label>
                                </div>
                                @error('auto_post') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <template x-if="autoPost">
                                <div class="col-md-3">
                                    <label for="auto_post_date" class="form-label">Auto Post Date</label>
                                    <input type="date" wire:model="auto_post_date" id="auto_post_date"
                                        class="form-control @error('auto_post_date') is-invalid @enderror">
                                    @error('auto_post_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </template>
                            <template x-if="recurring">
                                <div class="col-md-6">
                                    <label for="period" class="form-label">Period</label>
                                    <select wire:model="period" id="period" class="form-select @error('period') is-invalid @enderror">
                                        <option value="">Select Period</option>
                                        <option value="1">Monthly</option>
                                        <option value="2">Quarterly</option>
                                        <option value="3">Half-Yearly</option>
                                        <option value="4">Annually</option>
                                    </select>
                                    @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="schedule_type" class="form-label">Schedule Type</label>
                                    <select wire:model="schedule_type" id="schedule_type" x-model="scheduleType"
                                        class="form-select @error('schedule_type') is-invalid @enderror">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                    @error('schedule_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <template x-if="scheduleType === 'weekly'">
                                    <div class="col-12">
                                        <label class="form-label">Days of Week</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @php $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; @endphp
                                            @foreach($days as $i => $d)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="weekly_days"
                                                        value="{{ $i }}" id="wd_{{ $i }}">
                                                    <label class="form-check-label" for="wd_{{ $i }}">{{ $d }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('weekly_days') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </template>
                                <template x-if="scheduleType === 'monthly'">
                                    <div class="col-md-3">
                                        <label for="monthly_day" class="form-label">Day of Month</label>
                                        <input type="number" wire:model="monthly_day" id="monthly_day" min="1" max="31"
                                            class="form-control @error('monthly_day') is-invalid @enderror">
                                        @error('monthly_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </template>
                            </template>
                            <template x-if="!recurring">
                                <div class="col-md-6">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" wire:model="due_date" id="due_date"
                                        class="form-control @error('due_date') is-invalid @enderror">
                                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </template>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea wire:model="description" id="description"
                                    class="form-control @error('description') is-invalid @enderror" rows="3"
                                    placeholder="Optional"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::planned-payments') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Save Planned Payment</span>
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
                    @if($amount && $name)
                        @php $categoryName = $categories->firstWhere('id', $category_id)->name ?? 'Unknown'; @endphp
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box {{ $recurring ? 'icon-box-warning' : 'icon-box-primary' }}">
                                <i data-lucide="{{ $recurring ? 'repeat' : 'calendar-clock' }}"></i>
                            </span>
                            <div>
                                <div class="fs-5 fw-semibold">{{ $name }}</div>
                                <div class="text-body-secondary text-size-sm">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-soft-secondary">{{ $categoryName }}</span>
                            @if($recurring)
                                <span class="badge badge-soft-warning">Recurring · {{ Str::title($schedule_type) }}</span>
                            @else
                                <span class="badge badge-soft-primary">One-time{{ $due_date ? ' · ' . \Carbon\Carbon::parse($due_date)->format('d M Y') : '' }}</span>
                            @endif
                            @if($auto_post)
                                <span class="badge badge-soft-success">Auto Post</span>
                            @endif
                        </div>
                        @if($description)
                            <p class="text-body-secondary text-size-sm mb-0">{{ Str::limit($description, 140) }}</p>
                        @endif
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Fill in the name and amount to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
