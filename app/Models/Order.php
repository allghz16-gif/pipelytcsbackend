<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table      = 'orders';
    protected $primaryKey = 'order_id';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'order_id',
        'tanggal',
        'jam',
        'seller_id',
        'platform_id',
        'total_harga',
    ];

    // Relasi ke Platform
    public function platform()
    {
        return $this->belongsTo(Platform::class, 'platform_id', 'platform_id');
    }

    // Relasi ke Seller
    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'seller_id');
    }

    // Relasi ke OrderDetail
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }
}