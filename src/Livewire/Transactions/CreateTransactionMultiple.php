<?php

namespace ArtflowStudio\AccountFlow\Livewire\Transactions;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTransactionMultiple extends Component
{
    use AuthorizesAccountFlow;

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
        $this->authorizeAccountFlow(Ability::ManageTransactions);
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
        $this->authorizeAccountFlow(Ability::ManageTransactions);

        $this->validate();

        // Routed through createBatch() rather than writing rows and nudging the
        // balance by hand. That old path skipped validation, raised no events —
        // so nothing reached the audit trail — and adjusted the balance in a
        // second step that could succeed after the insert had already happened.
        // createBatch() is atomic: one bad row and none of them are written.
        try {
            Accountflow::transactions()->createBatch(
                array_map(fn (array $row): array => [
                    'type' => $this->type,
                    'amount' => $row['amount'],
                    'category_id' => $row['category_id'],
                    'account_id' => $this->account_id,
                    'payment_method' => $this->payment_method,
                    'date' => $row['date'],
                    'description' => $row['description'] ?: $this->description,
                    'user_id' => Auth::id(),
                ], $this->transactions),
            );
        } catch (AccountFlowException $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }

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
            'categories' => (int) $this->type === TransactionType::Income->value ? $income_categories : $expense_categories,
        ])->extends($layout)->section('content');
    }
}
