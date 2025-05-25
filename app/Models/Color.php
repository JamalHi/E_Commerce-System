<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    protected $fillable = [
        'color',
        'product_id',
    ];

    public function product(){
        return $this->belongsTo(Product::class , 'product_id');
    }

    public function size(): HasMany
    {
        return $this->hasMany(Size::class, 'color_id');
    }
}
