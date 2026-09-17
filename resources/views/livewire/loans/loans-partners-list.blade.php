<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Loan Partners</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Loan Partners</h1></div>
            <p class="page-header-subtitle">Lenders and borrowers directory ({{ $totalCount }} total).</p>
        </div>
        <div class="page-header-actions">
            @if ($canManage)
                <a href="{{ route('accountflow::loans.partners.create') }}" class="btn btn-primary" wire:navigate>
                    <i data-lucide="plus"></i> Add Partner
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Partners</h2>
            <div class="card-actions">
                <input wire:model.live.debounce.300ms="search" type="search" class="form-control form-control-sm" style="width:200px" placeholder="Search partners...">
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>CNIC / ID</th>
                            <th>Company</th>
                            <th class="cell-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td>
                                    <div class="cell-user">
                                        <span class="avatar avatar-sm avatar-primary">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                                        <div class="cell-user-body">
                                            <div class="cell-user-name">{{ $partner->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $partner->contact }}</td>
                                <td class="text-body-secondary">{{ $partner->cnic }}</td>
                                <td class="text-body-secondary">{{ $partner->company ?? '—' }}</td>
                                <td class="cell-actions">
                                    @if($canManage)
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('accountflow::loans.partners.edit', $partner->id) }}" class="btn btn-sm btn-ghost">
                                                <i data-lucide="pencil"></i>
                                            </a>
                                            <button wire:click="deletePartner({{ $partner->id }})" wire:confirm="Delete this partner?" class="btn btn-sm btn-ghost text-danger">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-body-secondary">
                                        <i data-lucide="users"></i>
                                        <div class="mt-2">
                                            No loan partners found.
                                            @if($canManage)
                                                <a href="{{ route('accountflow::loans.partners.create') }}" wire:navigate>Add the first one</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($partners->hasPages())
            <div class="card-footer justify-content-center">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</div>
