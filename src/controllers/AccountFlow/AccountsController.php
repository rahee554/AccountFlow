<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Account;

use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Transfer;

class AccountsController extends Controller
{
    public function index()
    {

          // Retrieve view path from configuration and append 'accounts'
          //$viewPath = config('accountflow.view_path') . 'default';

          // Return the view
          return view(config('accountflow.view_path') . 'accounts');
    }

    public function getList(Request $request)
    {

        if ($request->ajax()) {
            $data = Account::Select('name', 'description', 'balance', 'status');

            return DataTables()
                ->of($data)


                ->addColumn('actions', function ($data) {
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

    public static function updateAccountBalance()
    {
        $accounts = Account::all();

        foreach ($accounts as $account) {
            $transactionSum = Transaction::where('account_id', $account->id)->sum('amount');
            $transferFromSum = Transfer::where('from_account', $account->id)->sum('amount');
            $transferToSum = Transfer::where('to_account', $account->id)->sum('amount');

            $account->balance = $transactionSum + $transferToSum - $transferFromSum;
            $account->save();
        }
    }
}
