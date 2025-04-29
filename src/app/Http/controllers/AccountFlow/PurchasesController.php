<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\AccountFlow\Purchase;
use App\Models\AccountFlow\PurchaseTransaction;

class PurchasesController extends Controller
{
    public function index()
    {
        return view(config('accountflow.view_path') . 'purchases');
    }
    public function purchaseTrxView()
    {
        return view(config('accountflow.view_path') . 'assets_trx');
    }

    public function getPurchasesList(Request $request)
    {
        $purchases = Purchase::select('name', 'description', 'type', 'amount', 'amount_paid', 'category_id', 'status', 'date');

        return $request->ajax()
            ? DataTables::of($purchases)
            ->addColumn('date', function ($purchase) {
                return $purchase->date;
            })
            ->addColumn('category', function ($purchase) {
                $category = $purchase->category->name;
                return $category;
            })
            ->addColumn('transactions', function ($purchase) {
                $purchaseId = $purchase->id;

                // Sum the value of transactions associated with the asset
                $sumTransactions = PurchaseTransaction::where('trx_id', $purchaseId)->sum('trx_id');

                return $sumTransactions;
            })
            ->editColumn('status', function ($purchase) {
                if ($purchase->status == 1) {
                    return '<span class="badge badge-sm badge-light-success">Paid</span>';
                } elseif ($purchase->status == 2) {
                    return '<span class="badge badge-sm badge-light-danger">Partially Paid</span>';
                } else {
                    return '<span class="badge badge-sm badge-light-info">UnPaid</span>';
                }
            })
            ->addColumn('actions', function ($purchase) {
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

    public function getPurchasesTrx(Request $request)
    {
        $purchase_trx = PurchaseTransaction::select('unique_id', 'asset_id', 'trx_id');

        return $request->ajax()
            ? DataTables::of($purchase_trx)

            ->addColumn('unique_id', function ($purchase_trx) {
                return $purchase_trx->transaction->unique_id;
            })

            ->addColumn('date', function ($purchase_trx) {
                return $purchase_trx->transaction->date;
            })
            ->addColumn('category', function ($purchase_trx) {
                return $purchase_trx->transaction->category->name;
            })
            ->addColumn('name', function ($purchase_trx) {
                $purchase_name = $purchase_trx->asset->name;
                return $purchase_name;
            })
            ->addColumn('value', function ($purchase_trx) {
                $vlaue = $purchase_trx->asset->value;
                return $vlaue;
            })
            ->addColumn('amount', function ($purchase_trx) {
                $amount = $purchase_trx->transaction->amount;
                return $amount;
            })

            ->addColumn('actions', function ($purchase_trx) {
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
