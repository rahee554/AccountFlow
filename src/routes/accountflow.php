<?php


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;



use App\Http\Controllers\AccountFlow\AccountsController;
use App\Http\Controllers\AccountFlow\PurchasesController;
use App\Http\Controllers\AccountFlow\AssetsController;
use App\Http\Controllers\AccountFlow\LoansController;
use App\Http\Controllers\AccountFlow\AccountsCategoriesController;
use App\Http\Controllers\AccountFlow\CashbookController;
use App\Http\Controllers\AccountFlow\IncomeStatementController;
use App\Http\Controllers\AccountFlow\TransactionsController;
use App\Http\Controllers\AccountFlow\RecordsController;
use App\Http\Controllers\AccountFlow\TrialBalanceController;
use App\Http\Controllers\AccountFlow\UserWalletsController;
use App\Http\Controllers\AccountFlow\LedgerController;
use App\Http\Controllers\AccountFlow\TransfersController;

/** ::::::::::::::::::::::: Accounts & Finance ::::::::::::::::::::::: **/


Route::group(['middleware' => Config::get('accountflow.middlewares')], function () {

    Route::prefix('accounts')->group(function () {
        /** ::::::::::::::::::::::: Accounts ::::::::::::::::::::::: **/
        Route::get('/', [AccountsController::class, 'index'])->name('accounts.list');
        /** ::::::::::::::::::::::: Transactions ::::::::::::::::::::::: **/
        Route::post('/getList', [AccountsController::class, 'getList'])->name('accounts.get.list');
        Route::get('/transactions', [TransactionsController::class, 'index'])->name('accounts.transactions');
        // route for storing the transaction form income tab data
        //Route::post('save-transactions-income',[TransactionsController::class, 'TransactionIncome'])->name('accounts.save-transaction-income');
        Route::post('/add-income', [TransactionsController::class, 'addIncome'])->name('accounts.add.income');
        Route::post('/add-expense', [TransactionsController::class, 'addExense'])->name('accounts.add.expense');
        Route::post('/add-purchase', [TransactionsController::class, 'addPurchase'])->name('accounts.add.purchase');
        Route::post('/add-loan', [TransactionsController::class, 'addLoan'])->name('accounts.add.loan');

        /** ::::::::::::::::::::::: Assets ::::::::::::::::::::::: **/
        Route::get('/assets', [AssetsController::class, 'index'])->name('accounts.assets');
        Route::get('/assets-transactions', [AssetsController::class, 'assetTrxView'])->name('accounts.assets.trx');
        Route::post('/creae-asset', [AssetsController::class, 'storeAsset'])->name('accounts.assets.create');
        Route::post('/getAssets', [AssetsController::class, 'getAssets'])->name('get.assets.list');
        Route::post('/getAssetsTrx', [AssetsController::class, 'getAssetsTrx'])->name('get.assets.trx');
        /** ::::::::::::::::::::::: Purchases ::::::::::::::::::::::: **/
        Route::get('/purchases', [PurchasesController::class, 'index'])->name('accounts.purchases');
        Route::post('/getPurchases', [PurchasesController::class, 'getPurchasesList'])->name('get.purchases.list');
        /** ::::::::::::::::::::::: Loans ::::::::::::::::::::::: **/
        Route::get('/loans', [LoansController::class, 'index'])->name('accounts.loans');
        Route::post('/getLoans', [LoansController::class, 'getLoansList'])->name('get.loans.list');
        /** ::::::::::::::::::::::: Categories ::::::::::::::::::::::: **/
        Route::get('/categories', [AccountsCategoriesController::class, 'index'])->name('accounts.categories');
        Route::post('/get-categories', [AccountsCategoriesController::class, 'getCategories'])->name('get.accounts.categories');

        /** ::::::::::::::::::::::: Reports ::::::::::::::::::::::: **/
        Route::get('/cashbook', [CashbookController::class, 'index'])->name('accounts.cashbook');
        Route::post('/getCashbookData', [CashbookController::class, 'getCashbookData'])->name('accounts.cashbook.getData');
        Route::get('/incomestatement', [IncomeStatementController::class, 'index'])->name('accounts.incomestatement');
        Route::get('/ledgers', [LedgerController::class, 'index'])->name('accounts.ledgers');
        Route::post('/getLedgerData', [LedgerController::class, 'getLedgerData'])->name('accounts.ledgers.getdata');
        Route::get('/trialbalance', [TrialBalanceController::class, 'index'])->name('accounts.trialbalance');


        /** ::::::::::::::::::::::: Transactions ::::::::::::::::::::::: **/

        Route::post('/add-transaction', [TransactionsController::class, 'add'])->name('accounts.add.transaction');
        Route::post('/edit-transaction', [TransactionsController::class, 'edit'])->name('accounts.edit.transaction');
        Route::post('/update-transaction', [TransactionsController::class, 'update'])->name('accounts.update.transaction');
        Route::post('/delete-transaction', [TransactionsController::class, 'deete'])->name('accounts.delete.transaction');
        Route::post('/getTransactionsData', [TransactionsController::class, 'getTransactionsData'])->name('accounts.get.trx');

        /** ::::::::::::::::::::::: User Wallets ::::::::::::::::::::::: **/
        Route::get('/user-wallets', [UserWalletsController::class, 'index'])->name('accounts.userwallets');
        Route::post('/getUserWallets', [UserWalletsController::class, 'getWallets'])->name('get.userwallets');

        /** ::::::::::::::::::::::: Transfers ::::::::::::::::::::::: **/
        Route::post('/add-transfer', [TransfersController::class, 'addTransfer'])->name('accounts.add.transfer');

        Route::post('/getTransfersList', [TransfersController::class, 'getTransfersList'])->name('get.transfers.list');
    });
});
