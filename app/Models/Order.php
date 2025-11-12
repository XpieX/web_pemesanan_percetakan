<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 
        'status_id', 
        'order_date', 
        'total_price', 
        'note'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }
    public function details()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
