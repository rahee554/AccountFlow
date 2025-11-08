<?php

namespace App\Livewire\Accountflow\Budgets;

use App\Models\AccountFlow\Budget;
use Livewire\Component;

class CreateBudget extends Component
{
    public $account_id;

    public $category_id;

    public $amount;

    public $period = 'monthly';

    public $year;

    public $month;

    public $description;

    public function save()
    {
        $this->validate([
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:ac_categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required',
        ]);
        Budget::create([
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'period' => $this->period,
            'year' => $this->year,
            'month' => $this->month,
            'description' => $this->description,
            'created_by' => auth()->id(),
        ]);
        session()->flash('success', 'Budget created successfully');

        return redirect()->route('accountflow::budgets');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.budgets.create-budget')->extends($layout);
    }
}

