<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Reports;

use ArtflowStudio\AccountFlow\App\Models\Account;
use ArtflowStudio\AccountFlow\App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TrialBalance extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $period = 'month'; // today, month, year, custom

    public $dateFrom;

    public $dateTo;

    public $perPage = 25;

    public $search = '';

    public function mount()
    {
        $now = Carbon::now();
        $this->setPeriodDates($this->period, $now);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function applyQuickRange($type)
    {
        $this->period = $type;
        $this->setPeriodDates($type, Carbon::now());
        $this->resetPage();
    }

    protected function setPeriodDates($type, Carbon $now)
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
        if (in_array($field, ['dateFrom', 'dateTo', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        // TODO: implement CSV export of current view
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        // Accounts query
        $accountsQuery = Account::query()->orderBy('name');
        if ($this->search) {
            $accountsQuery->where('name', 'like', '%'.$this->search.'%');
        }

        $accounts = $accountsQuery->paginate($this->perPage);

        $accountIds = $accounts->pluck('id')->toArray();

        // Aggregate transactions for accounts on this page within date range
        $txAgg = [];
        if (count($accountIds)) {
            $txSums = Transaction::query()
                ->whereIn('account_id', $accountIds)
                ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
                ->selectRaw('account_id, SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as total_debit, SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as total_credit')
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id');

            foreach ($accountIds as $id) {
                $row = $txSums->has($id) ? $txSums->get($id) : null;
                $txAgg[$id] = [
                    'debit' => $row ? (float) $row->total_debit : 0.0,
                    'credit' => $row ? (float) $row->total_credit : 0.0,
                    'net' => $row ? ((float) $row->total_credit - (float) $row->total_debit) : 0.0,
                ];
            }
        }

        // Totals for displayed page
        $totalDebit = array_sum(array_map(fn ($v) => $v['debit'], $txAgg));
        $totalCredit = array_sum(array_map(fn ($v) => $v['credit'], $txAgg));
        $totalNet = $totalCredit - $totalDebit;

        // Additionally aggregate by category for each account on the current page
        $categoryAgg = [];
        if (count($accountIds)) {
            $txs = Transaction::query()
                ->whereIn('account_id', $accountIds)
                ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
                ->with('category')
                ->get();

            foreach ($txs as $t) {
                $aid = $t->account_id;
                $cid = $t->category_id;
                $cname = $t->category?->name ?? ('#'.$cid);

                if (! isset($categoryAgg[$aid])) {
                    $categoryAgg[$aid] = [];
                }

                if (! isset($categoryAgg[$aid][$cid])) {
                    $categoryAgg[$aid][$cid] = [
                        'category_name' => $cname,
                        'debit' => 0.0,
                        'credit' => 0.0,
                    ];
                }

                if ($t->type == 2) {
                    $categoryAgg[$aid][$cid]['debit'] += (float) $t->amount;
                } else {
                    $categoryAgg[$aid][$cid]['credit'] += (float) $t->amount;
                }
            }
        }

        return view($viewpath.'livewire.reports.trial-balance', [
            'accounts' => $accounts,
            'txAgg' => $txAgg,
            'categoryAgg' => $categoryAgg,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'totalNet' => $totalNet,
        ])->extends($layout)->section('content');
    }
}

