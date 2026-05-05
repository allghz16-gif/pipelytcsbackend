<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order_detail extends Model
{
    protected $table = 'order_detail';

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'total',
    ];

    // Relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // Relasi ke Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}