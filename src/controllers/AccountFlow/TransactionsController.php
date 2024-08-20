<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Purchase;
use App\Models\AccountFlow\Loan;
use Carbon\Carbon;
use App\Models\AccountFlow\PurchaseTransaction;
use App\Models\AccountFlow\Account;




class TransactionsController extends Controller
{
    public function index()
    {
        return view(config('accountflow.view_path') . 'transactions');
    }

    public function addIncome(Request $request)
    {
        // Validation
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'account_id' => 'required|integer',
            'category_id' => 'required|integer',
           'date' => ['nullable', 'date', 'date_format:Y-m-d'],

            'description' => 'nullable|string',
        ]);

        // Set default values
        $validatedData['date'] = $validatedData['date'] ?? now()->format('Y-m-d');

        // Generate a unique ID
        $unique_id = generateUniqueID(Transaction::class, 'unique_id');

        // Assign values from the validated data to the model attributes
        $transaction = new Transaction;
        $transaction->amount = $validatedData['amount'];
        $transaction->payment_method = $validatedData['payment_method'];
        $transaction->account_id = $validatedData['account_id'];
        $transaction->unique_id = $unique_id;
        $transaction->category_id = $validatedData['category_id'];
        $transaction->date = $validatedData['date'];  // The date is already in 'Y-m-d' format
        $transaction->description = $validatedData['description'];
        $transaction->type = 1;
        //$transaction->added_by = auth()->id();

        // Save the transaction
        $transaction->save();


        // Update the Account Balance if Above SUCCESS
        $account = Account::find($validatedData['account_id']);
        $account->balance += $validatedData['amount'];
        $account->save();


        return redirect()->back()->with('success', 'Transaction saved successfully');
    }



    public function addExpense()
    {
    }
    public function addPurchase()
    {
    }
    public function addLoan()
    {
    }

    public function getTransactionsData(Request $request)
    {
        $query = Transaction::select([
            'id', 'unique_id', 'type', 'amount', 'date', 'description', 'category_id', 'account_id', 'payment_method'
        ])->with(['category:id,name', 'account:id,name', 'paymentMethod:id,name']);

        return DataTables::of($query)
            ->addColumn('details', function ($transaction) {
                return $transaction->description;
            })
            ->addColumn('amount', function ($transaction) {
                if ($transaction->type == 1) {
                    return '<span class="text-success">Rs: ' . $transaction->amount . '</span>';
                } else {
                    return '<span class="text-danger">Rs: ' . $transaction->amount . '</span>';
                }
            })
            ->addColumn('category', function ($transaction) {
                return optional($transaction->category)->name ?? 'N/A';
            })
            ->addColumn('account', function ($transaction) {
                $account = optional($transaction->account)->name ?? 'N/A';
                return '<span class="badge badge-light">' . $account . '</span>';
            })
            ->addColumn('method', function ($transaction) {
                return optional($transaction->paymentMethod)->name ?? 'N/A';
            })
            ->addColumn('actions', function ($transaction) {
                return '<button>Edit</button>';
            })
            ->rawColumns(['actions', 'amount', 'account'])
            ->make(true);
    }
}
