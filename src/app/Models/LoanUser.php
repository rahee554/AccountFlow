<?php

namespace ArtflowStudio\AccountFlow\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanUser extends Model
{
    use HasFactory;

    protected $table = 'ac_loan_partners';
}

