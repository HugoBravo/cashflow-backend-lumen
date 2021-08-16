<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashFlow extends Model
{
    protected $fillable = [
        "cash_concept_id",
        "currency_id",
        "datetime", 
        "username",
        "amount",
        "type",
        "status",
        "obs",
        "room",
        "doc", 
        "pax",
        "image_name",
        "image_size",
        "nullable"
    ];

    use HasFactory;

    protected $table = 'cash_flow';

    public function Currency():BelongsTo {
        return $this->belongsTo('App\Models\Currencies','id');
    }
 
    public function CashConcept():BelongsTo {
        return $this->belongsTo('App\Models\CashConcept','id');
    }

    public function CashTrace():HasMany
    {
        return $this->hasMany('App\Models\CashTrace', 'cash_flow_id');
    }
}
