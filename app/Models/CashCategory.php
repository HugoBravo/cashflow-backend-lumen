<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashCategory extends Model
{
    protected $fillable = ["category", "status", "type"];

    use HasFactory;


    //TODO: Revisar relacion con tabla conceptos
    public function CashConcepts():HasMany
    {
        return $this->hasMany('App\Models\CashConcepts', 'category_id');
        
    }

}
