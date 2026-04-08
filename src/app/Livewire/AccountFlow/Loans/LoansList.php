<?php

namespace App\Livewire\AccountFlow\Loans;

use App\Models\AccountFlow\Loan;
use App\Models\AccountFlow\LoanUser;
use Livewire\Component;
use Livewire\WithPagination;

class LoansList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteLoan(int $id): void
    {
        abort_if(! auth()->check(), 403);

        Loan::findOrFail($id)->delete();
        session()->flash('success', 'Loan deleted successfully.');
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.loans.loans-list';
        $layout   = config('accountflow.layout');
        $title    = 'Loans | ' . config('accountflow.business_name');

        $currency = $this->resolveCurrency();

        $query = Loan::query()->with('loanPartner');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('unique_id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter !== '') {
            $query->where('loan_type', $this->typeFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $loans = $query->orderBy('date', 'desc')->paginate(15);

        $stats = [
            'total'     => Loan::count(),
            'lended'    => Loan::where('loan_type', 1)->sum('amount'),
            'borrowed'  => Loan::where('loan_type', 2)->sum('amount'),
            'active'    => Loan::where('status', 1)->count(),
        ];

        $view = view($viewpath, compact('loans', 'stats', 'currency'));

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
