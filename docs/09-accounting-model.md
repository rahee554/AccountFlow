# 9 — The accounting model

AccountFlow is a **cash book, by design**. It is built for the person running a
business, not for their accountant — so it deliberately avoids double-entry
bookkeeping, which is the single biggest source of confusion for non-accountants.

You record money in and money out against real accounts. No debits and credits,
no journals, no chart of accounts to set up before you can enter your first
expense. That simplicity is the product, not a shortcut.

This page says plainly what that choice does and does not give you, so nobody
discovers the boundary at the wrong moment.

## How it works

**A cash book.** Every entry is a row in `ac_transactions` with a `type`
(1 = income, 2 = expense), an `account_id`, an `amount` and a `date`. An
account's balance is `opening_balance + Σ income − Σ expense`, stored
denormalised in `accounts.balance` and rebuildable at any time with
`recalculateBalance()`.

That model is a good fit for:

- tracking money in and out of real cash, bank and wallet accounts
- categorised income and expense reporting
- per-account statements with a running balance
- budgets against categories
- registers of assets, loans, equity partners and scheduled payments

## What it deliberately leaves out

**It is not a double-entry general ledger, on purpose.** Double-entry is the
right model for a professional bookkeeper and the wrong one for a business
owner: it demands typed ledger accounts, balanced journal lines and a period
close before it gives you anything back. AccountFlow trades that away for
"type an amount, pick a category, done".

What you give up with that trade:

| Concept | Status |
|---------|--------|
| Journal entries with balanced Dr/Cr lines | ✗ not modelled |
| Chart of accounts with account *types* (asset / liability / income / expense / equity) | ✗ `accounts` are cash accounts only |
| Trial balance that balances by construction | ✗ see below |
| Accounts receivable / payable as ledger accounts | ✗ registers only |
| Fiscal periods and period close | ✗ any date is postable, forever |
| Multi-currency | ✗ one currency per install |
| Tax / VAT handling | ✗ not modelled |
| Depreciation schedules | ✗ assets are a register, not depreciated |

### The trial balance does not balance by construction

`Reports\TrialBalance` treats expenses as debits and income as credits per cash
account. In real double-entry, a trial balance sums *every* ledger account and
debits equal credits because each journal entry balances. Here the two totals
are just total expense and total income — they match only by coincidence.

### The balance sheet is a comparison, not an identity

`Reports\BalanceSheet` computes assets from account balances, liabilities from
loan amounts, and equity from partner equity plus retained earnings, then
reports `isBalanced` by comparing the two sides. Nothing forces them to agree,
because those figures come from unrelated registers rather than from one
balanced ledger. Expect `isBalanced` to be false on real data. Treat it as a
reconciliation hint, not an assertion.

## What this means in practice

**Use it for** what an owner actually asks for day to day: where the money
went, what I spent on what, how much is in each account, am I over budget,
what do I still owe on that loan. It handles all of that well.

**Hand off to an accountant for** statutory accounts, tax filings and anything
that must satisfy a formal trial balance. AccountFlow is the system of record
for your cash; it is not the thing that files your return. Export the
transaction list and let a professional take it from there.

**A note on the two accountant-style reports.** Trial Balance and Balance Sheet
ship behind feature flags (`trial_balance_report`, `profit_loss_report`). If
your users are not accountants, consider switching Trial Balance off — it will
show figures that look wrong to a non-specialist for the structural reason
above:

    AccountFlow::features()->disable('trial_balance');

## Things that *are* enforced

Within the cash-book model, the invariants that matter are held:

- **One write path.** `TransactionService::create()` is the only method that
  inserts, so validation, balance updates and events cannot be bypassed.
- **Balances cannot race.** Every balance write takes a row lock.
- **Balances are rebuildable.** `recalculateBalance()` derives the stored
  balance from the ledger, so drift is detectable and repairable.
- **Transfers post to the ledger.** Both legs are written and linked, so a
  transfer survives a rebuild and is excluded from profit & loss.
- **Reversals are contra entries**, so a reversed sale nets to zero rather than
  appearing as a cost.
- **Everything is audited.** The audit trail is driven by domain events, so a
  new write path cannot forget to log.
- **Batches are atomic.** A failed entry rolls the whole batch back.

## If you ever do need real double-entry

Only worth doing if the audience changes from owners to bookkeepers. It is a
different data model, not a feature flag. The shape would be:

1. `ac_journal_entries` (date, reference, description, posted_at)
2. `ac_journal_lines` (entry_id, ledger_account_id, debit, credit) with a
   database or application constraint that `Σ debit = Σ credit` per entry
3. `ac_ledger_accounts` with a `type` (asset / liability / income / expense /
   equity) and a normal balance side
4. Balances derived from lines rather than stored, or stored per period
5. Fiscal periods with a close that locks postings

The current cash-book tables could then become a posting *source* that
generates journal entries. That is a significant piece of work and a breaking
change to the data model, and it would make the product harder to use for the
people it is aimed at — which is exactly why it is not on the roadmap.
