{{--
    Sidebar + nav data. The navigation is a plain PHP array, built here from
    real accountflow::* route names and gated by real feature flags — not a
    config file (see ui-flow's own laravel-integration.md §5: a menu array
    belongs in the view that renders it, not behind a config-cache layer it
    never needs).

    A leaf has a `route` (a Laravel route name); a group has `children`
    instead. Feature-gated sections are filtered out entirely (not just
    hidden) when the module is off, via Accountflow::features()->isEnabled().
--}}
@php
    use ArtflowStudio\AccountFlow\Facades\Accountflow;

    $ufFeature = fn (string $key) => Accountflow::features()->isEnabled($key);

    $menu ??= array_values(array_filter([

        [
            'key' => 'overview',
            'label' => 'Overview',
            'icon' => 'gauge',
            'items' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'accountflow::dashboard'],
            ],
        ],

        [
            'key' => 'accounts',
            'label' => 'Accounts',
            'icon' => 'landmark',
            'items' => array_values(array_filter([
                [
                    'key' => 'accounts',
                    'label' => 'Accounts',
                    'icon' => 'landmark',
                    'route' => 'accountflow::accounts',
                    'active' => 'accountflow::accounts*',
                ],
                $ufFeature('categories') ? [
                    'key' => 'categories',
                    'label' => 'Categories',
                    'icon' => 'tag',
                    'route' => 'accountflow::categories',
                    'active' => 'accountflow::categories*',
                ] : null,
                $ufFeature('payment_methods') ? [
                    'key' => 'payment-methods',
                    'label' => 'Payment Methods',
                    'icon' => 'credit-card',
                    'route' => 'accountflow::payment-methods',
                    'active' => 'accountflow::payment-methods*',
                ] : null,
                $ufFeature('transfers') ? [
                    'key' => 'transfers',
                    'label' => 'Transfers',
                    'icon' => 'repeat',
                    'route' => 'accountflow::transfers.list',
                    'active' => 'accountflow::transfers*',
                ] : null,
            ])),
        ],

        [
            'key' => 'transactions',
            'label' => 'Transactions',
            'icon' => 'receipt',
            'items' => array_values(array_filter([
                [
                    'key' => 'transactions',
                    'label' => 'All Transactions',
                    'icon' => 'list',
                    'route' => 'accountflow::transactions',
                    'active' => 'accountflow::transactions',
                ],
                [
                    'key' => 'transaction-add',
                    'label' => 'Add Transaction',
                    'icon' => 'plus',
                    'route' => 'accountflow::transaction.create',
                ],
                $ufFeature('templates') ? [
                    'key' => 'transaction-templates',
                    'label' => 'Templates',
                    'icon' => 'copy',
                    'route' => 'accountflow::transactions.templates',
                    'active' => 'accountflow::transactions.templates*',
                ] : null,
            ])),
        ],

        $ufFeature('budgets') || $ufFeature('planned_payments') ? [
            'key' => 'planning',
            'label' => 'Planning',
            'icon' => 'calendar-clock',
            'items' => array_values(array_filter([
                $ufFeature('budgets') ? [
                    'key' => 'budgets',
                    'label' => 'Budgets',
                    'icon' => 'chart-pie',
                    'route' => 'accountflow::budgets',
                    'active' => 'accountflow::budgets*',
                ] : null,
                $ufFeature('planned_payments') ? [
                    'key' => 'planned-payments',
                    'label' => 'Planned Payments',
                    'icon' => 'calendar-clock',
                    'route' => 'accountflow::planned-payments',
                    'active' => 'accountflow::planned-payments*',
                ] : null,
            ])),
        ] : null,

        [
            'key' => 'reports',
            'label' => 'Reports',
            'icon' => 'chart-column',
            'items' => array_values(array_filter([
                ['key' => 'report-summary', 'label' => 'Summary', 'icon' => 'chart-column', 'route' => 'accountflow::report'],
                ['key' => 'balance-sheet', 'label' => 'Balance Sheet', 'icon' => 'scale', 'route' => 'accountflow::report.balance-sheet'],
                $ufFeature('profit_loss') ? ['key' => 'profit-loss', 'label' => 'Profit & Loss', 'icon' => 'trending-up', 'route' => 'accountflow::report.profitLoss'] : null,
                $ufFeature('trial_balance') ? ['key' => 'trial-balance', 'label' => 'Trial Balance', 'icon' => 'columns-3', 'route' => 'accountflow::report.trial-balance'] : null,
                $ufFeature('cashbook') ? ['key' => 'cashbook', 'label' => 'Cashbook', 'icon' => 'book-open', 'route' => 'accountflow::report.cashbook'] : null,
            ])),
        ],

        $ufFeature('assets') || $ufFeature('equity') || $ufFeature('loans') ? [
            'key' => 'other-ledgers',
            'label' => 'Other Ledgers',
            'icon' => 'layers',
            'items' => array_values(array_filter([
                $ufFeature('assets') ? [
                    'key' => 'assets',
                    'label' => 'Assets',
                    'icon' => 'gem',
                    'children' => [
                        ['key' => 'assets-list', 'label' => 'All Assets', 'icon' => 'list', 'route' => 'accountflow::assets'],
                        ['key' => 'assets-transactions', 'label' => 'Asset Transactions', 'icon' => 'receipt', 'route' => 'accountflow::assets.transactions'],
                    ],
                ] : null,
                $ufFeature('equity') ? [
                    'key' => 'equity',
                    'label' => 'Equity',
                    'icon' => 'users',
                    'children' => [
                        ['key' => 'equity-partners', 'label' => 'Partners', 'icon' => 'user', 'route' => 'accountflow::equity.partners'],
                        ['key' => 'equity-transactions', 'label' => 'Transactions', 'icon' => 'receipt', 'route' => 'accountflow::equity.transactions'],
                    ],
                ] : null,
                $ufFeature('loans') ? [
                    'key' => 'loans',
                    'label' => 'Loans & Credits',
                    'icon' => 'coins',
                    'children' => [
                        ['key' => 'loans-list', 'label' => 'Overview', 'icon' => 'list', 'route' => 'accountflow::loans'],
                        ['key' => 'loans-partners', 'label' => 'Loan Partners', 'icon' => 'user', 'route' => 'accountflow::loans.partners'],
                    ],
                ] : null,
            ])),
        ] : null,

        [
            'key' => 'admin',
            'label' => 'Admin',
            'icon' => 'shield',
            'items' => array_values(array_filter([
                $ufFeature('audit') ? [
                    'key' => 'audit-trail',
                    'label' => 'Audit Trail',
                    'icon' => 'shield-check',
                    'route' => 'accountflow::audittrail',
                ] : null,
                [
                    'key' => 'settings',
                    'label' => 'Settings',
                    'icon' => 'settings',
                    'route' => 'accountflow::settings',
                ],
            ])),
        ],

    ]));

    $sections = $menu;
@endphp

@include(config('accountflow.view_path') . 'layout.sidebar')
@include(config('accountflow.view_path') . 'layout.menubar')
