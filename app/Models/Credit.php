<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'pagare_number',
        'amount',
        'date',
        'loan_amount',
        'initial_fee',
        'due',
        'interest_rate',
        'monthly_fees',
        'notes',
        'disbursement_date',
        'state',
        'inactivation_reason',
        'created_by',
        'updated_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

}
