<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="card card-flush">
                    <div class="card-header pt-5 pb-3">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark fs-3"><i class="fas fa-users me-2 text-primary"></i>Loan Partners</span>
                            <span class="text-muted fw-semibold fs-7">Lenders and borrowers directory ({{ $totalCount }} total)</span>
                        </h3>
                        <div class="card-toolbar gap-2">
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm w-200px" placeholder="Search partners...">
                            <a href="{{ route('accountflow::loans.partners.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Partner
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th class="min-w-200px">Name</th>
                                        <th class="min-w-150px">Contact</th>
                                        <th class="min-w-150px">CNIC / ID</th>
                                        <th class="min-w-150px">Company</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($partners as $partner)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <div class="symbol-label bg-light-primary fw-bold text-primary fs-7">
                                                            {{ strtoupper(substr($partner->name, 0, 2)) }}
                                                        </div>
                                                    </div>
                                                    <div class="fw-bold text-gray-900 fs-7">{{ $partner->name }}</div>
                                                </div>
                                            </td>
                                            <td class="text-gray-800 fs-7">{{ $partner->contact }}</td>
                                            <td class="text-muted fs-8">{{ $partner->cnic }}</td>
                                            <td class="text-muted fs-8">{{ $partner->company ?? '—' }}</td>
                                            <td class="text-end">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="{{ route('accountflow::loans.partners.edit', $partner->id) }}" class="btn btn-sm btn-light-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button wire:click="deletePartner({{ $partner->id }})" wire:confirm="Delete this partner?" class="btn btn-sm btn-light-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-10">
                                                <div class="text-muted">
                                                    <i class="fas fa-users fs-2x mb-3 d-block"></i>
                                                    No loan partners found. <a href="{{ route('accountflow::loans.partners.create') }}">Add the first one</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $partners->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
