# 4 — Reports

```php
$reports = Accountflow::reports();
```

Every method aggregates in SQL and is driver-portable across MySQL, PostgreSQL,
SQLite and SQL Server. 0.2.x pulled the whole transactions table into PHP for
each report and grouped it in memory, with an N+1 on `category` and
`paymentMethod` inside every map.

## Methods

| Method | Returns |
|--------|---------|
| `incomeExpenseReport($from, $to, $accountId)` | Totals plus per-category breakdown |
| `profitAndLoss($from, $to, $accountId)` | Revenue, expenses, profit, margin |
| `cashFlowReport($from, $to, $accountId)` | Inflow/outflow by month |
| `balanceReport()` | Balance per active account |
| `byPaymentMethod($from, $to, $accountId)` | Totals per method |
| `categoryPerformance($from, $to, $accountId)` | Totals and average per category |
| `dailySummary($from, $to, $accountId)` | Per-day income, expense, net |
| `ledger($accountId, $from, $to)` | Entries with a running balance |

All date arguments are optional; omit them for all time.

## Null relations are reported, not fatal

`payment_method` is nullable. 0.2.x wrote `$group->first()->paymentMethod->id`,
so a single transaction without a payment method killed the whole report. Such
rows now appear as `Unassigned`, and category-less rows as `Uncategorised`.

## Ledger

```php
$ledger = $reports->ledger($accountId, '2026-01-01', '2026-03-31');

$ledger['opening_balance'];   // includes everything before the window
$ledger['closing_balance'];
$ledger['entries'];           // each with debit, credit, signed_amount, balance
```

The opening balance folds in every entry before the start date, so a windowed
ledger still reconciles.

## Driver portability

`Support\SqlDialect` supplies the date-grouping expression per driver.
0.2.x used `DATE_FORMAT()` and 17 double-quoted SQL string literals
(`type IN ("income","1",1)`) — both MySQL-only, since `"income"` is an
*identifier* in PostgreSQL and in SQLite under ANSI_QUOTES. That is precisely
what stopped the package being testable against SQLite.
