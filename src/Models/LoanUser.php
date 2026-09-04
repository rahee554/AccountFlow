<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A loan counterparty. Table: ac_loan_partners.
 *
 * @property int $id
 * @property string $name
 * @property string $contact
 * @property string $cnic
 * @property string|null $company
 * @property string|null $note
 */
class LoanUser extends Model
{
    use HasFactory;

    protected $table = 'ac_loan_partners';

    protected $guarded = ['id'];
}
