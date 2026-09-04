<?php

namespace ArtflowStudio\AccountFlow\Livewire\Reports;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Cashbook extends Component
{
    use AuthorizesAccountFlow;
    use WithPagination;

    public string $accountId = '';

    public string $period = 'month'; // today, month, year, custom

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 25;

    /** @var array<int, Account> */
    public array $accounts = [];

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewReports);
        $this->accounts = Account::orderBy('name')->get()->toArray();

        $now = Carbon::now();
        $this->setPeriodDates($this->period, $now);
    }

    public function updatingAccountId()
    {
        $this->resetPage();
    }

    public function applyQuickRange($type)
    {
        $this->period = $type;
        $this->setPeriodDates($type, Carbon::now());
        $this->resetPage();
    }

    public function setPeriodDates($type, Carbon $now)
    {
        switch ($type) {
            case 'today':
                $this->dateFrom = $now->copy()->startOfDay()->toDateString();
                $this->dateTo = $now->copy()->endOfDay()->toDateString();
                break;
            case 'year':
                $this->dateFrom = $now->copy()->startOfYear()->toDateString();
                $this->dateTo = $now->copy()->endOfYear()->toDateString();
                break;
            case 'month':
            default:
                $this->dateFrom = $now->copy()->startOfMonth()->toDateString();
                $this->dateTo = $now->copy()->endOfMonth()->toDateString();
                break;
        }
    }

    public function updated($field)
    {
        // When user edits filters, reset pagination
        if (in_array($field, ['accountId', 'dateFrom', 'dateTo', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.reports.cashbook';
        $layout = config('accountflow.layout');
        $title = 'Cashbook | '.config('accountflow.business_name');

        // Base query
        $query = Transaction::query();

        if ($this->accountId) {
            $query->where('account_id', $this->accountId);
        }

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        // totals — use whereIn to handle both integer and string type values
        $totalDebit = (clone $query)->whereIn('type', ['expense', '2', 2])->sum('amount');
        $totalCredit = (clone $query)->whereIn('type', ['income', '1', 1])->sum('amount');

        $transactions = (clone $query)->with(['account', 'category', 'paymentMethod'])->orderBy('date', 'desc')->paginate($this->perPage);

        return view($viewpath, [
            'transactions' => $transactions,
            'accounts' => $this->accounts,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ])->extends($layout)->section('content')->title($title);
    }
}
