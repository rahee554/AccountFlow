<?php

namespace ArtflowStudio\AccountFlow\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'ac_loans';

    protected $fillable = [
        'unique_id',
        'name',
        'description',
        'amount',
        'loan_type',
        'loan_partner_id',
        'roi',
        'installments',
        'installment_type',
        'date',
        'created_at',
        'updated_at',
    ];

    public function loan_partner()
    {
        return $this->belongsTo(LoanUser::class);
    }
}

