<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\AccountFlow\Category;
use app\Helpers\AccountsHelper;
class AccountsCategoriesController extends Controller
{
    public function index()
    {
        return view(config('accountflow.view_path') .'categories');
    }

    public function getCategories()
    {
        $categories = Category::select([
            'id', 'flow_type', 'name', 'parent_id', 'status',
            'privacy', 'icon',
        ])->get();
    
        $parentCategories = $categories->keyBy('id');
    
        return DataTables::of($categories)
            ->editColumn('name', function ($category) {
                if (!$category->icon) {
                    $nameInitial = strtoupper(substr($category->name, 0, 1));
                    $symbol = '<div class="symbol-label text-primary">' . $nameInitial . '</div>';
                } else {
                    $symbol = '<div class="symbol-label" style="background-image:url(' . asset('assets/media/icons/accounts_icons/' . $category->icon) . ')"></div>';
                }
                return '<div class="d-flex flex-stack justify-content-start">
                        <div class="symbol symbol-50px symbol-circle">' . $symbol . '</div>
                        <div class="ms-5 fw-bold text-dark text-hover-primary">' . $category->name . '</div>
                    </div>';
            })
            ->editColumn('parent_id', function ($category) use ($parentCategories) {
                if ($category->parent_id !== null) {
                    $parentCategory = $parentCategories->get($category->parent_id);
                    return '<span class="badge badge-light">' .
                        ($parentCategory ? $parentCategory->name : 'Unknown Parent') .
                        '</span>';
                }
                return '<span class="badge badge-white text-gray-400">No Parent</span>';
            })
            ->editColumn('flow_type', function ($category) {
                return $category->flow_type == 1 ?
                    '<span class="badge badge-light-success">Income</span>' :
                    '<span class="badge badge-light-danger">Expense</span>';
            })
            
            ->editColumn('status', function ($category) {
            
         
                return $category->status == 1 ?
                    '<span class="badge badge-light-success">Active</span>' :
                    '<span class="badge badge-light-danger">Inactive</span>';
            })

            ->editColumn('privacy', function ($category) {
                return $category->privacy == 1 ?
                    '<span class="badge badge-light-danger"><i class="fas fa-lock mx-2"></i> Locked</span>' :
                    '<span class="badge badge-light-success"><i class="fas fa-unlock mx-2"></i> unlocked</span>';
            })
            ->addColumn('actions', function ($category) {
                return '<button>Edit</button>';
            })
            ->rawColumns(['name', 'privacy', 'flow_type', 'parent_id', 'status', 'actions'])
            ->make(true);
    }
    
}
