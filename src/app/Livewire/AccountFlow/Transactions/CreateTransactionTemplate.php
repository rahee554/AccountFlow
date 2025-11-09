<?php

namespace App\Livewire\Accountflow\Transactions;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\PaymentMethod;
use App\Models\AccountFlow\TransactionTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTransactionTemplate extends Component
{
    public $name;

    public $amount;

    public $payment_method;

    public $account_id;

    public $category_id;

    public $type = 1; // 1 = Income, 2 = Expense

    public $description;

    public $payment_methods;

    public $accounts;

    public $income_categories;

    public $expense_categories;

    public function mount()
    {
        $this->payment_methods = PaymentMethod::all();
        $this->accounts = Account::where('active', true)->get();
        $this->income_categories = Category::whereNotNull('parent_id')->where('type', 1)->get();
        $this->expense_categories = Category::whereNotNull('parent_id')->where('type', 2)->get();
    }

    public function getCategoriesProperty()
    {
        return $this->type == 1 ? $this->income_categories : $this->expense_categories;
    }

    public function storeTemplate()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|exists:ac_payment_methods,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:ac_categories,id',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            TransactionTemplate::create([
                'name' => $this->name,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'account_id' => $this->account_id,
                'category_id' => $this->category_id,
                'description' => $this->description,
                'type' => $this->type,
                'created_by' => Auth::id(),
            ]);

            session()->flash('success', 'Transaction Template created successfully!');

            return $this->redirectRoute('accountflow::transactions.templates', navigate: true);

        } catch (\Exception $e) {
            session()->flash('error', 'Error creating template: '.$e->getMessage());
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.transactions.create-transaction-template', [
            'categories' => $this->getCategoriesProperty(),
        ])->extends($layout)->section('content');
    }
}
