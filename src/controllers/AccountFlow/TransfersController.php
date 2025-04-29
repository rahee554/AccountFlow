<?php

namespace App\Http\Controllers\AccountFlow;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Transfer;


use App\Models\AccountFlow\Account;
use App\Models\User;
use App\Http\Controllers\AccountFlow\AccountsController;

class TransfersController extends Controller
{


    public function getTransfersList(Request $request)
    {
        if ($request->ajax()) {
            $transfers = Transfer::select('unique_id', 'amount', 'from_account', 'to_account', 'description', 'created_by', 'date');

            return DataTables()
                ->of($transfers)

                ->editColumn('from_account', function ($transfer) {
                    return $transfer->fromAccount->name ?? '';
                })
                ->editColumn('to_account', function ($transfer) {
                    return $transfer->toAccount->name ?? '';
                })
                ->editColumn('created_by', function ($transfer) {
                    return $transfer->user ? $transfer->user->email : 'Unknown';
                })
                
                ->addColumn('actions', function ($transfer) {
                    return '<span class="svg-icon">
                                    <a href="#" data-id=""><i class="fad fa-edit text-gray-600 mx-2"></i></a>
                                    <a href="#" data-id=""><i class="fad fa-user text-success mx-2"></i></a>
                                    <a href="#" data-id=""><i class="fad fa-trash text-danger mx-2"></i></a>
                                </span>';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return abort(404); // Or handle the non-AJAX request appropriately
    }


    public function addTransfer(Request $request)
    {
        // Validation
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'from_account' => 'required|integer',
            'to_account' => 'required|integer',
            'date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string',
        ]);

        $fromAccount = Account::find($validatedData['from_account']);
        if ($fromAccount->balance < $validatedData['amount']) {
            return redirect()->back()->with('error', 'Low balance in account');
        }

        // Set default values
        $validatedData['date'] = $validatedData['date'] ?? now()->format('Y-m-d');

        // Generate a unique ID
        $unique_id = generateUniqueID(Transfer::class, 'unique_id');

        // Assign values from the validated data to the model attributes
        $transfer = new Transfer;
        $transfer->amount = $validatedData['amount'];
        $transfer->from_account = $validatedData['from_account'];
        $transfer->to_account = $validatedData['to_account'];
        $transfer->unique_id = $unique_id;
        $transfer->date = \Carbon\Carbon::parse($validatedData['date'])->toDateString(); // Save only the date part
        $transfer->description = $validatedData['description'];
        $transfer->created_by = Auth::id();

        // Save the transaction
        $transfer->save();
        AccountsController::updateAccountBalance();
        return redirect()->back()->with('success', 'Transfer saved successfully');
    }
}
