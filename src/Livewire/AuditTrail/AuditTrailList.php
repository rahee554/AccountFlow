<?php

namespace ArtflowStudio\AccountFlow\Livewire\AuditTrail;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\AuditTrail;
use Livewire\Component;
use Livewire\WithPagination;

class AuditTrailList extends Component
{
    use AuthorizesAccountFlow;
    use WithPagination;

    public string $search = '';

    public string $actionFilter = '';

    public string $modelFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewAuditTrail);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.audit-trail.audit-trail-list';
        $layout = config('accountflow.layout');
        $title = 'Audit Trail | '.config('accountflow.business_name');

        $query = AuditTrail::query()->with('user');

        if ($this->actionFilter) {
            $query->where('action', $this->actionFilter);
        }

        if ($this->modelFilter) {
            $query->where('model_type', 'like', '%'.$this->modelFilter.'%');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('model_type', 'like', '%'.$this->search.'%')
                    ->orWhere('action', 'like', '%'.$this->search.'%');
            });
        }

        $logs = $query->latest()->paginate(20);
        $totalLogs = AuditTrail::count();
        $actionCounts = AuditTrail::selectRaw('action, COUNT(*) as count')->groupBy('action')->pluck('count', 'action')->toArray();
        $modelTypes = AuditTrail::selectRaw('DISTINCT model_type')->orderBy('model_type')->pluck('model_type')->toArray();

        return view($viewpath, compact('logs', 'totalLogs', 'actionCounts', 'modelTypes'))
            ->extends($layout)
            ->section('content')
            ->title($title);
    }
}
