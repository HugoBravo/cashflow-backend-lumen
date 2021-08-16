<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashConcepts extends Model
{

    protected $fillable = ["type","concept","category_id", "status"];
    
    use HasFactory;
    
    public function CashCategory():HasMany
    {
        return $this->hasMany('App\Models\CashCategory', 'category_id');
        
    }
}
