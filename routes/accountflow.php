<?php

use ArtflowStudio\AccountFlow\Livewire\Accounts\AccountsList;
use ArtflowStudio\AccountFlow\Livewire\Accounts\CreateAccount;
use ArtflowStudio\AccountFlow\Livewire\AccountsDashboard;
use ArtflowStudio\AccountFlow\Livewire\AccountsReport;
use ArtflowStudio\AccountFlow\Livewire\Assets\AssetsList;
use ArtflowStudio\AccountFlow\Livewire\Assets\AssetTransactions;
use ArtflowStudio\AccountFlow\Livewire\Assets\CreateAsset;
use ArtflowStudio\AccountFlow\Livewire\Assets\CreateAssetTransaction;
use ArtflowStudio\AccountFlow\Livewire\AuditTrail\AuditTrailList;
use ArtflowStudio\AccountFlow\Livewire\Budgets\BudgetsList;
use ArtflowStudio\AccountFlow\Livewire\Budgets\CreateBudget;
use ArtflowStudio\AccountFlow\Livewire\Categories\CategoriesList;
use ArtflowStudio\AccountFlow\Livewire\Categories\CreateCategory;
use ArtflowStudio\AccountFlow\Livewire\Equity\CreateEquityPartner;
use ArtflowStudio\AccountFlow\Livewire\Equity\EquityPartnersList;
use ArtflowStudio\AccountFlow\Livewire\Equity\EquityTransactionsList;
use ArtflowStudio\AccountFlow\Livewire\Loans\CreateLoan;
use ArtflowStudio\AccountFlow\Livewire\Loans\CreateLoanPartner;
use ArtflowStudio\AccountFlow\Livewire\Loans\LoansList;
use ArtflowStudio\AccountFlow\Livewire\Loans\LoansPartnersList;
use ArtflowStudio\AccountFlow\Livewire\PaymentMethod\CreatePaymentMethod;
use ArtflowStudio\AccountFlow\Livewire\PaymentMethod\PaymentMethods;
use ArtflowStudio\AccountFlow\Livewire\PlannedPayments\CreatePlannedPayment;
use ArtflowStudio\AccountFlow\Livewire\PlannedPayments\PlannedPaymentsList;
use ArtflowStudio\AccountFlow\Livewire\Reports\BalanceSheet;
use ArtflowStudio\AccountFlow\Livewire\Reports\Cashbook;
use ArtflowStudio\AccountFlow\Livewire\Reports\ProfitLoss;
use ArtflowStudio\AccountFlow\Livewire\Reports\TrialBalance;
use ArtflowStudio\AccountFlow\Livewire\Settings as AccountsSettings;
use ArtflowStudio\AccountFlow\Livewire\Transactions\CreateTransaction;
use ArtflowStudio\AccountFlow\Livewire\Transactions\CreateTransactionMultiple;
use ArtflowStudio\AccountFlow\Livewire\Transactions\CreateTransactionTemplate;
use ArtflowStudio\AccountFlow\Livewire\Transactions\Transactions;
use ArtflowStudio\AccountFlow\Livewire\Transactions\TransactionTemplate;
use ArtflowStudio\AccountFlow\Livewire\Transfers\CreateTransfer;
use ArtflowStudio\AccountFlow\Livewire\Transfers\TransfersList;
use ArtflowStudio\AccountFlow\Livewire\Wallets\CreateUserWalletTransfers;
use ArtflowStudio\AccountFlow\Livewire\Wallets\UserWalletsList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AccountFlow routes
|--------------------------------------------------------------------------
|
| Two independent guards apply:
|
|   accountflow.feature:<key>   is the module switched on for this install?
|   accountflow.can:<ability>   may this user do it?
|
| Route middleware only guards the initial page load. A Livewire action
| arrives over `livewire/update` and never passes through it, so components
| authorize again via the AuthorizesAccountFlow trait.
*/

