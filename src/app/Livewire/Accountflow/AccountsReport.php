<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow;

use ArtflowStudio\AccountFlow\App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AccountsReport extends Component
{
    public $categories;

    public $startDate;

    public $endDate;

    public $dateRange;

    public $totalAmount;

    public function mount()
    {
        // Set default date range to the current month
        $this->dateRange = Carbon::now()->startOfMonth()->format('m/d/Y').' - '.Carbon::now()->endOfMonth()->format('m/d/Y');
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();

        $this->loadData();
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
                return;
            }

            [$start, $end] = explode(' - ', $this->dateRange);

            $this->startDate = Carbon::createFromFormat('m/d/Y', trim($start))->toDateString();
            $this->endDate = Carbon::createFromFormat('m/d/Y', trim($end))->toDateString();

            $this->loadData();

        } catch (\Exception $e) {
            session()->flash('error', 'Invalid date format. Please select a valid date range.');
        }
    }

    private function loadData()
    {
        $this->categories = $this->getCategoriesWithTotalAmount();
        $this->totalAmount = $this->getTotalAmount();
    }

    private function getCategoriesWithTotalAmount()
    {
        // Aggregate income and expense separately to avoid sign confusion
        $categorySums = DB::table('ac_transactions')
            ->select('category_id',
                DB::raw('SUM(CASE WHEN type IN ("income","1",1) THEN amount ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN type IN ("expense","2",2) THEN amount ELSE 0 END) as total_expense')
            )
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Get all categories
        $categories = Category::all();

        $categories = $categories->map(function ($category) use ($categorySums) {
            $row = $categorySums->get($category->id);
            $income = $row ? (float) $row->total_income : 0.0;
            $expense = $row ? (float) $row->total_expense : 0.0;

            $category->total_income = $income;
            $category->total_expense = $expense;
            // net is income minus expense
            $category->net = $income - $expense;

            return $category;
        });

        // Calculate parent category totals by summing their children (income/expense/net)
        $parentCategories = $categories->whereNull('parent_id');
        foreach ($parentCategories as $parent) {
            $children = $categories->where('parent_id', $parent->id);
            $childrenIncome = $children->sum('total_income');
            $childrenExpense = $children->sum('total_expense');

            $parent->total_income = ($parent->total_income ?? 0) + $childrenIncome;
            $parent->total_expense = ($parent->total_expense ?? 0) + $childrenExpense;
            $parent->net = ($parent->net ?? 0) + ($childrenIncome - $childrenExpense);
        }

        return $categories;
    }

    private function getTotalAmount()
    {
        // compute total as income - expense, supporting both numeric and string type values
        $row = DB::table('ac_transactions')
            ->select(DB::raw('SUM(CASE WHEN type IN ("income","1",1) THEN amount ELSE 0 END) as total_income'), DB::raw('SUM(CASE WHEN type IN ("expense","2",2) THEN amount ELSE 0 END) as total_expense'))
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->first();

        $income = $row->total_income ?? 0;
        $expense = $row->total_expense ?? 0;

        return ($income - $expense) ?? 0;
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts-report';
        $layout = config('accountflow.layout');

        return view($viewpath, [
            'parentCategories' => $this->categories->whereNull('parent_id'),
            'categories' => $this->categories,
            'totalAmount' => $this->totalAmount,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ])->extends($layout)->section('content');
    }
}

