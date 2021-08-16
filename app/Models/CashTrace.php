<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTrace extends Model
{
    protected $fillable = [
        "cash_flow_id",
        "username", 
        "comments",
    ];

    use HasFactory;

 
    public function CashFlow():BelongsTo {
        return $this->belongsTo('App\Models\CashFlow','id');
    }








    
}
