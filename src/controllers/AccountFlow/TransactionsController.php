<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountFlow\Loan;
use Carbon\Carbon;
use App\Models\AccountFlow\PurchaseTransaction;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\PaymentMethod;

use App\Http\Controllers\AccountFlow\AccountsController;




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



    public function addExpense(Request $request)
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

        // Generate a unique ID using a helper or Accounflow method
        $unique_id = generateUniqueID(Transaction::class, 'unique_id');

        // Create a new transaction
        $transaction = new Transaction;
        $transaction->amount = $validatedData['amount'];
        $transaction->payment_method = $validatedData['payment_method'];
        $transaction->account_id = $validatedData['account_id'];
        $transaction->unique_id = $unique_id;
        $transaction->category_id = $validatedData['category_id'];
        $transaction->date = $validatedData['date'];
        $transaction->description = $validatedData['description'];
        $transaction->type = 2; // For Expense
        $transaction->added_by = Auth::id();

        // Save the transaction
        $transaction->save();

        // Update the Account balance
        $account = Account::find($validatedData['account_id']);
        $account->balance -= $validatedData['amount']; // Subtract for expense
        $account->save();

        return redirect()->back()->with('success', 'Transaction saved successfully');
    }

    public function addPurchase() {}
    public function addLoan() {}

    public function getTransactionsData(Request $request)
    {
        $query = Transaction::select([
            'id',
            'unique_id',
            'type',
            'amount',
            'date',
            'description',
            'category_id',
            'account_id',
            'payment_method'
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
                return '<i class="fas fa-edit" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $transaction->id . '"></i>
                        <i class="fas fa-trash delete-btn" data-id="' . $transaction->id . '"></i>';;
            })
            ->rawColumns(['actions', 'amount', 'account'])
            ->make(true);
    }

    public function getSingleTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Fetch payment methods and accounts
        $paymentMethods = PaymentMethod::all();
        $accounts = Account::all();

        // Fetch categories based on the transaction type
        if ($transaction->type === 1) {
            $categories = Category::where('flow_type', 1)->whereNotNull('parent_id')->get();
        } else {
            $categories = Category::where('flow_type', 2)->whereNotNull('parent_id')->get();
        }

        return response()->json([
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'date' => $transaction->date,
            'description' => $transaction->description,
            'payment_method_id' => $transaction->payment_method_id,
            'account_id' => $transaction->account_id,
            'category_id' => $transaction->category_id,
            'paymentMethods' => $paymentMethods,
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }


    public function updateRecord(Request $request, $id)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'account_id' => 'required|integer',
            'category_id' => 'required|integer',
            'date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'description' => 'nullable|string',
        ]);

        // Find the transaction by ID
        $transaction = Transaction::findOrFail($id);

        // Store original values
        $originalAccountId = $transaction->account_id;
        $originalAmount = $transaction->amount;
        $originalType = $transaction->type;

        // Fetch the original account
        $oldAccount = Account::find($originalAccountId);
        $newAccount = Account::find($validatedData['account_id']); // Get new account if changed


        // Check if the amount has changed
        $amountChanged = $originalAmount !== $validatedData['amount'];

        // Adjust the balance of the old account based on the original amount
        if ($amountChanged) {
            // Adjust balance based on the type of transaction
            if ($originalType === 1) { // Income
                $oldAccount->balance -= $originalAmount; // Remove original income
            } elseif ($originalType === 2) { // Expense
                $oldAccount->balance += $originalAmount; // Add back the original expense
            }
        }

        // Update the transaction with new values
        $transaction->amount = $validatedData['amount'];
        $transaction->payment_method = $validatedData['payment_method'];
        $transaction->category_id = $validatedData['category_id'];
        $transaction->date = $validatedData['date'] ?? now()->format('Y-m-d'); // Default date if not provided
        $transaction->description = $validatedData['description'];

        // Adjust balance again based on the new amount
        if ($amountChanged) {
            // Adjust the balance based on the new amount and type of transaction
            if ($validatedData['amount'] !== null) {
                if ($originalType === 1) { // Income
                    $oldAccount->balance += $validatedData['amount']; // Add new income
                } elseif ($originalType === 2) { // Expense
                    $oldAccount->balance -= $validatedData['amount']; // Subtract new expense
                }
            }
        }
        // Handle account change
        if ($originalAccountId !== $validatedData['account_id']) {
            // Adjust the balance for the new account
            if ($newAccount) {
                if ($originalType === 1) { // Income
                    $newAccount->balance += $validatedData['amount']; // Add new income
                    $oldAccount->balance -= $validatedData['amount']; // Deduct from old account
                } elseif ($originalType === 2) { // Expense
                    $newAccount->balance -= $validatedData['amount']; // Subtract new expense
                    $oldAccount->balance += $validatedData['amount']; // Add back to old account
                }
                $newAccount->save(); // Save the new account balance
            }
            // Save changes to the old account balance
            if ($oldAccount) {
                $oldAccount->save(); // Save the old account balance after adjustments
            }

            // Update the account ID in the transaction
            $transaction->account_id = $validatedData['account_id'];
        }
        // Save the updated account balance and transaction
        $oldAccount->save();
        $transaction->save();

        return response()->json(['message' => 'Transaction updated successfully']);
    }
    public function deleteRecord($id)
    {
        // Find the transaction by ID
        $transaction = Transaction::findOrFail($id);
    
        // Fetch the associated account
        $account = Account::find($transaction->account_id);
    
        // Adjust the account balance based on transaction type
        if ($account) {
            if ($transaction->type === 1) { // Income
                $account->balance -= $transaction->amount; // Deduct the income
            } elseif ($transaction->type === 2) { // Expense
                $account->balance += $transaction->amount; // Add back the expense
            }
            $account->save(); // Save the updated account balance
        }
    
        // Delete the transaction
        $transaction->delete();
    
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
    
}
