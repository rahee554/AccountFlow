# Migration Guide: Using TransactionService

This guide shows how to update existing code to use the new `TransactionService` for cleaner, more maintainable transaction management.

## Before and After Examples

### Example 1: Creating a Simple Transaction

**BEFORE** (inline in component):
```php
$transaction = Transaction::create([
    'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
    'amount' => $this->amount,
    'type' => 1,
    'payment_method' => Setting::defaultPaymentMethodId(),
    'category_id' => null,
    'date' => now(),
    'description' => $this->description,
    'user_id' => auth()->id(),
    'account_id' => Setting::defaultAccountId(),
]);
```

**AFTER** (using service):
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$transaction = TransactionService::create([
    'amount' => $this->amount,
    'description' => $this->description,
]);
```

✅ Cleaner, DRY, defaults handled automatically

---

### Example 2: Creating Income Transaction with Category

**BEFORE**:
```php
$paymentMethod = PaymentMethod::find(Setting::defaultPaymentMethodId());
$account = $paymentMethod?->account_id ?? Setting::defaultAccountId();

if (!Account::find($account)) {
    throw new Exception('Invalid account');
}

$transaction = Transaction::create([
    'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
    'amount' => $this->amount,
    'type' => 1,
    'payment_method' => Setting::defaultPaymentMethodId(),
    'category_id' => $this->category_id,
    'date' => now(),
    'description' => $this->description,
    'user_id' => auth()->id(),
    'account_id' => $account,
]);
```

**AFTER**:
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$transaction = TransactionService::createIncome([
    'amount' => $this->amount,
    'category_id' => $this->category_id,
    'description' => $this->description,
]);
```

✅ Account auto-resolved, validation built-in, type is income by default

---

### Example 3: Creating Transaction with Specific Payment Method

**BEFORE**:
```php
$paymentMethod = PaymentMethod::find($this->payment_method_id);
$account = $paymentMethod?->account_id ?? Setting::defaultAccountId();

$transaction = Transaction::create([
    'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
    'amount' => $this->amount,
    'type' => $this->type,
    'payment_method' => $this->payment_method_id,
    'category_id' => $this->category_id ?? null,
    'date' => $this->date ?? now(),
    'description' => $this->description,
    'user_id' => auth()->id(),
    'account_id' => $account,
]);
```

**AFTER**:
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$transaction = TransactionService::create([
    'amount' => $this->amount,
    'type' => $this->type,
    'payment_method' => $this->payment_method_id,
    'category_id' => $this->category_id,
    'date' => $this->date,
    'description' => $this->description,
]);
```

✅ Much cleaner, no manual account resolution needed

---

### Example 4: Creating Multiple Transactions

**BEFORE**:
```php
$transactions = [];
foreach ($items as $item) {
    $transaction = Transaction::create([
        'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
        'amount' => $item['amount'],
        'type' => 2,
        'payment_method' => Setting::defaultPaymentMethodId(),
        'category_id' => $item['category_id'],
        'date' => now(),
        'description' => $item['description'],
        'user_id' => auth()->id(),
        'account_id' => Setting::defaultAccountId(),
    ]);
    $transactions[] = $transaction;
}
```

**AFTER**:
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$transactions = TransactionService::createBatch(
    collect($items)->map(fn ($item) => [
        'amount' => $item['amount'],
        'type' => 2,
        'category_id' => $item['category_id'],
        'description' => $item['description'],
    ])->toArray()
);
```

✅ Single atomic operation, all transactions created in one transaction

---

### Example 5: Using Helper Functions

**BEFORE**:
```php
$transaction = Transaction::create([...]);
```

**AFTER** (option 1 - using helper):
```php
$transaction = create_transaction([
    'amount' => 1000,
    'description' => 'Sale'
]);

$income = create_income([
    'amount' => 2000,
    'category_id' => 1
]);

$expense = create_expense([
    'amount' => 500,
    'category_id' => 5
]);
```

✅ Even shorter syntax with global helpers

---

### Example 6: Updating a Transaction

**BEFORE**:
```php
$transaction->update([
    'amount' => $newAmount,
    'description' => $newDescription,
]);
```

**AFTER**:
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$transaction = TransactionService::update($transaction, [
    'amount' => $newAmount,
    'description' => $newDescription,
]);
```

✅ Validation and consistency checks included

---

### Example 7: Reversing a Transaction

**BEFORE**:
```php
// Manual reversal - error prone
$reverseTransaction = Transaction::create([
    'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
    'amount' => -$transaction->amount,
    'type' => $transaction->type,
    'payment_method' => $transaction->payment_method,
    'account_id' => $transaction->account_id,
    'category_id' => $transaction->category_id,
    'date' => now(),
    'description' => "REVERSAL: " . $transaction->description,
    'user_id' => auth()->id(),
]);
```

**AFTER**:
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$reverseTransaction = TransactionService::reverse(
    $transaction,
    'Correcting accounting error'
);
```

