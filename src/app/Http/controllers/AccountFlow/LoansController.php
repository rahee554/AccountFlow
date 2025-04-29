<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Loan;
use App\Models\AccountFlow\LoanTransaction;
use Yajra\DataTables\Facades\DataTables;

class LoansController extends Controller
{
    public function index()
    {
        return view(config('accountflow.view_path') . 'loans');
    }

    public function getLoansList(Request $request)
    {
        $loans = Loan::select('description', 'loan_type', 'amount', 'loan_user_id', 'roi', 'installments', 'installment_type', 'status', 'date', 'due_date');

        return $request->ajax() ? DataTables::of($loans)

            
        ->addColumn('loan_user', function ($loan) {
            $user = $loan->loan_user->name;
            return $user;
        })
        
        ->addColumn('transactions', function ($loan) {
                $loanId = $loan->id;

                // Sum the value of transactions associated with the asset
                $sumTransactions = LoanTransaction::where('trx_id', $loanId)->sum('trx_id');

                return $sumTransactions;
            })
            ->editColumn('status', function ($loan) {
                if ($loan->status == 1) {
                    return '<span class="badge badge-sm badge-light-success">Paid</span>';
                } elseif ($loan->status == 2) {
                    return '<span class="badge badge-sm badge-light-danger">Partially Paid</span>';
                } else {
                    return '<span class="badge badge-sm badge-light-info">UnPaid</span>';
                }
            })
            ->addColumn('actions', function ($loan) {
                return '<span class="svg-icon">
                                <a href="#" data-id=""><i class="fad fa-edit text-gray-600 mx-2"></i></a>
                                <a href="#" data-id=""><i class="fad fa-user text-success mx-2"></i></a>
                                <a href="#" data-id=""><i class="fad fa-trash text-danger mx-2"></i></a>
                            </span>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true)
            : abort(404);
    }
}
