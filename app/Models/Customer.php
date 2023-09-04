<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'document',
        'name',
        'lastname',
        'address',
        'phone',
        'contact',
        'credit_limit',
        'status',
        'grace_days',
        'city_id',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }
}
