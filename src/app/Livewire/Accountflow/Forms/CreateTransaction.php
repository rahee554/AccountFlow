<?php

namespace App\Livewire\Accountflow\Forms;

use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\PaymentMethod;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;


class CreateTransaction extends Component
{
    public $amount, $payment_method, $account_id, $category_id, $date, $description;
    public $payment_methods, $accounts, $income_categories, $expense_categories;
    public $type = 1; // 1 = Income, 2 = Expense


    public function mount()
    {
        $this->payment_methods = PaymentMethod::all();
        $this->accounts = Account::where('status', 1)->get();
        $this->income_categories = Category::whereNotNull('parent_id')->where('flow_type', 1)->get();
        $this->expense_categories = Category::whereNotNull('parent_id')->where('flow_type', 2)->get();

        $this->date = now()->format('Y-m-d');
    }

    public function changeType($value)
    {
        $this->type = $value;
        $this->category_id = null;
    }

    public function getCategoriesProperty()
    {
        return $this->type == 1 ? $this->income_categories : $this->expense_categories;
    }



    public function storeTransaction()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|exists:ac_payment_methods,id',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'required|exists:ac_categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        Transaction::create([
            'unique_id' => generateUniqueID(Transaction::class, 'unique_id'),
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'date' => $this->date,
            'description' => $this->description,
            'type' => $this->type,
            'added_by' => Auth::id(),
        ]);


        return $this->redirectRoute('accountflow::transactions', navigate: true);








    }


    public function render()
    {
        return view('livewire.accountflow.forms.create-transaction', [
            'categories' => $this->getCategoriesProperty(),

        ])->extends(('accountflow::layout.app'));
    }
}
