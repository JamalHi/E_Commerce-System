<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;
    protected $fillable = [
        'size',
        'quant',
        'color_id'
    ];

    public function color(){
        return $this->belongsTo(Color::class , 'color_id');
    }

}
