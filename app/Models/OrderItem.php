<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_size_id',
        'length',
        'width',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function size()
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id');
    }
    public function productSize()
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'addon_order_item');
    }
}
