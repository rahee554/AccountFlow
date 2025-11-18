# AccountFlow TransactionService Architecture

## Overview

The `TransactionService` is a comprehensive service layer for the AccountFlow package that handles all transaction creation, updates, deletions, and reversals with intelligent defaults and auto-management of related data.

## Location

**Package**: `artflow-studio/accountflow`
**Path**: `src/app/Services/TransactionService.php`
**Namespace**: `ArtflowStudio\AccountFlow\App\Services\TransactionService`

## Architecture Decision

The service was created in the AccountFlow **package** rather than in the application layer because:

1. **Reusability** - It's core AccountFlow functionality used across multiple modules
2. **Consistency** - Ensures all parts of the application use the same transaction creation logic
3. **Maintainability** - Single source of truth for transaction business logic
4. **Package-driven** - AccountFlow is a package managing its own domain logic

## Key Features

### 1. Auto-Generated Unique IDs

- Uses `generateUniqueId()` from `artflow-studio/snippets` package
- Format: `TXN-YYYYMMDD-XXXXX`
- Automatically ensures database uniqueness
- **NOT custom-built** - leverages package utility

```php
'unique_id' => generateUniqueId(Transaction::class, 'unique_id')
```

### 2. Smart Defaults

When fields are not provided, the service auto-fills from settings:

| Field | Default Source | Fallback |
|-------|---|---|
| `type` | Provided value | `Setting::defaultTransactionType()` |
| `payment_method` | Provided value | `Setting::defaultPaymentMethodId()` |
| `account_id` | Provided value | PaymentMethod's linked account → `Setting::defaultAccountId()` |
| `category_id` | Provided value | (remains null) |
| `date` | Provided value | `Carbon::now()` |
| `user_id` | Provided value | `auth()->id()` |

### 3. Auto Account Resolution

When no `account_id` is provided:

1. ✅ Check if PaymentMethod has a linked account → Use it
2. ✅ No linked account? → Use default account from settings
3. ✅ Resolved account is validated to exist

```php
if (empty($data['account_id'])) {
    $paymentMethod = PaymentMethod::find($transactionData['payment_method']);
    $transactionData['account_id'] = $paymentMethod?->account_id ?? Setting::defaultAccountId();
}
```

### 4. Validation

All transactions are validated before creation:

- ✅ Amount > 0
- ✅ Type is 1 (income) or 2 (expense)
- ✅ Account exists
- ✅ Payment method exists and is active
- ✅ Category exists (if provided)

### 5. Database Transactions

All operations wrap in `DB::transaction()` for data consistency:

```php
return DB::transaction(function () use ($data) {
    // Create transaction safely
    $transaction = Transaction::create($transactionData);
    return $transaction;
});
```

## Public Methods

### Create Methods

```php
// Basic create
TransactionService::create(array $data): Transaction

// Income transaction (type = 1)
TransactionService::createIncome(array $data): Transaction

// Expense transaction (type = 2)
TransactionService::createExpense(array $data): Transaction

// Multiple transactions
TransactionService::createBatch(array $transactions): Collection
```

### Update & Delete

```php
TransactionService::update(Transaction $transaction, array $data): Transaction
TransactionService::delete(Transaction $transaction): bool
```

### Reversals

```php
TransactionService::reverse(Transaction $transaction, ?string $reason = null): Transaction
```

### Queries & Utilities

```php
// Get accounts
TransactionService::resolveAccount(?int $accountId, ?int $paymentMethodId): ?Account

// Get data for forms
TransactionService::getActivePaymentMethods(): Collection
TransactionService::getCategoriesForType(int $type): Collection

// Analytics
TransactionService::getSummary(?string $startDate, ?string $endDate, ?int $accountId): array
```

## Helper Functions

The package provides convenient helper functions in `src/app/Helpers/AccountFlowHelper.php`:

```php
// Global helpers
transaction_service(): TransactionService
create_transaction(array $data): Transaction
create_income(array $data): Transaction
create_expense(array $data): Transaction
```

## Integration Points

### Used In

- `CreateInvoice` Livewire component
- `CreateTransaction` Livewire component
- `CreateAssetTransaction` Livewire component
- Other AccountFlow modules

### Replaces

Previously, transactions were created directly in components with inline logic:

```php
// BEFORE (in components)
'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
'amount' => (float) $data['amount'],
'type' => 1,
// ... manual defaults
Transaction::create($transactionData);

// AFTER (using service)
TransactionService::create([
    'amount' => $data['amount'],
    // Defaults handled automatically
]);
```

## Package Registration

The service is automatically available because:

1. ✅ Namespace registered in package's `composer.json`
2. ✅ Helper functions registered in package's `composer.json`
3. ✅ No additional service provider registration needed

**Package composer.json**:
```json
{
  "autoload": {
    "psr-4": {
      "ArtflowStudio\\AccountFlow\\App\\": "src/app/"
    },
    "files": [
      "src/app/Helpers/AccountFlowHelper.php"
    ]
  }
}
```

## Data Flow

```
┌─────────────────────────────────────┐
│ Livewire Component / Controller     │
└─────────────────┬───────────────────┘
                  │
                  ▼
        ┌─────────────────────┐
        │ TransactionService  │
        │  - Normalize type   │
        │  - Generate ID      │
        │  - Resolve account  │
        │  - Validate data    │
        └─────────────┬───────┘
                      │
                      ▼
        ┌──────────────────────────┐
        │ DB::transaction()         │
        │ (Ensure data consistency) │
        └─────────────┬────────────┘
                      │
                      ▼
        ┌──────────────────────────┐
        │ Transaction::create()     │
        │ (Eloquent Model)          │
        └──────────────────────────┘
```

## Error Handling

```php
try {
    $transaction = TransactionService::create([
        'amount' => 1000,
        'account_id' => 9999
    ]);
} catch (\Exception $e) {
    // "Account #9999 not found"
}
```

## Example Usage in Components

```php
namespace App\Livewire\BranchManager\Invoices;

use ArtflowStudio\AccountFlow\App\Services\TransactionService;
use Livewire\Component;

class CreateInvoice extends Component
{
    public function saveInvoice()
    {
        // Create related transaction
        $transaction = TransactionService::create([
            'amount' => $this->total,
            'type' => 1, // Income
            'category_id' => Setting::defaultSalesCategoryId(),
            'description' => "Invoice #{$invoice->reference}"
        ]);
        // Account resolved automatically from default payment method
    }
}
```

## Future Enhancements

Potential additions to the service:

1. **Event Dispatching** - `TransactionCreated`, `TransactionUpdated` events
2. **Audit Logging** - Track changes to transactions
3. **Batch Hooks** - Before/after callbacks for batch operations
4. **Statistics Caching** - Cache summary statistics for performance
5. **Related Models** - Auto-create related models (InvoiceTransaction, etc.)
6. **Payment Synchronization** - Update account balance on transaction create
7. **Tax Handling** - Apply tax rules during creation
8. **Multi-currency** - Support for currency conversions

## Testing

When writing tests for the service:

```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;
use ArtflowStudio\AccountFlow\App\Models\Transaction;

it('creates transaction with auto-filled defaults', function () {
    $transaction = TransactionService::create([
        'amount' => 1000
    ]);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->unique_id)->toStartWith('TXN-');
    expect($transaction->payment_method)->toBe(Setting::defaultPaymentMethodId());
});
```

## Related Documentation

- See `TRANSACTION_SERVICE_USAGE.md` for detailed usage examples
- Check AccountFlow models for entity relationships
- Review `artflow-studio/snippets` for `generateUniqueId()` documentation
