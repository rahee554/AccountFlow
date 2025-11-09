<?php

namespace App\Livewire\Accountflow\Reports;

use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfitLoss extends Component
{
    public $startDate;

    public $endDate;

    public $dateRange;

    public $reportData;

    public $companyInfo;

    public $isLoading = false;

    public $reportId;

    protected $listeners = ['print-report' => 'printReport'];

    public function mount()
    {
        $this->reportId = uniqid('pl_report_');

        // Set default date range to the current month
        $this->initializeDateRange();

        // Load company information
        $this->loadCompanyInfo();

        // Load initial report data
        $this->loadReportData();
    }

    private function initializeDateRange()
    {
        $this->dateRange = Carbon::now()->startOfMonth()->format('m/d/Y').' - '.Carbon::now()->endOfMonth()->format('m/d/Y');
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();
    }

    private function loadCompanyInfo()
    {
        $this->companyInfo = [
            'name' => config('app.name', 'Your Company Name'),
            'address' => config('accountflow.company_address', '123 Business Street'),
            'city' => config('accountflow.company_city', 'City, State 12345'),
            'phone' => config('accountflow.company_phone', '(555) 123-4567'),
            'email' => config('accountflow.company_email', 'info@company.com'),
        ];
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'dateRange') {
            $this->updateDateRange();
        }
    }

    private function updateDateRange()
    {
        try {
            if (empty($this->dateRange) || ! str_contains($this->dateRange, ' - ')) {
                $this->addError('dateRange', 'Please select a valid date range.');

                return;
            }

            [$start, $end] = explode(' - ', $this->dateRange);
            $startDate = Carbon::createFromFormat('m/d/Y', trim($start));
            $endDate = Carbon::createFromFormat('m/d/Y', trim($end));

            // Validate date range
            if ($startDate->gt($endDate)) {
                $this->addError('dateRange', 'Start date cannot be after end date.');

                return;
            }

            if ($startDate->gt(Carbon::now())) {
                $this->addError('dateRange', 'Start date cannot be in the future.');

                return;
            }

            $this->startDate = $startDate->toDateString();
            $this->endDate = $endDate->toDateString();

            $this->loadReportData();

        } catch (\Exception $e) {
            $this->addError('dateRange', 'Invalid date format. Please select a valid date range.');
            \Log::error('Date range parsing error: '.$e->getMessage());
        }
    }

    public function loadReportData()
    {
        $this->isLoading = true;

        try {
            // Create cache key for this specific report
            $cacheKey = "profit_loss_report_{$this->startDate}_{$this->endDate}_".auth()->id();

            // Check if we have cached data (cache for 5 minutes)
            $this->reportData = Cache::remember($cacheKey, 300, function () {
                return $this->generateReportData();
            });

            $this->dispatch('reportDataUpdated');

        } catch (\Exception $e) {
            $this->addError('reportData', 'Error loading report data: '.$e->getMessage());
            \Log::error('Profit Loss Report Error: '.$e->getMessage());

            // Initialize empty report data
            $this->reportData = $this->getEmptyReportData();
        } finally {
            $this->isLoading = false;
        }
    }

    private function generateReportData()
    {
        // Get transaction totals by category with proper income/expense handling
        $categoryTotals = DB::table('ac_transactions')
            ->select(
                'category_id',
                DB::raw('SUM(CASE 
                    WHEN type = "income" THEN amount 
                    WHEN type = "expense" THEN -amount 
                    ELSE amount 
                END) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->whereNull('deleted_at') // Exclude soft-deleted transactions
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Get all categories with their totals
        $categories = Category::with('parent')
            ->get()
            ->map(function ($category) use ($categoryTotals) {
                $categoryData = $categoryTotals->get($category->id);
                $category->total_amount = $categoryData ? (float) $categoryData->total_amount : 0;
                $category->transaction_count = $categoryData ? $categoryData->transaction_count : 0;

                return $category;
            });

        // Calculate parent category totals (including children)
        $parentCategories = $categories->whereNull('parent_id');
        foreach ($parentCategories as $parent) {
            $childrenTotal = $categories->where('parent_id', $parent->id)->sum('total_amount');
            $parent->total_amount += $childrenTotal;

            $childrenTransactionCount = $categories->where('parent_id', $parent->id)->sum('transaction_count');
            $parent->transaction_count += $childrenTransactionCount;
        }

        // Separate revenue and expenses
        $revenue = $parentCategories->where('total_amount', '>', 0)->sortByDesc('total_amount');
        $expenses = $parentCategories->where('total_amount', '<', 0)->sortBy('total_amount');

        $totalRevenue = $revenue->sum('total_amount');
        $totalExpenses = abs($expenses->sum('total_amount'));
        $netIncome = $totalRevenue - $totalExpenses;

        // Calculate percentages
        $revenueWithPercentages = $revenue->map(function ($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0 ? ($item->total_amount / $totalRevenue) * 100 : 0;

            return $item;
        });

        $expensesWithPercentages = $expenses->map(function ($item) use ($totalExpenses) {
            $item->percentage = $totalExpenses > 0 ? (abs($item->total_amount) / $totalExpenses) * 100 : 0;

            return $item;
        });

        return [
            'revenue' => $revenueWithPercentages,
            'expenses' => $expensesWithPercentages,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'categories' => $categories,
            'revenue_count' => $revenue->sum('transaction_count'),
            'expense_count' => $expenses->sum('transaction_count'),
            'profit_margin' => $totalRevenue > 0 ? ($netIncome / $totalRevenue) * 100 : 0,
            'generated_at' => now(),
        ];
    }

    private function getEmptyReportData()
    {
        return [
            'revenue' => collect(),
            'expenses' => collect(),
            'total_revenue' => 0,
            'total_expenses' => 0,
            'net_income' => 0,
            'categories' => collect(),
            'revenue_count' => 0,
            'expense_count' => 0,
            'profit_margin' => 0,
            'generated_at' => now(),
        ];
    }

    public function printReport()
    {
        // Log the print action
        \Log::info('Profit Loss Report printed', [
            'user_id' => auth()->id(),
            'date_range' => $this->dateRange,
            'net_income' => $this->reportData['net_income'] ?? 0,
        ]);

        $this->dispatch('print-report');
    }

    public function exportToPdf()
    {
        // This method can be implemented later for PDF export
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'PDF export feature coming soon!',
        ]);
    }

    public function exportToExcel()
    {
        // This method can be implemented later for Excel export
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Excel export feature coming soon!',
        ]);
    }

    // Quick date range methods
    public function setCurrentMonth()
    {
        $this->dateRange = Carbon::now()->startOfMonth()->format('m/d/Y').' - '.Carbon::now()->endOfMonth()->format('m/d/Y');
        $this->updateDateRange();
    }

    public function setLastMonth()
    {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();
        $this->dateRange = $start->format('m/d/Y').' - '.$end->format('m/d/Y');
        $this->updateDateRange();
    }

    public function setCurrentYear()
    {
        $this->dateRange = Carbon::now()->startOfYear()->format('m/d/Y').' - '.Carbon::now()->endOfYear()->format('m/d/Y');
        $this->updateDateRange();
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path', 'accountflow.');
        $layout = config('accountflow.print_layout', 'layouts.app');

        return view($viewpath.'livewire.reports.profit-loss', [
            'reportData' => $this->reportData,
            'companyInfo' => $this->companyInfo,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'isLoading' => $this->isLoading,
        ])->extends($layout)->section('content');
    }
}
