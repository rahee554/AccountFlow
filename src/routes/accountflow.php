<?php

use App\Livewire\Accountflow\Accounts\AccountsList;
use App\Livewire\Accountflow\Accounts\CreateAccount;
use App\Livewire\Accountflow\AccountsDashboard;
use App\Livewire\Accountflow\AccountsReport;
use App\Livewire\Accountflow\Assets\AssetsList;
use App\Livewire\Accountflow\Assets\AssetTransactions;
use App\Livewire\Accountflow\Assets\CreateAsset;
use App\Livewire\Accountflow\Assets\CreateAssetTransaction;
use App\Livewire\Accountflow\AuditTrail\AuditTrailList;
use App\Livewire\Accountflow\Budgets\BudgetsList;
use App\Livewire\Accountflow\Budgets\CreateBudget;
use App\Livewire\Accountflow\Categories\CategoriesList;
use App\Livewire\Accountflow\Categories\CreateCategory;
use App\Livewire\Accountflow\Equity\CreateEquityPartner;
use App\Livewire\Accountflow\Equity\EquityPartnersList;
use App\Livewire\Accountflow\Equity\EquityTransactionsList;
use App\Livewire\Accountflow\Loans\CreateLoan;
use App\Livewire\Accountflow\Loans\CreateLoanPartner;
use App\Livewire\Accountflow\Loans\LoansList;
use App\Livewire\Accountflow\Loans\LoansPartnersList;
use App\Livewire\Accountflow\PaymentMethod\CreatePaymentMethod;
use App\Livewire\Accountflow\PaymentMethod\PaymentMethods;
use App\Livewire\Accountflow\PlannedPayments\CreatePlannedPayment;
use App\Livewire\Accountflow\PlannedPayments\PlannedPaymentsList;
use App\Livewire\Accountflow\Reports\Cashbook;
use App\Livewire\Accountflow\Reports\ProfitLoss;
use App\Livewire\Accountflow\Reports\TrialBalance;
use App\Livewire\Accountflow\Settings as AccountsSettings;
use App\Livewire\Accountflow\Transactions\CreateTransaction;
use App\Livewire\Accountflow\Transactions\CreateTransactionMultiple;
use App\Livewire\Accountflow\Transactions\CreateTransactionTemplate;
use App\Livewire\Accountflow\Transactions\Transactions;
use App\Livewire\Accountflow\Transactions\TransactionTemplate;
use App\Livewire\Accountflow\Transfers\CreateTransfer;
use App\Livewire\Accountflow\Transfers\TransfersList;
use App\Livewire\Accountflow\Wallets\CreateUserWalletTransfers;
use App\Livewire\Accountflow\Wallets\UserWalletsList;
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
