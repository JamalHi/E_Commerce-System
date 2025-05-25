<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable=
    [
        'wallet',
        'user_id'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class,'payment_id');
    }

}
