<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AccountFlow\Account;
use Yajra\DataTables\DataTables;
class LedgerController extends Controller
{
    public function index(){

          // Fetch all accounts with their transactions
        $accounts = Account::with('transactions')->get();

        // Initialize arrays for ledgers
        $ledgers = [];

        foreach ($accounts as $account) {
            // Initialize the ledger for the account
            $ledgers[$account->id] = [];

            $debitTotal = 0;
            $creditTotal = 0;
            $balance = 0;

            // Calculate debits, credits, and balance for each account
            $transactions = $account->transactions->sortBy('date');
            foreach ($transactions as $transaction) {
                if ($transaction->type == 1) { // Income
                    $creditTotal += $transaction->amount;
                } else { // Expense
                    $debitTotal += $transaction->amount;
                }

                // Update balance
                $balance = $creditTotal - $debitTotal;

                // Store the ledger data
                $ledgers[$account->id][] = [
                    'date' => $transaction->date,
                    'description' => $transaction->description,
                    'debit' => $transaction->type == 2 ? $transaction->amount : 0,
                    'credit' => $transaction->type == 1 ? $transaction->amount : 0,
                    'balance' => $balance
                ];
            }
        }


        return view(config('accountflow.view_path') . 'ledger', compact('ledgers', 'accounts'));
    }

    

   
public function getLedgerData()
{
    $accounts = Account::with('transactions')->get();

    $ledgerData = [];

    foreach ($accounts as $account) {
        $debitTotal = 0;
        $creditTotal = 0;
        $balance = 0;

        $transactions = $account->transactions->sortBy('date');
        foreach ($transactions as $transaction) {
            if ($transaction->type == 1) { // Income
                $creditTotal += $transaction->amount;
            } else { // Expense
                $debitTotal += $transaction->amount;
            }

            $balance = $creditTotal - $debitTotal;

            $ledgerData[] = [
                'date' => $transaction->date,
                'description' => $transaction->description,
                'debit' => $transaction->type == 2 ? $transaction->amount : 0,
                'credit' => $transaction->type == 1 ? $transaction->amount : 0,
                'balance' => $balance
            ];
        }
    }

    return DataTables::of(collect($ledgerData))
        ->addColumn('date', function ($row) {
            return $row['date'];
        })
        ->addColumn('description', function ($row) {
            return $row['description'];
        })
        ->addColumn('debit', function ($row) {
            return $row['debit'];
        })
        ->addColumn('credit', function ($row) {
            return $row['credit'];
        })
        ->addColumn('balance', function ($row) {
            return $row['balance'];
        })
        ->make(true);
}
}