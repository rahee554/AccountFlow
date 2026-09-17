<?php

namespace ArtflowStudio\AccountFlow\Livewire\Equity;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\EquityPartner;
use ArtflowStudio\AccountFlow\Models\EquityTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class EquityTransactionsList extends Component
{
    use AuthorizesAccountFlow;
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $partnerFilter = '';

    public array $partners = [];

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewEquity);
        $this->partners = EquityPartner::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteTransaction(int $id): void
    {
        $this->authorizeAccountFlow(Ability::ManageEquity);

        abort_if(! auth()->check(), 403);

        EquityTransaction::findOrFail($id)->delete();
        session()->flash('success', 'Transaction deleted successfully.');
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.equity.equity-transactions-list';
        $layout = config('accountflow.layout');
        $title = 'Equity Transactions | '.config('accountflow.business_name');

        $currency = $this->resolveCurrency();
        $currencySymbol = $this->resolveCurrencySymbol($currency);

        $query = EquityTransaction::query()->with('partner');

        if ($this->partnerFilter) {
            $query->where('partner_id', $this->partnerFilter);
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->search) {
            $query->where('description', 'like', '%'.$this->search.'%');
        }

        $transactions = $query->latest()->paginate(20);
        $totalContributions = EquityTransaction::where('type', 1)->sum('amount');
        $totalWithdrawals = EquityTransaction::where('type', 2)->sum('amount');
        $canManage = $this->canAccountFlow(Ability::ManageEquity);

        $view = view($viewpath, compact('transactions', 'totalContributions', 'totalWithdrawals', 'currency', 'currencySymbol', 'canManage'));

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }

    protected function resolveCurrency(): string
    {
        try {
            $dbVal = \ArtflowStudio\AccountFlow\Models\Setting::where('key', 'currency')
                ->where('type', 2)
                ->value('value');

            return $dbVal ?: config('accountflow.currency', 'PKR');
        } catch (Throwable $e) {
            return config('accountflow.currency', 'PKR');
        }
    }

    protected function resolveCurrencySymbol(string $currency): string
    {
        $symbols = config('accountflow.currency_symbols', []);

        return $symbols[$currency] ?? ($currency.' ');
    }
}
