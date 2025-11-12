<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'addon_product');
    }

    public function orderItems()
    {
        return $this->belongsToMany(OrderItem::class, 'addon_order_item');
    }
}
