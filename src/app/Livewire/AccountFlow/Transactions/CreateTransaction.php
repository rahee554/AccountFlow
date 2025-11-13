<?php

namespace App\Livewire\AccountFlow\Transactions;

use App\Http\Controllers\AccountFlow\AccountsController;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\PaymentMethod;
use App\Models\AccountFlow\Setting;
use App\Models\AccountFlow\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTransaction extends Component
{
    public $transactionId;

    public $amount;

    public $payment_method;

    public $account_id;

    public $category_id;

    public $date;

    public $description;

    public $payment_methods;

    public $accounts;

    public $income_categories;

    public $expense_categories;

    public $type = 1; // 1 = Income, 2 = Expense

    public $isEdit = false; // Track if we're in edit mode

    public function mount($id = null)
    {
        $this->payment_methods = PaymentMethod::get(['id', 'name', 'account_id']);
        $this->accounts = Account::where('active', true)->get();
        $this->income_categories = Category::whereNotNull('parent_id')->where('type', 1)->get();
        $this->expense_categories = Category::whereNotNull('parent_id')->where('type', 2)->get();

        if ($id) {
            // Decode base64 ID for edit mode
            try {
                $decodedId = base64_decode($id);
                $this->isEdit = true;
                $this->transactionId = $decodedId;

                $transaction = Transaction::findOrFail($decodedId);

                $this->amount = $transaction->amount;
                $this->payment_method = $transaction->payment_method;
                $this->account_id = $transaction->account_id;
                $this->category_id = $transaction->category_id;
                $this->date = $transaction->date;
                $this->description = $transaction->description;
                $this->type = $transaction->type;

            } catch (\Exception $e) {
                session()->flash('error', 'Invalid transaction ID or transaction not found.');

                return redirect()->route('accountflow::transactions');
            }
        } else {
            $this->date = now()->format('Y-m-d');
            // Set default payment method from settings if available
            $defaultPaymentMethodId = Setting::defaultPaymentMethodId();
            if ($defaultPaymentMethodId && PaymentMethod::find($defaultPaymentMethodId)) {
                $this->payment_method = $defaultPaymentMethodId;
                $this->updateAccountFromPaymentMethod();
            }
            // Set default transaction type from settings
            $this->type = Setting::defaultTransactionType();
        }
    }

    public function changeType($value)
    {
        $this->type = $value;
        $this->category_id = null;
    }

    public function updatedType(): void
    {
        $this->category_id = null;
    }

    public function updatedPaymentMethod($value)
    {
        if ($value) {
            $this->updateAccountFromPaymentMethod();
        } else {
            $this->account_id = null;
        }
    }

    protected function updateAccountFromPaymentMethod(): void
    {
        if ($this->payment_method) {
            $paymentMethod = PaymentMethod::find($this->payment_method);
            if ($paymentMethod && $paymentMethod->account_id) {
                $this->account_id = $paymentMethod->account_id;
            } else {
                $this->account_id = null;
            }
        }
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

        try {
            if ($this->isEdit) {
                // Update existing transaction
                $transaction = Transaction::findOrFail($this->transactionId);

                // If account, amount, or type changed, adjust balances
                if (
                    $transaction->account_id != $this->account_id ||
                    $transaction->amount != $this->amount ||
                    $transaction->type != $this->type
                ) {
                    // Revert previous transaction effect
                    if ($transaction->type == 1) {
                        // Previous was income, subtract from account
                        AccountsController::subtractFromAccount($transaction->account_id, $transaction->amount);
                    } else {
                        // Previous was expense, add back to account
                        AccountsController::addToAccount($transaction->account_id, $transaction->amount);
                    }

                    // Apply new transaction effect
                    if ($this->type == 1) {
                        // New is income, add to account
                        AccountsController::addToAccount($this->account_id, $this->amount);
                    } else {
                        // New is expense, subtract from account
                        AccountsController::subtractFromAccount($this->account_id, $this->amount);
                    }
                }

                $transaction->update([
                    'amount' => $this->amount,
                    'payment_method' => $this->payment_method,
                    'account_id' => $this->account_id,
                    'category_id' => $this->category_id,
                    'date' => $this->date,
                    'description' => $this->description,
                    'type' => $this->type,
                ]);

                session()->flash('success', 'Transaction updated successfully!');
            } else {
                // Create new transaction
                $transaction = Transaction::create([
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

                // Apply effect based on type
                if ($this->type == 1) {
                    AccountsController::addToAccount($transaction->account_id, $transaction->amount);
                } else {
                    AccountsController::subtractFromAccount($transaction->account_id, $transaction->amount);
                }

                session()->flash('success', 'Transaction created successfully!');
            }

            return $this->redirectRoute('accountflow::transactions', navigate: true);
            $this->dispatch('refreshTable');
        } catch (\Exception $e) {
            session()->flash('error', 'Error '.($this->isEdit ? 'updating' : 'creating').' transaction: '.$e->getMessage());
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view(
            $viewpath.'livewire.transactions.create-transaction',
            [
                'categories' => $this->getCategoriesProperty(),
            ]
        )->extends($layout)->section('content');
    }
}
