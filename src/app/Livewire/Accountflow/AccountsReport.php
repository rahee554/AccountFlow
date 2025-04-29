<?php

namespace App\Livewire\Accountflow;

use Livewire\Component;
use App\Models\AccountFlow\Category;
use Carbon\Carbon;

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
        $this->dateRange = Carbon::now()->startOfMonth()->format('m/d/Y') . ' - ' . Carbon::now()->endOfMonth()->format('m/d/Y');
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();

        // Fetch categories with sum of transactions for the default date range
        $this->categories = $this->getCategoriesWithTotalAmount();

        $this->totalAmount = $this->getTotalAmount();

    }

    // This method will be triggered whenever any property is updated
    public function updated($propertyName)
    {
        if ($propertyName === 'dateRange') {
            // Split the date range into start and end dates (user input)
            [$start, $end] = explode(' - ', $this->dateRange);

            // Convert input to 'Y-m-d' format using Carbon
            $this->startDate = Carbon::createFromFormat('m/d/Y', $start)->toDateString();
            $this->endDate = Carbon::createFromFormat('m/d/Y', $end)->toDateString();

            // Re-fetch categories with sum of transactions based on the new date range
            $this->categories = $this->getCategoriesWithTotalAmount();

            // Dispatch an event to refresh the component
            $this->dispatch('refreshComponent');

               // Recalculate the total sum of all transactions
               $this->totalAmount = $this->getTotalAmount();

        }
    }

    // Listener for the refreshComponent event
    protected $listeners = ['refreshComponent' => 'refreshData'];

    public function refreshData()
    {
        // Your logic to refresh or reload data when the dateRange is updated
        $this->categories = $this->getCategoriesWithTotalAmount();
    }

    private function getCategoriesWithTotalAmount()
    {
        // Eloquent query to get categories and their total transactions within the date range
        return Category::with(['transactions' => function ($query) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        }])
        ->get()
        ->map(function ($category) {
            // Calculate the total amount using Eloquent's sum function
            $category->total_amount = $category->transactions->sum('amount');
            return $category;
        });
    }

    private function getTotalAmount()
    {
        // Calculate the total sum of all transactions within the date range, without filtering by category
        return Category::whereHas('transactions', function ($query) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        })
        ->get()
        ->flatMap(function ($category) {
            return $category->transactions;
        })
        ->sum('amount');
    }
    
    public function render()
    {
        $viewpath = config('accountflow.view_path') . '.accounts-report';

        return view($viewpath, [
            'parentCategories' => $this->categories->whereNull('parent_id'),
            'categories' => $this->categories,
            'totalAmount' => $this->totalAmount, // Pass total amount to the view
        ])
        ->extends(config('accountflow.layout'))
        ->section('content');
    }
}
