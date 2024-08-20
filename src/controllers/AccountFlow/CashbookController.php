<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Transaction;
use Yajra\DataTables\DataTables;
class CashbookController extends Controller
{
    public function index(){

         // Fetch transactions and order by date
         $transactions = Transaction::with('account')->orderBy('date')->get();


           // Initialize running balance
        $balance = 0;

        // Format transactions for cashbook
        $formattedTransactions = $transactions->map(function ($transaction) use (&$balance) {
            if ($transaction->type == 1) { // Income
                $balance += $transaction->amount;
                $transaction->income = $transaction->amount;
                $transaction->expense = '';
            } else { // Expense
                $balance -= $transaction->amount;
                $transaction->income = '';
                $transaction->expense = $transaction->amount;
            }
            $transaction->balance = $balance;
            return $transaction;
        });


        return view(config('accountflow.view_path') . 'cashbook' , compact('formattedTransactions'));
    }
    public function getCashbookData()
{
    // Fetch transactions and order by date
    $transactions = Transaction::with('account')->orderBy('date')->get();

    // Initialize running balance
    $balance = 0;

    // Format transactions for cashbook
    $formattedTransactions = $transactions->map(function ($transaction) use (&$balance) {
        if ($transaction->type == 1) { // Income
            $balance += $transaction->amount;
            $income = $transaction->amount;
            $expense = '';
        } else { // Expense
            $balance -= $transaction->amount;
            $income = '';
            $expense = $transaction->amount;
        }
        
        return [
            'date' => $transaction->date,
            'description' => $transaction->description,
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance
        ];
    });

    return DataTables::of(collect($formattedTransactions))
        ->addColumn('date', function ($row) {
            return $row['date'];
        })
        ->addColumn('description', function ($row) {
            return $row['description'];
        })
        ->addColumn('income', function ($row) {
            return $row['income'];
        })
        ->addColumn('expense', function ($row) {
            return $row['expense'];
        })
        ->addColumn('balance', function ($row) {
            return $row['balance'];
        })
        ->make(true);
}

    
}
