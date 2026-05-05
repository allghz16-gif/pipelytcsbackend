<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table      = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'nama',
        'harga',
        'stok',
        'kategori',
        'status',
    ];

    // Relasi ke OrderDetail
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'product_id', 'product_id');
    }
}