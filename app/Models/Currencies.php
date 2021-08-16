<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currencies extends Model
{
    protected $fillable = ["name","symbol","image", "status"];

    use HasFactory;

    public function CashFlow():HasMany
    {
        return $this->hasMany('App\Models\CashFlow', 'currency_id');
        
    }


}
