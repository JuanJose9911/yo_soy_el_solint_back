<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'date',
        'amount',
        'fee',
        'interest',
        'late_interest_paid',
        'amortization',
        'credit_due',
        'due',
        'interest_due',
        'late_due',
        'state',
        'late_interest_rate',
        'late_interest_pay',
        'credit_id'
    ];

    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }
}
