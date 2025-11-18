# AccountFlow TransactionService

A comprehensive service for managing transactions with intelligent defaults in the AccountFlow package.

## Features

- **Auto-generated Unique IDs**: Uses the `artflow-studio/snippets` package's `generateUniqueId()` function
- **Smart Defaults**: Automatically fills in payment method, account, and category from settings when not provided
- **Auto Account Resolution**: Resolves account from payment method when not explicitly provided
- **Batch Operations**: Create multiple transactions in a single database transaction
- **Transaction Types**: Easy methods for creating income and expense transactions
- **Reversals**: Create reversing entries for transactions
- **Statistics**: Get summaries and analytics of transactions
- **Validation**: Built-in validation of transaction data

## Installation

The `TransactionService` is included in the AccountFlow package. No additional installation needed.

## Usage Examples

### Basic Transaction Creation

```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

// Create a transaction with minimum data (uses defaults)
$transaction = TransactionService::create([
    'amount' => 1000,
    'description' => 'Sales revenue'
]);

// Create a transaction with full data
$transaction = TransactionService::create([
    'amount' => 1500.50,
    'type' => 1, // 1=income, 2=expense
    'payment_method' => 1,
    'account_id' => 2,
    'category_id' => 5,
    'date' => now(),
    'description' => 'Invoice payment received',
    'user_id' => auth()->id()
]);
```

### Using Helper Functions

```php
// Using the global helper
$transaction = create_transaction([
    'amount' => 1000,
    'description' => 'Sales'
]);

// Create income transaction (type = 1)
$transaction = create_income([
    'amount' => 2000,
    'category_id' => 2,
    'description' => 'Sales revenue'
]);

// Create expense transaction (type = 2)
$transaction = create_expense([
    'amount' => 500,
    'category_id' => 5,
    'description' => 'Office supplies'
]);

// Get service instance
$service = transaction_service();
$activePaymentMethods = $service->getActivePaymentMethods();
```

### Batch Operations

```php
// Create multiple transactions at once
$transactions = TransactionService::createBatch([
    [
        'amount' => 1000,
        'type' => 1,
        'description' => 'Invoice #1001'
    ],
    [
        'amount' => 1500,
        'type' => 1,
        'description' => 'Invoice #1002'
    ],
    [
        'amount' => 500,
        'type' => 2,
        'description' => 'Expense'
    ]
]);
```

### Update & Delete

```php
// Update a transaction
$transaction = TransactionService::update($transaction, [
    'amount' => 2000,
    'description' => 'Updated description'
]);

// Delete a transaction
TransactionService::delete($transaction);
```

### Reversals

```php
// Reverse a transaction (creates a reversing entry)
$reversalTransaction = TransactionService::reverse(
    $transaction,
    'Correcting invoice error'
);
```

### Get Account for Transaction

```php
// Auto-resolve account
$account = TransactionService::resolveAccount(
    accountId: null,
    paymentMethodId: 1
);

// Or with explicit account
$account = TransactionService::resolveAccount(accountId: 2);
```

### Get Data for Forms

```php
// Get active payment methods for dropdowns
$paymentMethods = TransactionService::getActivePaymentMethods();

// Get categories for a transaction type
$incomeCategories = TransactionService::getCategoriesForType(1);
$expenseCategories = TransactionService::getCategoriesForType(2);
```

### Get Statistics

```php
// Get summary statistics
$summary = TransactionService::getSummary(
    startDate: '2024-01-01',
    endDate: '2024-12-31',
    accountId: 2
);

// Results include:
// - total_income
// - total_expense
// - net (income - expense)
// - count
// - by_category (grouped by category)
// - by_payment_method (grouped by payment method)
```

## Auto-Default Behavior

When creating a transaction without specifying these fields, the service uses:

### Payment Method
- Uses provided `payment_method`, OR
- Falls back to `Setting::defaultPaymentMethodId()` from settings

### Account
- Uses provided `account_id`, OR
- Resolves from PaymentMethod's linked account, OR
- Falls back to `Setting::defaultAccountId()` from settings

### Category
- Uses provided `category_id`, OR
- Left null (optional)

### Type
- Uses provided `type`, OR
- Falls back to `Setting::defaultTransactionType()` from settings

### Date
- Uses provided `date`, OR
- Defaults to `now()`

### User
- Uses provided `user_id`, OR
- Defaults to `auth()->id()`

## Validation

The service validates:
- Amount must be greater than 0
- Type must be 1 (income) or 2 (expense)
- Account must exist
- Payment method must exist and be active (status = 1)
- Category must exist (if provided)

## Transaction Uniqueness

Unique IDs are generated using the `generateUniqueId()` function from `artflow-studio/snippets`:
- Format: `TXN-YYYYMMDD-XXXXX` (e.g., `TXN-20240115-A1B2C`)
- Automatically ensures uniqueness by checking the database
- Generated during transaction creation

## Error Handling

```php
try {
    $transaction = TransactionService::create([
        'amount' => -100 // Invalid: negative amount
    ]);
} catch (\Exception $e) {
    // "Transaction amount must be greater than 0"
    echo $e->getMessage();
}

try {
    $transaction = TransactionService::create([
        'amount' => 1000,
        'type' => 99 // Invalid: wrong type
    ]);
} catch (\Exception $e) {
    // "Transaction type must be 1 (income) or 2 (expense)"
    echo $e->getMessage();
}

try {
    $transaction = TransactionService::create([
        'amount' => 1000,
        'account_id' => 9999 // Invalid: account doesn't exist
    ]);
} catch (\Exception $e) {
    // "Account #9999 not found"
    echo $e->getMessage();
}
```

## Database Transactions

All operations use Laravel's database transactions to ensure data consistency:
- Create operations wrap in `DB::transaction()`
- Update operations wrap in `DB::transaction()`
- Delete operations wrap in `DB::transaction()`
- Batch operations wrap in `DB::transaction()`
- Reversals wrap in `DB::transaction()`

## Type Values

- `1` or `'income'` = Income transaction
- `2` or `'expense'` = Expense transaction

Both integer and string values are accepted and normalized to integers.

## Integration with Livewire

When using in Livewire components:

```php
<?php

namespace App\Livewire;

use ArtflowStudio\AccountFlow\App\Services\TransactionService;
use Livewire\Component;

class CreateTransaction extends Component
{
    public $amount;
    public $description;

    public function save()
    {
        $transaction = TransactionService::create([
            'amount' => $this->amount,
            'description' => $this->description,
            // Other fields auto-filled from defaults
        ]);

        return redirect()->route('transactions.show', $transaction);
    }

    public function render()
    {
        return view('livewire.create-transaction', [
            'paymentMethods' => TransactionService::getActivePaymentMethods(),
            'incomeCategories' => TransactionService::getCategoriesForType(1),
        ]);
    }
}
```

## Notes

- The service uses the `generateUniqueId()` function from `artflow-studio/snippets`
- All dates are parsed using Laravel's Carbon library
- User ID defaults to currently authenticated user
- The service is stateless and uses static methods for simplicity
