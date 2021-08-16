<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashClosure extends Model
{

    protected $fillable = ["datetime","status","obs", "username"];

    use HasFactory;

    
}
