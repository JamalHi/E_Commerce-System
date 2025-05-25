<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserExtra extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'owner',
        'area_id',
        'p_type',
        'description',
        'licence',
        'commerical_record',
        'personal_id',
        'accept',
    ];


    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }
    public function product(): HasMany
    {
        return $this->hasMany(product::class, 'owner_id');
    }
}
