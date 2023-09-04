<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterestRate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'percent',
        'created_by',
        'updated_by',
        'deleted_by'
    ];
}
