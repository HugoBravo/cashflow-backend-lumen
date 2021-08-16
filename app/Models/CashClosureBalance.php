<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashClosureBalance extends Model
{

    protected $fillable = ["cash_closure_id","currency_id","balance"];

    use HasFactory;
}