✅ Simplified, consistent reversal logic

---

### Example 8: Getting Data for Forms

**BEFORE**:
```php
$paymentMethods = PaymentMethod::where('status', 1)->orderBy('name')->get();
$categories = Category::where('type', 1)->where('status', 1)->orderBy('name')->get();
```

**AFTER**:
```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

$paymentMethods = TransactionService::getActivePaymentMethods();
$categories = TransactionService::getCategoriesForType(1);
```

✅ Consistent queries, reusable across the application

---

## Migration Checklist

When updating your code to use `TransactionService`:

- [ ] Replace direct `Transaction::create()` calls with `TransactionService::create()`
- [ ] Use `createIncome()` or `createExpense()` instead of setting `type` manually
- [ ] Remove manual unique ID generation (it's automatic now)
- [ ] Remove manual account resolution (it's automatic now)
- [ ] Remove manual setting of default payment method (it's automatic now)
- [ ] Remove validation logic (it's built-in now)
- [ ] Use `createBatch()` for multiple transactions instead of loops
- [ ] Use helper functions for shorter syntax where appropriate
- [ ] Update tests to use the service instead of mocking model methods

---

## Common Patterns

### Pattern 1: In a Livewire Component

```php
namespace App\Livewire;

use ArtflowStudio\AccountFlow\App\Services\TransactionService;
use Livewire\Component;

class CreateTransaction extends Component
{
    public $amount = '';
    public $description = '';
    public $category_id = '';

    public function save()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ]);

        try {
            $transaction = TransactionService::create([
                'amount' => $this->amount,
                'category_id' => $this->category_id,
                'description' => $this->description,
            ]);

            $this->dispatch('transaction-created', transaction: $transaction->id);
        } catch (\Exception $e) {
            $this->addError('amount', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.create-transaction', [
            'paymentMethods' => TransactionService::getActivePaymentMethods(),
            'incomeCategories' => TransactionService::getCategoriesForType(1),
            'expenseCategories' => TransactionService::getCategoriesForType(2),
        ]);
    }
}
```

### Pattern 2: In a Controller

```php
namespace App\Http\Controllers;

use ArtflowStudio\AccountFlow\App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:1,2',
            'category_id' => 'nullable|exists:ac_categories,id',
            'description' => 'required|string',
        ]);

        try {
            $transaction = TransactionService::create($validated);
            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaction created');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
```

### Pattern 3: In a Service/Action

```php
namespace App\Services;

use ArtflowStudio\AccountFlow\App\Services\TransactionService;

class InvoiceService
{
    public function recordPayment($invoice, $amount, $paymentMethodId)
    {
        return TransactionService::create([
            'amount' => $amount,
            'type' => 1, // Income
            'payment_method' => $paymentMethodId,
            'category_id' => $invoice->category_id,
            'description' => "Payment for Invoice #{$invoice->reference}",
        ]);
    }
}
```

---

## Error Handling

```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

try {
    $transaction = TransactionService::create([
        'amount' => $amount,
        'type' => $type,
        'payment_method' => $paymentMethodId,
    ]);
} catch (\Exception $e) {
    // Handle specific errors
    match ($e->getMessage()) {
        'Transaction amount must be greater than 0' => 
            $this->addError('amount', 'Amount must be positive'),
        'Transaction type must be 1 (income) or 2 (expense)' => 
            $this->addError('type', 'Invalid transaction type'),
        'Payment method not found or inactive' => 
            $this->addError('payment_method', 'Payment method unavailable'),
        default => 
            $this->addError('transaction', 'Failed to create transaction'),
    };
}
```

---

## Benefits Summary

| Aspect | Before | After |
|--------|--------|-------|
| **Code Length** | 15-20 lines | 3-5 lines |
| **Defaults** | Manual | Automatic |
| **Validation** | Manual | Built-in |
| **Consistency** | Variable | Guaranteed |
| **Account Resolution** | Manual | Automatic |
| **Unique IDs** | Manual | Automatic |
| **Error Handling** | Per-location | Centralized |
| **Testing** | Difficult | Easy (service level) |
| **Maintainability** | Low | High |

---

## Questions?

Refer to:
- `TRANSACTION_SERVICE_USAGE.md` - Detailed usage examples
- `ARCHITECTURE.md` - Architecture and design decisions
- Package: `artflow-studio/accountflow`
