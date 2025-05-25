<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [

        'owner_id',
        'name',
        'category',
        'price',
        'gender',
        'description',
    ];

    public function userExtra(){
        return $this->belongsTo(UserExtra::class , 'owner_id');
    }

    public function orderItemm(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'from_id');
    }

    public function orderItem(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }
    public function image(): HasMany
    {
        return $this->hasMany(Image::class, 'product_id');
    }
    public function rates(){
        return $this->hasMany(Rate::class,'product_id');
    }

    public function book(): HasMany
    {
        return $this->hasMany(Book::class, 'product_id');
    }

    public function bookOWner(): HasMany
    {
        return $this->hasMany(Book::class, 'from_id');
    }

    public function color(): HasMany
    {
        return $this->hasMany(Color::class, 'product_id');
    }

    public function quant_order() {
        return $this->hasMany(Color::class)
            ->join('sizes', 'sizes.color_id', '=', 'colors.id')
            ->orderBy('sizes.quant', 'asc');
    }
}
