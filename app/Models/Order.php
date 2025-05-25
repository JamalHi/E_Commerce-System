<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'by_id',
        'location',
        'total_price',
        'isDelivered',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'by_id');
    }

    public function orderItem(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
