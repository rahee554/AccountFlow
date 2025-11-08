<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountsController extends Controller
{
    public static function updateAccountBalance()
    {
        $accounts = Account::all();
        $filterColumn = config('accountflow.filter_column');
        $userId = Auth::id();

        foreach ($accounts as $account) {
            // Get the value of the filter column for the current account
            $filtered = $account->$filterColumn;

            // Sum income transactions
            $incomeSum = Transaction::where('account_id', $account->id)
                ->where('type', 1) // Type 1 for income
                ->where($filterColumn, $filtered)
                ->sum('amount');

            // Sum expense transactions
            $expenseSum = Transaction::where('account_id', $account->id)
                ->where('type', 2) // Type 2 for expense
                ->where($filterColumn, $filtered)
                ->sum('amount');

            // Sum transfers
            $transferFromSum = Transfer::where('from_account', $account->id)->sum('amount');
            $transferToSum = Transfer::where('to_account', $account->id)->sum('amount');

            // Calculate balance: Income + Transfers received - (Expenses + Transfers sent)
            $account->balance = $incomeSum + $transferToSum - ($expenseSum + $transferFromSum);
            $account->save();
        }
    }

    // Static function to add value to account balance
    public static function addToAccount($accountId, $value)
    {
        $account = Account::find($accountId); // Fetch the account by ID

        if ($account) {
            $account->balance += $value; // Add the value to the current balance
            $account->save(); // Save the updated balance
        }
    }

    // Static function to subtract value from account balance
    public static function subtractFromAccount($accountId, $value)
    {
        $account = Account::find($accountId); // Fetch the account by ID

        if ($account) {
            $account->balance -= $value; // Subtract the value from the current balance
            $account->save(); // Save the updated balance
        }
    }
}

