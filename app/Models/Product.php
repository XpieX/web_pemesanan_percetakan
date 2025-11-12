<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'calculation_type',
        'price_per_unit',
        'description',
        'image'
    ];
    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'addon_product');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
