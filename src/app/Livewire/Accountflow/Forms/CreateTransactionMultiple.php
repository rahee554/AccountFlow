<?php

namespace App\Livewire\Accountflow\Forms;

use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\PaymentMethod;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CreateTransactionMultiple extends Component
{
    public $expenses = [];
    public $income = [];

    public $payment_method;
    public $account;
    public $description;
    public $flow_type = 1; // Default to income form

    public function mount()
    {
        // Initialize both income and expense form data
        $this->expenses = collect([
            ['amount' => '', 'category_id' => '', 'date' => now()->format('Y-m-d')],
        ]);

        $this->income = collect([
            ['amount' => '', 'category_id' => '', 'date' => now()->format('Y-m-d')],
        ]);
    }

    public function addExpense()
    {
        $this->expenses->push([
            'amount' => '',
            'category_id' => '',
            'date' => now()->format('Y-m-d'),
        ]);
    }

    public function removeExpense($index)
    {
        $this->expenses->forget($index);
        $this->expenses = $this->expenses->values();
    }

    public function addIncome()
    {
        $this->income->push([
            'amount' => '',
            'category_id' => '',
            'date' => now()->format('Y-m-d'),
        ]);
    }

    public function removeIncome($index)
    {
        $this->income->forget($index);
        $this->income = $this->income->values();
    }

    public function save()
    {
        if ($this->flow_type == 1) { // Income
            $this->validate([
                'payment_method' => 'required|exists:transaction_payment_methods,id',
                'account' => 'required|exists:accounts,id',
                'income.*.amount' => 'required|numeric|min:1',
                'income.*.category_id' => 'required|exists:transaction_categories,id',
                'income.*.date' => 'required|date',
            ]);

            foreach ($this->income as $income) {
                Transaction::create([
                    'type' => 1, // income
                    'account_id' => $this->account,
                    'payment_method_id' => $this->payment_method,
                    'amount' => $income['amount'],
                    'category_id' => $income['category_id'],
                    'date' => $income['date'],
                    'description' => $this->description,
                    'user_id' => Auth::id(),
                ]);
            }

            session()->flash('success', 'Income saved successfully!');
            $this->income = []; // Reset income form
        }

        if ($this->flow_type == 2) { // Expense
            $this->validate([
                'payment_method' => 'required|exists:transaction_payment_methods,id',
                'account' => 'required|exists:accounts,id',
                'expenses.*.amount' => 'required|numeric|min:1',
                'expenses.*.category_id' => 'required|exists:transaction_categories,id',
                'expenses.*.date' => 'required|date',
            ]);

            foreach ($this->expenses as $expense) {
                Transaction::create([
                    'type' => 2, // expense
                    'account_id' => $this->account,
                    'payment_method_id' => $this->payment_method,
                    'amount' => $expense['amount'],
                    'category_id' => $expense['category_id'],
                    'date' => $expense['date'],
                    'description' => $this->description,
                    'user_id' => Auth::id(),
                ]);
            }

            session()->flash('success', 'Expenses saved successfully!');
            $this->expenses = []; // Reset expense form
        }

        return redirect()->route('transactions.index');
    }

    public function render()
    {
        return view('livewire.accountflow.forms.create-transaction-multiple', [
            'accounts' => Account::all(),
            'payment_methods' => PaymentMethod::all(),
            'income_categories' => Category::where('type', 1)->get(),
            'expense_categories' => Category::where('type', 2)->get(),
        ])->extends(('accountflow::layout.app'));
    }
}
