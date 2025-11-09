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
        $viewpath = config('accountflow.view_path').'livewire.budgets.budgets-list';
        $layout = config('accountflow.layout');
        $title = 'Budgets List | '.config('accountflow.business_name');

        return view($viewpath, ['budgets' => $this->budgets])->extends($layout)->section('content')->title($title);
    }
}
