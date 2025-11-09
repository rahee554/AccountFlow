<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanUser extends Model
{
    use HasFactory;

    protected $table = 'ac_loan_partners';
}
