<div
    x-data="{ recurring: @entangle('recurring'), autoPost: @entangle('auto_post'), scheduleType: @entangle('schedule_type') }">
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <form wire:submit.prevent="save" class="card card-flush shadow-sm p-4" autocomplete="off">
        <h3 class="mb-4"><i class="fas fa-calendar-check me-2"></i>Create Planned Payment</h3>
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
                <input type="number" wire:model="amount" id="amount"
                    class="form-control @error('amount') is-invalid @enderror" min="0" step="0.01"
                    placeholder="Enter amount">
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
                        <div class="d-flex gap-2">
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
                    class="form-control @error('description') is-invalid @enderror" rows="2"
                    placeholder="Optional"></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <i class="fas fa-save me-2"></i>Save Planned Payment
            </button>
        </div>
        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
    </form>
</div>