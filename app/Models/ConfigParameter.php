<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigParameter extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    use HasFactory;

    public $timestamps = false;
}
