<?php

namespace ArtflowStudio\AccountFlow\Support;

use ArtflowStudio\AccountFlow\Models\Setting;

/**
 * AFTable column definitions for the bundled list screens.
 *
 * These lived inline in the Blade views as 30-line arrays with three levels of
 * nested quote escaping, a `config()` lookup per rendered row for the currency
 * symbol, and `$row->category->icon` on a nullable relation — one uncategorised
 * transaction was enough to fatal the page.
 *
 * Every relation column declares `relation`, which is what tells AFTable to
 * eager-load it; without that each row triggers its own query.
 *
 * Usage in a Blade view:
 *
 *   {@literal @}livewire('aftable', [
 *       'model'   => Transaction::class,
 *       'columns' => TableColumns::transactions(),
 *       'vars'    => TableColumns::vars(),
 *   ])
 */
final class TableColumns
{
    /**
     * Values shared with `raw` templates so they are resolved once per table
     * rather than once per row.
     *
     * @return array<string,mixed>
     */
    public static function vars(): array
    {
        return [
            'currencySymbol' => self::currencySymbol(),
            'iconBase' => rtrim((string) config('accountflow.asset_path'), '/').'/icons/accounts_icons/',
        ];
    }

    public static function currencySymbol(): string
    {
        $currency = Setting::currency();

        return (string) config("accountflow.currency_symbols.{$currency}", $currency.' ');
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function transactions(): array
    {
        return [
            [
                'key' => 'date',
                'label' => 'Date',
                'raw' => '{{ $row->date?->format("d M Y") ?? "—" }}',
            ],
            [
                'key' => 'amount',
                'label' => 'Amount',
                'raw' => '<span class="fw-bold {{ $row->type == 1 ? "text-success" : "text-danger" }}">'
                    .'{{ $currencySymbol }}{{ number_format((float) $row->amount, 2) }}</span>',
            ],
            [
                'key' => 'description',
                'label' => 'Description',
            ],
            [
                'key' => 'category_id',
                'label' => 'Category',
                'relation' => 'category:name',
                // Null-safe: category_id is nullable.
                'raw' => '@if($row->category)'
                    .'<span class="d-inline-flex align-items-center">'
                    .'@if($row->category->icon)<img src="{{ asset($iconBase . $row->category->icon) }}" alt="" class="h-25px me-2">@endif'
                    .'{{ $row->category->name }}</span>'
                    .'@else<span class="text-muted">Uncategorised</span>@endif',
            ],
            [
                'key' => 'account_id',
                'label' => 'Account',
                'relation' => 'account:name',
                'raw' => '{{ $row->account?->name ?? "—" }}',
            ],
            [
                'key' => 'payment_method',
                'label' => 'Method',
                'relation' => 'paymentMethod:name',
                'raw' => '{{ $row->paymentMethod?->name ?? "—" }}',
            ],
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function accounts(): array
    {
        return [
            ['key' => 'name', 'label' => 'Account'],
            ['key' => 'description', 'label' => 'Description'],
            [
                'key' => 'opening_balance',
                'label' => 'Opening',
                'raw' => '{{ $currencySymbol }}{{ number_format((float) $row->opening_balance, 2) }}',
            ],
            [
                'key' => 'balance',
                'label' => 'Balance',
                'raw' => '<span class="fw-bold {{ (float) $row->balance < 0 ? "text-danger" : "text-success" }}">'
                    .'{{ $currencySymbol }}{{ number_format((float) $row->balance, 2) }}</span>',
            ],
            [
                'key' => 'active',
                'label' => 'Status',
                'raw' => '<span class="badge badge-light-{{ $row->active ? "success" : "secondary" }}">'
                    .'{{ $row->active ? "Active" : "Inactive" }}</span>',
            ],
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function categories(): array
    {
        return [
            [
                'key' => 'name',
                'label' => 'Category',
                'raw' => '@if($row->icon)<img src="{{ asset($iconBase . $row->icon) }}" alt="" class="h-25px me-2">@endif{{ $row->name }}',
            ],
            [
                'key' => 'type',
                'label' => 'Type',
                'raw' => '<span class="badge badge-light-{{ $row->type == 1 ? "success" : "danger" }}">'
                    .'{{ $row->type == 1 ? "Income" : "Expense" }}</span>',
            ],
            [
                'key' => 'parent_id',
                'label' => 'Parent',
                'relation' => 'parent:name',
                'raw' => '{{ $row->parent?->name ?? "—" }}',
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'raw' => '<span class="badge badge-light-{{ $row->status == 1 ? "success" : "secondary" }}">'
                    .'{{ $row->status == 1 ? "Active" : "Inactive" }}</span>',
            ],
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function transfers(): array
    {
        return [
            ['key' => 'unique_id', 'label' => 'Ref'],
            [
                'key' => 'date',
                'label' => 'Date',
                'raw' => '{{ $row->date ? \Carbon\Carbon::parse($row->date)->format("d M Y") : "—" }}',
            ],
            [
                'key' => 'amount',
                'label' => 'Amount',
                'raw' => '{{ $currencySymbol }}{{ number_format((float) $row->amount, 2) }}',
            ],
            ['key' => 'description', 'label' => 'Description'],
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function auditTrail(): array
    {
        return [
            [
                'key' => 'created_at',
                'label' => 'When',
                'raw' => '{{ $row->created_at?->format("d M Y H:i") ?? "—" }}',
            ],
            ['key' => 'model_type', 'label' => 'Record'],
            ['key' => 'model_id', 'label' => 'ID'],
            [
                'key' => 'action',
                'label' => 'Action',
                'raw' => '<span class="badge badge-light-primary">{{ ucfirst($row->action) }}</span>',
            ],
            [
                'key' => 'user_id',
                'label' => 'User',
                'relation' => 'user:name',
                'raw' => '{{ $row->user?->name ?? "System" }}',
            ],
        ];
    }
}
