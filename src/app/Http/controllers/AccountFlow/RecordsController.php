<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecordsController extends Controller
{
    public function index(){
        return view(config('accountflow.view_path') . 'records');
    }
}
