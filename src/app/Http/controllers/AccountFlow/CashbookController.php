<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Transaction;
use Yajra\DataTables\DataTables;

class CashbookController extends Controller
{
    public function index()
    {

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


        return view(config('accountflow.view_path') . 'cashbook', compact('formattedTransactions'));
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

    public function getCashbookDataJson(Request $request)    {

        // Get current page or default to 1
        $page = $request->get('page', 1);
        $perPage = $request->get('perPage', 10);

        // Fetch transactions and format for cashbook
        $transactions = Transaction::with('account')->orderBy('date')->paginate($perPage, ['*'], 'page', $page);

        $balance = 0;
        $formattedTransactions = $transactions->getCollection()->map(function ($transaction) use (&$balance) {
            $income = $transaction->type == 1 ? $transaction->amount : '';
            $expense = $transaction->type != 1 ? $transaction->amount : '';
            $balance += $transaction->type == 1 ? $transaction->amount : -$transaction->amount;

            return [
                'date' => $transaction->date,
                'description' => $transaction->description,
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance
            ];
        });

        return response()->json([
            'data' => $formattedTransactions,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ]
        ], 200, [], JSON_UNESCAPED_SLASHES);
        
    }
}