Route::middleware(config('accountflow.middlewares', ['web', 'auth']))->group(function () {
    Route::prefix(config('accountflow.route_prefix', 'accounts'))
        ->name('accountflow::')
        ->group(function () {

            /* Dashboard */
            Route::get('/dashboard', AccountsDashboard::class)
                ->middleware('accountflow.can:view-dashboard')
                ->name('dashboard');

            /* Settings */
            Route::middleware(['accountflow.admin', 'accountflow.can:manage-settings'])->group(function () {
                Route::get('/settings', AccountsSettings::class)->name('settings');
            });

            /* Accounts */
            Route::get('/', AccountsList::class)
                ->middleware('accountflow.can:view-accounts')
                ->name('accounts');
            Route::get('/create', CreateAccount::class)
                ->middleware('accountflow.can:manage-accounts')
                ->name('accounts.create');

            /* Transactions */
            Route::get('/transactions', Transactions::class)
                ->middleware('accountflow.can:view-transactions')
                ->name('transactions');

            Route::middleware('accountflow.can:manage-transactions')->group(function () {
                Route::get('/transaction/create', CreateTransaction::class)->name('transaction.create');
                Route::get('/transactions/edit/{id}', CreateTransaction::class)->name('transactions.edit');
                Route::get('/transactions/create/multiple', CreateTransactionMultiple::class)->name('transactions.create');
            });

            /* Transaction templates */
            Route::middleware('accountflow.feature:templates')->group(function () {
                Route::get('/transactions/templates', TransactionTemplate::class)
                    ->middleware('accountflow.can:view-templates')
                    ->name('transactions.templates');
                Route::get('/transactions/templates/create', CreateTransactionTemplate::class)
                    ->middleware('accountflow.can:manage-templates')
                    ->name('transactions.templates.create');
            });

            /* Budgets */
            Route::middleware('accountflow.feature:budgets')->group(function () {
                Route::get('/budgets', BudgetsList::class)
                    ->middleware('accountflow.can:view-budgets')
                    ->name('budgets');
                Route::get('/budgets/create', CreateBudget::class)
                    ->middleware('accountflow.can:manage-budgets')
                    ->name('budgets.create');
            });

            /* Audit trail */
            Route::middleware(['accountflow.feature:audit', 'accountflow.can:view-audit-trail'])->group(function () {
                Route::get('/audit-trail', AuditTrailList::class)->name('audittrail');
            });

            /* Equity */
            Route::middleware('accountflow.feature:equity')->group(function () {
                Route::get('/equity/partners', EquityPartnersList::class)
                    ->middleware('accountflow.can:view-equity')
                    ->name('equity.partners');
                Route::get('/equity/transactions', EquityTransactionsList::class)
                    ->middleware('accountflow.can:view-equity')
                    ->name('equity.transactions');

                Route::middleware('accountflow.can:manage-equity')->group(function () {
                    Route::get('/equity/partners/create', CreateEquityPartner::class)->name('equity.partners.create');
                    Route::get('/equity/partners/edit/{id}', CreateEquityPartner::class)->name('equity.partners.edit');
                });
            });

            /* Assets */
            Route::middleware('accountflow.feature:assets')->group(function () {
                Route::middleware('accountflow.can:view-assets')->group(function () {
                    Route::get('/assets', AssetsList::class)->name('assets');
                    Route::get('/assets/transactions', AssetTransactions::class)->name('assets.transactions');
                });

                Route::middleware('accountflow.can:manage-assets')->group(function () {
                    Route::get('/assets/create', CreateAsset::class)->name('assets.create');
                    Route::get('/assets/edit/{id}', CreateAsset::class)->name('assets.edit');
                    Route::get('/assets/transactions/create', CreateAssetTransaction::class)->name('assets.transactions.create');
                    Route::get('/assets/transactions/edit/{id}', CreateAssetTransaction::class)->name('assets.transactions.edit');
                });
            });

            /* Categories */
            Route::middleware('accountflow.feature:categories')->group(function () {
                Route::get('/categories', CategoriesList::class)
                    ->middleware('accountflow.can:view-categories')
                    ->name('categories');

                Route::middleware('accountflow.can:manage-categories')->group(function () {
                    Route::get('/categories/create', CreateCategory::class)->name('categories.create');
                    Route::get('/categories/edit/{id}', CreateCategory::class)->name('categories.edit');
                });
            });

            /* User wallets */
            Route::middleware('accountflow.feature:user_wallet_module')->group(function () {
                Route::get('/users-wallets/list', UserWalletsList::class)
                    ->middleware('accountflow.can:view-wallets')
                    ->name('users.wallets');
                Route::get('/users-wallets/transfers/create', CreateUserWalletTransfers::class)
                    ->middleware('accountflow.can:manage-wallets')
                    ->name('users.wallets.create');
            });

            /* Transfers */
            Route::middleware('accountflow.feature:transfers')->group(function () {
                Route::get('/transfers', TransfersList::class)
                    ->middleware('accountflow.can:view-transfers')
                    ->name('transfers.list');

                Route::middleware('accountflow.can:manage-transfers')->group(function () {
                    Route::get('/transfers/create', CreateTransfer::class)->name('transfers.create');
                    Route::get('/transfers/edit/{id}', CreateTransfer::class)->name('transfers.edit');
                });
            });

            /* Payment methods */
            Route::middleware('accountflow.feature:payment_methods')->group(function () {
                Route::get('/payment-methods', PaymentMethods::class)
                    ->middleware('accountflow.can:view-payment-methods')
                    ->name('payment-methods');
                Route::get('/payment-methods/create', CreatePaymentMethod::class)
                    ->middleware('accountflow.can:manage-payment-methods')
                    ->name('payment-methods.create');
            });

            /* Planned payments */
            Route::middleware('accountflow.feature:planned_payments')->group(function () {
                Route::get('/planned-payments', PlannedPaymentsList::class)
                    ->middleware('accountflow.can:view-planned-payments')
                    ->name('planned-payments');

                Route::middleware('accountflow.can:manage-planned-payments')->group(function () {
                    Route::get('/planned-payments/create', CreatePlannedPayment::class)->name('planned-payments.create');
                    Route::get('/planned-payments/edit/{id}', CreatePlannedPayment::class)->name('planned-payments.edit');
                });
            });

            /* Loans */
            Route::middleware('accountflow.feature:loans')->group(function () {
                Route::middleware('accountflow.can:view-loans')->group(function () {
                    Route::get('/loans', LoansList::class)->name('loans');
                    Route::get('/loans/partners', LoansPartnersList::class)->name('loans.partners');
                });

                Route::middleware('accountflow.can:manage-loans')->group(function () {
                    Route::get('/loans/create', CreateLoan::class)->name('loans.create');
                    Route::get('/loans/edit/{id}', CreateLoan::class)->name('loans.edit');
                    Route::get('/loans/partners/create', CreateLoanPartner::class)->name('loans.partners.create');
                    Route::get('/loans/partners/edit/{id}', CreateLoanPartner::class)->name('loans.partners.edit');
                });
            });

            /* Reports */
            Route::middleware('accountflow.can:view-reports')->group(function () {
                Route::get('/report', AccountsReport::class)->name('report');
                Route::get('/report/balance-sheet', BalanceSheet::class)->name('report.balance-sheet');

                Route::get('/report/profit-and-loss', ProfitLoss::class)
                    ->middleware('accountflow.feature:profit_loss')
                    ->name('report.profitLoss');

                Route::get('/report/trial-balance', TrialBalance::class)
                    ->middleware('accountflow.feature:trial_balance')
                    ->name('report.trial-balance');

                Route::get('/report/cashbook', Cashbook::class)
                    ->middleware('accountflow.feature:cashbook')
                    ->name('report.cashbook');
            });
        });
});
