<div>
    @include(config('accountflow.view_path') . 'blades.dashboard-header')

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                {{-- KPI Cards --}}
                <div class="row g-5 g-xl-8 mb-5">
                    @php
                        $actionColors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger'];
                        $actionIcons  = ['created' => 'fa-plus-circle', 'updated' => 'fa-edit', 'deleted' => 'fa-trash'];
                    @endphp

                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-list text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Log Entries</span>
                                <span class="text-white fs-2hx fw-bolder">{{ number_format($totalLogs) }}</span>
                            </div>
                        </div>
                    </div>
                    @foreach(['created', 'updated', 'deleted'] as $action)
                        <div class="col-xl-3">
                            <div class="card card-flush h-100">
                                <div class="card-body p-6 d-flex align-items-center gap-4">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label bg-light-{{ $actionColors[$action] ?? 'primary' }}">
                                            <i class="fas {{ $actionIcons[$action] ?? 'fa-circle' }} text-{{ $actionColors[$action] ?? 'primary' }} fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-8 fw-semibold text-capitalize">{{ $action }}</div>
                                        <div class="fw-bolder fs-2 text-gray-900">{{ number_format($actionCounts[$action] ?? 0) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Filters & Table --}}
                <div class="card card-flush">
                    <div class="card-header pt-5 pb-3">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark fs-3">
                                <i class="fas fa-history me-2 text-primary"></i>Audit Trail
                            </span>
                            <span class="text-muted fw-semibold fs-7">All create, update, and delete activity</span>
                        </h3>
                        <div class="card-toolbar gap-2">
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   class="form-control form-control-sm w-200px"
                                   placeholder="Search model or action...">
                            <select wire:model.live="actionFilter" class="form-select form-select-sm w-150px">
                                <option value="">All Actions</option>
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                            </select>
                            <select wire:model.live="modelFilter" class="form-select form-select-sm w-200px">
                                <option value="">All Models</option>
                                @foreach($modelTypes as $model)
                                    @php $shortModel = class_exists($model) ? class_basename($model) : basename(str_replace('\\', '/', $model)); @endphp
                                    <option value="{{ $model }}">{{ $shortModel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th class="min-w-80px">#</th>
                                        <th class="min-w-150px">Model</th>
                                        <th class="min-w-100px text-center">Action</th>
                                        <th class="min-w-200px">Changes</th>
                                        <th class="min-w-150px">User</th>
                                        <th class="min-w-150px">Timestamp</th>
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
                                            <td class="text-muted fs-8">{{ $log->model_id }}</td>
                                            <td>
                                                <span class="badge badge-light-primary fw-bold fs-8">{{ $modelClass }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light-{{ $actionColor }} fw-bold text-capitalize">{{ $log->action }}</span>
                                            </td>
                                            <td>
                                                @if($log->action === 'created' && !empty($after))
                                                    <span class="text-muted fs-8">Created {{ count($after) }} field(s)</span>
                                                @elseif($log->action === 'updated' && !empty($changedKeys))
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach(array_slice($changedKeys, 0, 3) as $key)
                                                            <span class="badge badge-light-warning fs-9">{{ $key }}</span>
                                                        @endforeach
                                                        @if(count($changedKeys) > 3)
                                                            <span class="badge badge-light fs-9">+{{ count($changedKeys) - 3 }} more</span>
                                                        @endif
                                                    </div>
                                                @elseif($log->action === 'deleted')
                                                    <span class="text-danger fs-8">Record removed</span>
                                                @else
                                                    <span class="text-muted fs-8">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->user)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="symbol symbol-30px">
                                                            <div class="symbol-label bg-light-primary fw-bold text-primary fs-8">
                                                                {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                                            </div>
                                                        </div>
                                                        <span class="text-gray-800 fs-8">{{ $log->user->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted fs-8">System</span>
                                                @endif
                                            </td>
                                            <td class="text-muted fs-8">
                                                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-10">
                                                <div class="text-muted">
                                                    <i class="fas fa-history fs-2x mb-3 d-block"></i>
                                                    No audit log entries found.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $logs->links() }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
