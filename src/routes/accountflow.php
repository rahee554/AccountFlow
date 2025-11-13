<?php

use App\Livewire\AccountFlow\Accounts\AccountsList;
use App\Livewire\AccountFlow\Accounts\CreateAccount;
use App\Livewire\AccountFlow\AccountsDashboard;
use App\Livewire\AccountFlow\AccountsReport;
use App\Livewire\AccountFlow\Assets\AssetsList;
use App\Livewire\AccountFlow\Assets\AssetTransactions;
use App\Livewire\AccountFlow\Assets\CreateAsset;
use App\Livewire\AccountFlow\Assets\CreateAssetTransaction;
use App\Livewire\AccountFlow\AuditTrail\AuditTrailList;
use App\Livewire\AccountFlow\Budgets\BudgetsList;
use App\Livewire\AccountFlow\Budgets\CreateBudget;
use App\Livewire\AccountFlow\Categories\CategoriesList;
use App\Livewire\AccountFlow\Categories\CreateCategory;
use App\Livewire\AccountFlow\Equity\CreateEquityPartner;
use App\Livewire\AccountFlow\Equity\EquityPartnersList;
use App\Livewire\AccountFlow\Equity\EquityTransactionsList;
use App\Livewire\AccountFlow\Loans\CreateLoan;
use App\Livewire\AccountFlow\Loans\CreateLoanPartner;
use App\Livewire\AccountFlow\Loans\LoansList;
use App\Livewire\AccountFlow\Loans\LoansPartnersList;
use App\Livewire\AccountFlow\PaymentMethod\CreatePaymentMethod;
use App\Livewire\AccountFlow\PaymentMethod\PaymentMethods;
use App\Livewire\AccountFlow\PlannedPayments\CreatePlannedPayment;
use App\Livewire\AccountFlow\PlannedPayments\PlannedPaymentsList;
use App\Livewire\AccountFlow\Reports\Cashbook;
use App\Livewire\AccountFlow\Reports\ProfitLoss;
use App\Livewire\AccountFlow\Reports\TrialBalance;
use App\Livewire\AccountFlow\Settings as AccountsSettings;
use App\Livewire\AccountFlow\Transactions\CreateTransaction;
use App\Livewire\AccountFlow\Transactions\CreateTransactionMultiple;
use App\Livewire\AccountFlow\Transactions\CreateTransactionTemplate;
use App\Livewire\AccountFlow\Transactions\Transactions;
use App\Livewire\AccountFlow\Transactions\TransactionTemplate;
use App\Livewire\AccountFlow\Transfers\CreateTransfer;
use App\Livewire\AccountFlow\Transfers\TransfersList;
use App\Livewire\AccountFlow\Wallets\CreateUserWalletTransfers;
use App\Livewire\AccountFlow\Wallets\UserWalletsList;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/** ::::::::::::::::::::::: Accounts & Finance ::::::::::::::::::::::: **/
Route::group(['middleware' => Config::get('accountflow.middlewares')], function () {

    Route::prefix(Config::get('accountflow.route_prefix', 'accounts'))->name('accountflow::')->group(function () {

        // ** ::::::::::::::::::::::: Dashboard ::::::::::::::::::::::: **/
        Route::get('/dashboard', AccountsDashboard::class)->name('dashboard');

        // ** ::::::::::::::::::::::: Settings ::::::::::::::::::::::: **/
        Route::get('/settings', AccountsSettings::class)->name('settings');

        // ** ::::::::::::::::::::::: Budgets ::::::::::::::::::::::: **/
        Route::get('/budgets', BudgetsList::class)->name('budgets');
        Route::get('/budgets/create', CreateBudget::class)->name('budgets.create');

        // ** ::::::::::::::::::::::: Audit Trail ::::::::::::::::::::::: **/
        Route::get('/audit-trail', AuditTrailList::class)->name('audittrail');

        // ** ::::::::::::::::::::::: Equity ::::::::::::::::::::::: **/

        Route::get('/equity/partners', EquityPartnersList::class)->name('equity.partners');
        Route::get('/equity/partners/create', CreateEquityPartner::class)->name('equity.partners.create');
        Route::get('/equity/partners/edit/{id}', CreateEquityPartner::class)->name('equity.partners.edit');
        Route::get('/equity/transactions', EquityTransactionsList::class)->name('equity.transactions');

        // ** ::::::::::::::::::::::: Assets ::::::::::::::::::::::: **/
        Route::get('/assets', AssetsList::class)->name('assets');
        Route::get('/assets/create', CreateAsset::class)->name('assets.create');
        Route::get('/assets/edit/{id}', CreateAsset::class)->name('assets.edit');
        Route::get('/assets/transactions', AssetTransactions::class)->name('assets.transactions');
        Route::get('/assets/transactions/create', CreateAssetTransaction::class)->name('assets.transactions.create');
        Route::get('/assets/transactions/edit/{id}', CreateAssetTransaction::class)->name('assets.transactions.edit');

        // ** ::::::::::::::::::::::: Categories ::::::::::::::::::::::: **/

        Route::get('/categories', CategoriesList::class)->name('categories');
        Route::get('/categories/create', CreateCategory::class)->name('categories.create');
        Route::get('/categories/edit/{id}', CreateCategory::class)->name('categories.edit');

        // ** ::::::::::::::::::::::: Accounts ::::::::::::::::::::::: **/
        Route::get('/', AccountsList::class)->name('accounts');
        Route::get('/create', CreateAccount::class)->name('accounts.create');

        // ** ::::::::::::::::::::::: Users Wallet ::::::::::::::::::::::: **/
        Route::get('/users-wallets/list', UserWalletsList::class)->name('users.wallets');
        Route::get('/users-wallets/transfers', CreateUserWalletTransfers::class)->name('users.wallets.create');
        Route::get('/users-wallets/transfers/create', CreateUserWalletTransfers::class)->name('users.wallets.create');

        // ** ::::::::::::::::::::::: Transfers ::::::::::::::::::::::: **/

        Route::get('/transfers', TransfersList::class)->name('transfers.list');
        Route::get('/transfers/create', CreateTransfer::class)->name('transfers.create');
        Route::get('/transfers/edit/{id}', CreateTransfer::class)->name('transfers.edit');

        // * ::::::::::::::::::::::: Transactions ::::::::::::::::::::::: **/

        Route::get('/transactions', Transactions::class)->name('transactions');
        Route::get('/transaction/create', CreateTransaction::class)->name('transaction.create');
        Route::get('/transactions/edit/{id}', CreateTransaction::class)->name('transactions.edit');
        Route::get('/transactions/create/multiple', CreateTransactionMultiple::class)->name('transactions.create');

        // * ::::::::::::::::::::::: Transactions Template ::::::::::::::::::::::: **/

        Route::get('/transactions/templates', TransactionTemplate::class)->name('transactions.templates');
        Route::get('/transactions/templates/create', CreateTransactionTemplate::class)->name('transactions.templates.create');

        // * ::::::::::::::::::::::: Payment Methods ::::::::::::::::::::::: **/

        Route::get('/payment-methods', PaymentMethods::class)->name('payment-methods');
        Route::get('/payment-methods/create', CreatePaymentMethod::class)->name('payment-methods.create');

        // * ::::::::::::::::::::::: Planned Payment  ::::::::::::::::::::::: **/

        Route::get('/planned-payments', PlannedPaymentsList::class)->name('planned-payments');
        Route::get('/planned-payments/create', CreatePlannedPayment::class)->name('planned-payments.create');
        Route::get('/planned-payments/edit/{id}', CreatePlannedPayment::class)->name('planned-payments.edit');

        // * ::::::::::::::::::::::: Loan Routes  ::::::::::::::::::::::: **/

        Route::get('/loans', LoansList::class)->name('loans');
        Route::get('/loans/create', CreateLoan::class)->name('loans.create');
        Route::get('/loans/edit/{id}', CreateLoan::class)->name('loans.edit');
        Route::get('/loans/partners', LoansPartnersList::class)->name('loans.partners');
        Route::get('/loans/partners/create', CreateLoanPartner::class)->name('loans.partners.create');
        Route::get('/loans/partners/edit/{id}', CreateLoanPartner::class)->name('loans.partners.edit');

        // ** ::::::::::::::::::::::: Transfers ::::::::::::::::::::::: **/

        Route::get('/transfers', TransfersList::class)->name('transfers.list');
        Route::get('/transfers/create', CreateTransfer::class)->name('transfers.create');
        Route::get('/transfers/edit/{id}', CreateTransfer::class)->name('transfers.edit');

        // ** ::::::::::::::::::::::: Reports ::::::::::::::::::::::: **/

        Route::get('/report', AccountsReport::class)->name('report');
        Route::get('/report/profit-and-loss', ProfitLoss::class)->name('report.profitLoss');
        Route::get('/report/trial-balance', TrialBalance::class)->name('report.trial-balance');
        Route::get('/report/cashbook', Cashbook::class)->name('report.cashbook');

    });
});
