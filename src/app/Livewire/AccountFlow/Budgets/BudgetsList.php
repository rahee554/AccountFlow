<?php

namespace App\Livewire\AccountFlow\Budgets;

use App\Models\AccountFlow\Budget;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class BudgetsList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $periodFilter = '';

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteBudget(int $id): void
    {
        abort_if(! auth()->check(), 403);

        Budget::findOrFail($id)->delete();
        session()->flash('success', 'Budget deleted successfully.');
    }

    public function render(): View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.budgets.budgets-list';
        $layout   = config('accountflow.layout');
        $title    = 'Budgets | ' . config('accountflow.business_name');

        $currency = $this->resolveCurrency();

        $query = Budget::query()->with(['account', 'category', 'creator']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('category', fn ($q2) => $q2->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('account', fn ($q2) => $q2->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->periodFilter) {
            $query->where('period', $this->periodFilter);
        }

        $budgets = $query->latest()->paginate(15);

        $allBudgets = Budget::with(['account', 'category'])->get();

        $stats = [
            'total'       => $allBudgets->sum('amount'),
            'count'       => $allBudgets->count(),
            'monthly'     => $allBudgets->where('period', 'monthly')->sum('amount'),
            'yearly'      => $allBudgets->where('period', 'yearly')->sum('amount'),
        ];

        $view = view($viewpath, compact('budgets', 'stats', 'currency'));

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }

    protected function resolveCurrency(): string
    {
        try {
            $dbVal = \App\Models\AccountFlow\Setting::where('key', 'currency')
                ->where('type', 2)
                ->value('value');

            return $dbVal ?: config('accountflow.currency', 'PKR');
        } catch (\Throwable $e) {
            return config('accountflow.currency', 'PKR');
        }
    }
}

