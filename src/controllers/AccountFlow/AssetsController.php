<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFlow\Asset;
use App\Models\AccountFlow\AssetTransaction;
use App\Models\AccountFlow\Transaction;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;



class AssetsController extends Controller
{
    public function index()
    {
        return view(config('accountflow.view_path') . 'assets');
    }
    public function assetTrxView()
    {
        return view(config('accountflow.view_path') . 'assets_trx');
    }

    public function getAssets(Request $request)
    {
        $assets = Asset::select('id', 'name', 'description', 'value', 'category_id', 'status', 'acquisition_date');

        // Apply search filters if search value is provided
        // if ($request->has('search') && !empty($request->input('search')) && $request->has('search_columns')) {
        //     $searchValue = $request->input('search');
        //     $searchColumns = $request->input('search_columns');

        //     $assets->where(function ($query) use ($searchValue, $searchColumns) {
        //         foreach ($searchColumns as $columnIndex) {
        //             // Assuming $columnIndex is the index of the column to search
        //             $query->orWhere(DB::raw("CONVERT(`$columnIndex`, CHAR)"), 'like', '%' . $searchValue . '%');
        //         }
        //     });
        // }

        return $request->ajax()
            ? DataTables::of($assets)
            ->addColumn('date', function ($asset) {
                return $asset->acquisition_date;
            })
            ->addColumn('category', function ($asset) {
                $category = $asset->category->name;
                return $category;
            })
            ->addColumn('transactions', function ($asset) {
                $assetId = $asset->id;

                // Sum the value of transactions associated with the asset
                $sumTransactions = AssetTransaction::where('asset_id', $assetId)->sum('asset_id');

                return $sumTransactions;
            })
            ->editColumn('status', function ($asset) {
                if ($asset->status == 1) {
                    return '<span class="badge badge-sm badge-light-success">Operating Asset</span>';
                } elseif ($asset->status == 2) {
                    return '<span class="badge badge-sm badge-light-danger">Not Operating</span>';
                } else {
                    return '<span class="badge badge-sm badge-light-info">Sold Out</span>';
                }
            })
            ->addColumn('actions', function ($asset) {
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

    public function getAssetsTrx(Request $request)
    {
        $asset_trx = AssetTransaction::select('asset_id', 'trx_id');

        return $request->ajax()
            ? DataTables::of($asset_trx)

            ->addColumn('unique_id', function ($asset_trx) {
                return $asset_trx->transaction->unique_id;
            })

            ->addColumn('date', function ($asset_trx) {
                return $asset_trx->transaction->date;
            })
            ->addColumn('category', function ($asset_trx) {
                return $asset_trx->transaction->category->name;
            })
            ->addColumn('name', function ($asset_trx) {
                $asset_name = $asset_trx->asset->name;
                return $asset_name;
            })
            ->addColumn('value', function ($asset_trx) {
                $vlaue = $asset_trx->asset->value;
                return $vlaue;
            })
            ->addColumn('amount', function ($asset_trx) {
                $amount = $asset_trx->transaction->amount;
                return $amount;
            })

            ->addColumn('actions', function ($asset_trx) {
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

    public function storeAsset(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'value' => 'required|numeric',
            'category' => 'required|numeric',
            'status' => 'required|numeric',
            'date' => 'nullable|date',
            'description' => 'nullable|string',
            'assetArray' => 'required|array',
            'assetArray.*.amount' => 'required|numeric',
            'assetArray.*.account' => 'required|numeric',
            'assetArray.*.date' => 'required|date',
        ]);
    
        // Wrap logic in a transaction
        DB::transaction(function () use ($validatedData) {
            // Step 1: Store the Asset data
            $asset = new Asset;
            $asset->name = $validatedData['name'];
            $asset->value = $validatedData['value'];
            $asset->acquisition_date = $validatedData['date'] ?? null;
            $asset->category_id = $validatedData['category'];
            $asset->status = $validatedData['status'];
            $asset->description = $validatedData['description'] ?? null;
            $asset->save();
    
            // Step 2: Store each AssetTransaction
            foreach ($validatedData['assetArray'] as $transactionData) {
                $trx = new Transaction;
                $trx->unique_id = generateUniqueID(Transaction::class, 'unique_id');
                $trx->account_id = $transactionData['account'];
                $trx->category_id = $validatedData['category'];
                $trx->amount = $transactionData['amount'];
                $trx->type = 2;
                $trx->date = $transactionData['date'];
                $trx->description = 'Asset Trx {' . $asset->description . '}';
                $trx->save();
    
                $assetTransaction = new AssetTransaction;
                $assetTransaction->asset_id = $asset->id;
                $assetTransaction->trx_id = $trx->id;
                $assetTransaction->save();
    
                // Subtract from the account
                AccountsController::subtractFromAccount($transactionData['account'], $transactionData['amount']);
            }
        });
    
        return response()->json(['message' => 'Asset and transactions stored successfully']);
    }
    
}
