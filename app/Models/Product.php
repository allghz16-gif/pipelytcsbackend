<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    protected $fillable = [
        'user_id', 'name', 'sku', 'stock', 'price', 'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function sales() {
        return $this->hasMany(Sale::class);
    }
}