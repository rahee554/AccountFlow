<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Trail</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Audit Trail</h1></div>
            <p class="page-header-subtitle">All create, update, and delete activity.</p>
        </div>
    </div>

    @php
        $actionColors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger'];
        $actionIcons  = ['created' => 'plus', 'updated' => 'pencil', 'deleted' => 'trash-2'];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Log Entries</div>
                        <span class="icon-box icon-box-primary"><i data-lucide="history"></i></span>
                    </div>
                    <div class="stat-value">{{ number_format($totalLogs) }}</div>
                </div>
            </div></div>
        </div>
        @foreach(['created', 'updated', 'deleted'] as $action)
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label text-capitalize">{{ $action }}</div>
                            <span class="icon-box icon-box-{{ $actionColors[$action] }}"><i data-lucide="{{ $actionIcons[$action] }}"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($actionCounts[$action] ?? 0) }}</div>
                    </div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Activity Log</h2>
            <div class="d-flex flex-wrap gap-2">
                <input wire:model.live.debounce.300ms="search"
                       type="text"
                       class="form-control form-control-sm"
                       style="width: 200px;"
                       placeholder="Search model or action...">
                <select wire:model.live="actionFilter" class="form-select form-select-sm" style="width: 150px;">
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>
                <select wire:model.live="modelFilter" class="form-select form-select-sm" style="width: 200px;">
                    <option value="">All Models</option>
                    @foreach($modelTypes as $model)
                        @php $shortModel = class_exists($model) ? class_basename($model) : basename(str_replace('\\', '/', $model)); @endphp
                        <option value="{{ $model }}">{{ $shortModel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Model</th>
                            <th class="text-center">Action</th>
                            <th>Changes</th>
                            <th>User</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $actionColor = $actionColors[$log->action] ?? 'primary';
                                $modelClass  = class_exists($log->model_type) ? class_basename($log->model_type) : basename(str_replace('\\', '/', $log->model_type));
                                $before = is_array($log->before) ? $log->before : (is_string($log->before) ? json_decode($log->before, true) ?? [] : []);
                                $after  = is_array($log->after)  ? $log->after  : (is_string($log->after)  ? json_decode($log->after, true)  ?? [] : []);
                                $changedKeys = array_keys(array_diff_assoc($after ?? [], $before ?? []));
                            @endphp
                            <tr>
                                <td class="text-body-secondary text-size-sm cell-numeric">{{ $log->model_id }}</td>
                                <td>
                                    <span class="badge badge-soft-primary">{{ $modelClass }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-soft-{{ $actionColor }} text-capitalize">{{ $log->action }}</span>
                                </td>
                                <td>
                                    @if($log->action === 'created' && !empty($after))
                                        <span class="text-body-secondary text-size-sm">Created {{ count($after) }} field(s)</span>
                                    @elseif($log->action === 'updated' && !empty($changedKeys))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(array_slice($changedKeys, 0, 3) as $key)
                                                <span class="badge badge-soft-warning">{{ $key }}</span>
                                            @endforeach
                                            @if(count($changedKeys) > 3)
                                                <span class="badge badge-soft-secondary">+{{ count($changedKeys) - 3 }} more</span>
                                            @endif
                                        </div>
                                    @elseif($log->action === 'deleted')
                                        <span class="text-danger text-size-sm">Record removed</span>
                                    @else
                                        <span class="text-body-secondary text-size-sm">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center gap-2">
                                            <i data-lucide="user"></i>
                                            <span class="text-size-sm">{{ $log->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-body-secondary text-size-sm">System</span>
                                    @endif
                                </td>
                                <td class="text-body-secondary text-size-sm">
                                    {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-body-secondary">
                                        <i data-lucide="history"></i>
                                        <div class="mt-2">No audit log entries found.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $logs->links() }}</div>
    </div>
</div>
