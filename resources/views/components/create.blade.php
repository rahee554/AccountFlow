{{--
    AccountFlow Embed Create Component
    ===================================
    Companion to <x-accountflow::table>. Mounts the matching "Create" form in
    standalone mode (no layout wrapper, no nav header) so you can drop a
    ready-made add-record form into any Blade/Livewire view.

    Usage:
        <x-accountflow::create table="transactions" />
        <x-accountflow::create table="accounts" />
        <x-accountflow::create table="categories" />
        <x-accountflow::create table="payment-methods" />

    To edit an existing record instead of creating one, pass its id:
        <x-accountflow::create table="accounts" :id="$account->id" />
--}}
@props(['table' => 'transactions', 'id' => null])

@php
    $createMap = [
        'transactions'     => \ArtflowStudio\AccountFlow\Livewire\Transactions\CreateTransaction::class,
        'accounts'         => \ArtflowStudio\AccountFlow\Livewire\Accounts\CreateAccount::class,
        'categories'       => \ArtflowStudio\AccountFlow\Livewire\Categories\CreateCategory::class,
        'payment-methods'  => \ArtflowStudio\AccountFlow\Livewire\PaymentMethod\CreatePaymentMethod::class,
    ];

    $componentClass = $createMap[$table] ?? null;
@endphp

@if ($componentClass)
    @livewire($componentClass, array_filter(['standalone' => true, 'id' => $id]))
@else
    <div class="alert alert-warning">
        AccountFlow: unknown create key <strong>{{ $table }}</strong>.
        Available keys: {{ implode(', ', array_keys($createMap)) }}
    </div>
@endif
