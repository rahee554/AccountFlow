<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="container mt-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Payment Methods</h4>
            <a href="{{ route('accountflow::payment-methods.create') }}" class="btn btn-primary btn-sm">Create Payment Method</a>
        </div>

        <div class="row g-3">
            @forelse($paymentMethods as $method)
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex gap-3">
                            <div class="flex-shrink-0">
                                @if($method->logo_icon)
                                    <img src="{{ $method->logo_icon }}" alt="{{ $method->name }}" class="rounded" style="width:56px;height:56px;object-fit:cover">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:56px;height:56px">
                                        <span class="text-muted small">PM</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $method->name }}</h6>
                                        @if($method->account)
                                            <div class="small text-muted">Account: {{ $method->account->name }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        @if($method->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </div>
                                </div>

                                @if($method->info)
                                    <p class="small text-muted mb-0">{{ Str::limit($method->info, 140) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted">No payment methods found.</div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>