<?php

namespace App\Livewire\AccountFlow\Reports;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\EquityPartner;
use App\Models\AccountFlow\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BalanceSheet extends Component
{
    public string $asOfDate = '';

    /** @var array<string, mixed> */
    public array $reportData = [];

    /** @var array<string, string> */
    public array $companyInfo = [];

    public string $currency = '';

    public string $currencySymbol = '';

    public function mount(): void
    {
        $this->asOfDate = Carbon::today()->toDateString();
        $this->currency = $this->resolveCurrency();
        $this->currencySymbol = $this->resolveCurrencySymbol($this->currency);
        $this->companyInfo = [
            'name'    => config('app.name', 'Company Name'),
            'address' => config('accountflow.company_address', ''),
            'city'    => config('accountflow.company_city', ''),
        ];
        $this->loadReportData();
    }

    public function updatedAsOfDate(): void
    {
        $this->loadReportData();
    }

    public function loadReportData(): void
    {
        // ── Assets: account balances as of date ──────────────────────────────
        $accounts = Account::orderBy('name')->get(['id', 'name', 'balance']);
        $totalAssets = (float) $accounts->sum('balance');

        // ── Liabilities: loan amounts ─────────────────────────────────────────
        $loans = Loan::orderBy('name')
            ->get(['id', 'name', 'amount', 'loan_type']);
        $totalLiabilities = (float) $loans->sum('amount');

        // ── Equity: partner equity + retained earnings ────────────────────────
        $equityPartners = EquityPartner::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'current_equity', 'ownership_percentage']);
        $totalPartnerEquity = (float) $equityPartners->sum('current_equity');

        // Retained earnings = cumulative net income (income - expenses) up to asOfDate
        $retainedEarningsRow = DB::table('ac_transactions')
            ->when($this->asOfDate, fn ($q) => $q->whereDate('date', '<=', $this->asOfDate))
            ->selectRaw("
                SUM(CASE WHEN type IN ('income','1',1) THEN amount ELSE 0 END) -
                SUM(CASE WHEN type IN ('expense','2',2) THEN amount ELSE 0 END) as net
            ")
            ->first();

        $retainedEarnings = (float) ($retainedEarningsRow->net ?? 0);
        $totalEquity      = $totalPartnerEquity + $retainedEarnings;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01;

        $this->reportData = [
            'as_of_date'                    => $this->asOfDate,
            'accounts'                      => $accounts->toArray(),
            'total_assets'                  => $totalAssets,
            'loans'                         => $loans->toArray(),
            'total_liabilities'             => $totalLiabilities,            'equity_partners'               => $equityPartners->toArray(),
            'total_partner_equity'          => $totalPartnerEquity,
            'retained_earnings'             => $retainedEarnings,
            'total_equity'                  => $totalEquity,
            'total_liabilities_and_equity'  => $totalLiabilitiesAndEquity,
            'is_balanced'                   => $isBalanced,
            'generated_at'                  => Carbon::now()->toDateTimeString(),
        ];
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.reports.balance-sheet';
        $layout   = config('accountflow.layout');
        $title    = 'Balance Sheet | ' . config('accountflow.business_name', config('app.name'));

        return view($viewpath, [
            'reportData'    => $this->reportData,
            'companyInfo'   => $this->companyInfo,
            'currency'      => $this->currency,
            'currencySymbol' => $this->currencySymbol,
            'asOfDate'      => $this->asOfDate,
        ])
            ->extends($layout)
            ->section('content')
            ->title($title);
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

    protected function resolveCurrencySymbol(string $currency): string
    {
        $symbols = config('accountflow.currency_symbols', []);

        return $symbols[$currency] ?? ($currency . ' ');
    }
}
