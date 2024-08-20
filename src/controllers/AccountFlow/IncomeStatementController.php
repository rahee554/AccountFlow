<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Transaction;    

class IncomeStatementController extends Controller
{
    public function index(){

          // Fetch all transactions with categories
          $transactions = Transaction::with('category')->orderBy('date')->get();

          // Initialize arrays for income and expenses
          $incomeCategories = [];
          $expenseCategories = [];
  
          foreach ($transactions as $transaction) {
              $category = $transaction->category->name;
  
              if ($transaction->type == 1) { // Income
                  if (!isset($incomeCategories[$category])) {
                      $incomeCategories[$category] = 0;
                  }
                  $incomeCategories[$category] += $transaction->amount;
              } else { // Expense
                  if (!isset($expenseCategories[$category])) {
                      $expenseCategories[$category] = 0;
                  }
                  $expenseCategories[$category] += $transaction->amount;
              }
          }

          
        return view(config('accountflow.view_path') . 'income_statement' , compact('incomeCategories', 'expenseCategories') );
    }
}
