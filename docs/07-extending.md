# 7 — Extending

## Swapping a service

Every service is an ordinary class bound as a container singleton, so any of
them can be replaced:

```php
// AppServiceProvider::register()
$this->app->singleton(
    \ArtflowStudio\AccountFlow\Services\ReportService::class,
    \App\Accounting\MyReportService::class,
);
```

Services have **no static methods**. 0.2.x declared them static while also
binding them as singletons — two contradictory designs — which made them
impossible to mock, extend or inject.

```php
// all reach the same instance
Accountflow::transactions();
accountflow()->transactions();
app(TransactionService::class);
public function __construct(private TransactionService $transactions) {}
```

## Listening to events

```php
use ArtflowStudio\AccountFlow\Events\TransactionCreated;
use Illuminate\Support\Facades\Event;

Event::listen(TransactionCreated::class, function (TransactionCreated $event) {
    // $event->transaction
});
```

| Event | Carries |
|-------|---------|
| `TransactionCreated` | `$transaction` |
| `TransactionUpdated` | `$transaction`, `$before` |
| `TransactionDeleted` | `$transaction` |
| `TransactionReversed` | `$original`, `$reversal`, `$reason` |
| `AccountBalanceChanged` | `$account`, `$from`, `$to`, `delta()` |

The bundled `Listeners\AuditSubscriber` subscribes to these. Wiring the audit
trail to events rather than scattering `AuditService::log()` calls through the
services means a new write path cannot forget to log — in 0.2.x `AuditService`
was never called from anywhere, so the audit trail had a table, a settings flag,
a route and a UI page, and recorded nothing.

## Attaching your own models

The package does not know about your application's models. Two hooks:

```php
// config/accountflow.php
'models' => [
    'invoice_payment' => \App\Models\InvoicePayment::class,
],
```

That registers `$transaction->invoicePayment` at boot. 0.2.x hardcoded
`\App\Models\InvoicePayment` inside the `Transaction` model, which tied the
package to one specific CRM.

User relations resolve from `auth.providers.users.model`, so `Transaction::author()`,
`Budget::creator()`, `Transfer` and `AuditTrail::user()` follow your auth config
rather than assuming `App\Models\User`.

## Overriding views

```bash
php artisan vendor:publish --tag=accountflow-views
```

Published to `resources/views/vendor/accountflow/`. Set `accountflow.layout` to
your own layout — it needs `@stack('styles')` in `<head>` and `@stack('scripts')`
before `</body>`, which is how the dashboard CSS and JS get in.

## Custom listings

`Support\TableColumns` returns plain arrays; build your own or extend theirs:

```php
$columns = array_merge(
    TableColumns::transactions(),
    [['key' => 'invoice_id', 'label' => 'Invoice']],
);
```

## Custom abilities

```php
Gate::define('accountflow.manage-transactions', fn ($user) => $user->isAccountant());
```

See [05 — Authorization](05-authorization.md).

## Testing against the package

```php
use ArtflowStudio\AccountFlow\Models\Transaction;

Transaction::factory()->income()->create(['amount' => 500]);
Account::factory()->withOpeningBalance(1000)->create();
PaymentMethod::factory()->inactive()->create();
Category::factory()->expense()->create();
```

Factories resolve through `Concerns\HasPackageFactory`, which points Eloquent at
the package's factory namespace instead of the host application's.
