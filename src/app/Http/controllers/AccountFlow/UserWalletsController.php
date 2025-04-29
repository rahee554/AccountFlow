<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use App\Models\AccountFlow\UserWallet;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class UserWalletsController extends Controller
{
    public function index()
    {
        return view(config('accountflow.view_path') . 'user_wallets');
    }

    public function getWallets()
    {
        $wallets = UserWallet::get();

        return DataTables::of($wallets)
            ->addColumn('user_id', function ($wallet) {
                return $wallet->user->name;
            })

            ->addColumn('actions', function ($wallet) {
                // Add any additional columns or actions you need
                return '<button>Edit</button>';
            })

            ->rawColumns(['actions']) // Specify columns containing HTML/markup
            ->make(true);
    }
}
