<?php

namespace App\Livewire\Accountflow\Transactions;

use App\Http\Controllers\AccountFlow\AccountsController;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\PaymentMethod;
use App\Models\AccountFlow\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateTransactionMultiple extends Component
{
    public $transactions = [];

    public $payment_method;

    public $account_id;

    public $description;

    public $type = 1; // 1 = Income, 2 = Expense

    protected $rules = [
        'payment_method' => 'required|exists:ac_payment_methods,id',
        'account_id' => 'required|exists:accounts,id',
        'transactions.*.amount' => 'required|numeric|min:0.01',
        'transactions.*.category_id' => 'required|exists:ac_categories,id',
        'transactions.*.date' => 'required|date',
        'transactions.*.description' => 'nullable|string|max:500',
        'description' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        // Initialize with one empty transaction
        $this->addTransaction();
    }

    public function addTransaction()
    {
        $this->transactions[] = [
            'amount' => '',
            'category_id' => '',
            'date' => now()->format('Y-m-d'),
            'description' => '',
        ];
    }

    public function removeTransaction($index)
    {
        if (count($this->transactions) > 1) {
            unset($this->transactions[$index]);
            $this->transactions = array_values($this->transactions); // Re-index array
        }
    }

    public function changeType($value)
    {
        $this->type = $value;
        // Reset category selections when type changes
        foreach ($this->transactions as $key => $transaction) {
            $this->transactions[$key]['category_id'] = '';
        }
    }

    public function storeTransactions()
    {
        $this->validate();

        DB::transaction(function () {
            foreach ($this->transactions as $transactionData) {
                $transaction = Transaction::create([
                    'unique_id' => generateUniqueID(Transaction::class, 'unique_id'),
                    'amount' => $transactionData['amount'],
                    'payment_method' => $this->payment_method,
                    'account_id' => $this->account_id,
                    'category_id' => $transactionData['category_id'],
                    'date' => $transactionData['date'],
                    'description' => $transactionData['description'] ?: $this->description,
                    'type' => $this->type,
                    'added_by' => Auth::id(),
                ]);

                // Gracefully update account balance based on transaction type
                if ($transaction->type == 1) {
                    // Income: add to account
                    AccountsController::addToAccount($transaction->account_id, $transaction->amount);
                } elseif ($transaction->type == 2) {
                    // Expense: subtract from account
                    AccountsController::subtractFromAccount($transaction->account_id, $transaction->amount);
                }
            }
        });

        session()->flash('success', 'Transactions created and account balances updated!');

        return $this->redirectRoute('accountflow::transactions', navigate: true);
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        $payment_methods = PaymentMethod::all();
        $accounts = Account::where('active', true)->get();
        $income_categories = Category::whereNotNull('parent_id')->where('type', 1)->get();
        $expense_categories = Category::whereNotNull('parent_id')->where('type', 2)->get();

        return view($viewpath.'livewire.transactions.create-transaction-multiple', [
            'payment_methods' => $payment_methods,
            'accounts' => $accounts,
            'income_categories' => $income_categories,
            'expense_categories' => $expense_categories,
            'categories' => $this->type == 1 ? $income_categories : $expense_categories,
        ])->extends($layout)->section('content');
    }
}

