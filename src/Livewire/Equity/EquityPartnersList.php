<?php

namespace ArtflowStudio\AccountFlow\Livewire\Equity;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\EquityPartner;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class EquityPartnersList extends Component
{
    use AuthorizesAccountFlow;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewEquity);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deletePartner(int $id): void
    {
        $this->authorizeAccountFlow(Ability::ManageEquity);

        abort_if(! auth()->check(), 403);

        EquityPartner::findOrFail($id)->delete();
        session()->flash('success', 'Equity partner deleted successfully.');
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.equity.equity-partners-list';
        $layout = config('accountflow.layout');
        $title = 'Equity Partners | '.config('accountflow.business_name');

        $query = EquityPartner::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('company', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', (bool) $this->statusFilter);
        }

        $partners = $query->orderBy('name')->paginate(15);
        $totalEquity = EquityPartner::sum('current_equity');
        $activeCount = EquityPartner::where('is_active', true)->count();
        $totalCount = EquityPartner::count();
        $currency = $this->resolveCurrency();
        $currencySymbol = $this->resolveCurrencySymbol($currency);

        $view = view($viewpath, compact('partners', 'totalEquity', 'activeCount', 'totalCount', 'currency', 'currencySymbol'));

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
