<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'type_pay',
        'date',
        'credit_id',
        'paid_fees',
        'created_by',
        'updated_by'
    ];

    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }
}
