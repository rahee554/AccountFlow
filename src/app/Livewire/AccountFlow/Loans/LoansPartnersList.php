<?php

namespace App\Livewire\AccountFlow\Loans;

use App\Models\AccountFlow\LoanUser;
use Livewire\Component;
use Livewire\WithPagination;

class LoansPartnersList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deletePartner(int $id): void
    {
        abort_if(! auth()->check(), 403);

        LoanUser::findOrFail($id)->delete();
        session()->flash('success', 'Loan partner deleted.');
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.loans.loans-partners-list';
        $layout   = config('accountflow.layout');
        $title    = 'Loan Partners | ' . config('accountflow.business_name');

        $query = LoanUser::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('company', 'like', '%' . $this->search . '%')
                  ->orWhere('cnic', 'like', '%' . $this->search . '%');
            });
        }

        $partners     = $query->orderBy('name')->paginate(15);
        $totalCount   = LoanUser::count();

        return view($viewpath, compact('partners', 'totalCount'))
            ->extends($layout)
            ->section('content')
            ->title($title);
    }
}
