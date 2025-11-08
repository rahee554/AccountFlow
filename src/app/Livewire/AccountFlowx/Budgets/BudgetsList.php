<?php

namespace App\Livewire\Accountflow\Budgets;

use App\Models\AccountFlow\Budget;
use Illuminate\View\View;
use Livewire\Component;

class BudgetsList extends Component
{
    public $budgets;

    public function mount(): void
    {
        $this->budgets = Budget::with(['account', 'category', 'creator'])->get();
    }

    public function render(): View
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.budgets.budgets-list', ['budgets' => $this->budgets])->extends($layout);
    }
}

