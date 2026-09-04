{{--
    AccountFlow Embed Table Component
    ===================================
    The canonical column definitions live inside each Livewire list component.
    This component is only a thin dispatcher — it mounts the right component in
    "standalone" mode (no layout wrapper, no nav header, table only).

    Usage — anonymous Blade component:
        <x-accountflow::table table="transactions" />
        <x-accountflow::table table="accounts" />
        <x-accountflow::table table="transfers" />
        <x-accountflow::table table="categories" />
        <x-accountflow::table table="planned-payments" />
        <x-accountflow::table table="assets" />
        <x-accountflow::table table="asset-transactions" />
        <x-accountflow::table table="equity-transactions" />
        <x-accountflow::table table="equity-partners" />

    Usage — Blade directive (identical output):
        @accountflow(['table' => 'transactions'])
        @accountflow(['table' => 'accounts'])

    The "standalone" prop suppresses the page layout and nav header so only
    the AFtable is rendered — ideal for embedding inside any Blade/Livewire view.
--}}
@props(['table' => 'transactions'])

@php
    $tableMap = [
        'transactions'       => 'account-flow.transactions.transactions',
        'accounts'           => 'account-flow.accounts.accounts-list',
        'transfers'          => 'account-flow.transfers.transfers-list',
        'categories'         => 'account-flow.categories.categories-list',
        'planned-payments'   => 'account-flow.planned-payments.planned-payments-list',
        'assets'             => 'account-flow.assets.assets-list',
        'asset-transactions' => 'account-flow.assets.asset-transactions',
        'equity-transactions'=> 'account-flow.equity.equity-transactions-list',
        'equity-partners'    => 'account-flow.equity.equity-partners-list',
        'loans'              => 'account-flow.loans.loans-list',
        'budgets'            => 'account-flow.budgets.budgets-list',
    ];

    $componentName = $tableMap[$table] ?? null;
@endphp

@if ($componentName)
    @livewire($componentName, ['standalone' => true])
@else
    <div class="alert alert-warning">
        AccountFlow: unknown table key <strong>{{ $table }}</strong>.
        Available keys: {{ implode(', ', array_keys($tableMap)) }}
    </div>
@endif
