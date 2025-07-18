<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice'; // ou 'invoices' si ta table s'appelle comme ça

    protected $primaryKey = 'invoice_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'issue_date',
        'total_amount',
        'payment_id',
        'pdf_path',
    ];
}
