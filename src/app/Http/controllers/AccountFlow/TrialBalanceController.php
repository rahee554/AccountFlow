<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Account;    

class TrialBalanceController extends Controller
{
    public function index(){

         // Fetch all accounts with their transactions
         $accounts = Account::with('transactions')->get();

         // Initialize arrays for debits and credits
         $trialBalance = [];
 
         foreach ($accounts as $account) {
             $debitTotal = 0;
             $creditTotal = 0;
 
             // Calculate total debits and credits for each account
             foreach ($account->transactions as $transaction) {
                 if ($transaction->type == 1) { // Income
                     $creditTotal += $transaction->amount;
                 } else { // Expense
                     $debitTotal += $transaction->amount;
                 }
             }
 
             // Calculate the balance for the account
             $balance = $creditTotal - $debitTotal;
 
             // Store the data in the trial balance array
             $trialBalance[] = [
                 'account' => $account->name,
                 'debit_total' => $debitTotal,
                 'credit_total' => $creditTotal,
                 'balance' => $balance
             ];
         }

        return view(config('accountflow.view_path') . 'trialbalance',compact('trialBalance'));
    }
}
