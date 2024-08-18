<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Reports;

use ArtflowStudio\AccountFlow\App\Models\Account;
use ArtflowStudio\AccountFlow\App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Cashbook extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $accountId = '';

    public $period = 'month'; // today, month, year, custom

    public $dateFrom;

    public $dateTo;

    public $perPage = 25;

    public $accounts = [];

    public function mount()
    {
        $this->accounts = Account::orderBy('name')->get();

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
        if (in_array($field, ['accountId', 'dateFrom', 'dateTo', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

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

        // totals (type codes: 1 = income/credit, 2 = expense/debit)
        $totalDebit = (clone $query)->where('type', 2)->sum('amount');
        $totalCredit = (clone $query)->where('type', 1)->sum('amount');

        $transactions = (clone $query)->with(['account', 'category', 'paymentMethod'])->orderBy('date', 'desc')->paginate($this->perPage);

        return view($viewpath.'livewire.reports.cashbook', [
            'transactions' => $transactions,
            'accounts' => $this->accounts,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ])->extends($layout)->section('content');
    }
}

