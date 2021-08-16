<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserRoles extends Model
{
    use HasFactory;

    public function User():HasMany
    {
        return $this->hasMany('App\Models\User', 'role_id');
        
    }


}
