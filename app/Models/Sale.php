<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model {
    protected $fillable = [
        'user_id', 'product_id', 'platform_id',
        'product_name', 'product_category',
        'quantity', 'price_per_unit',
        'total_revenue', 'total_profit', 'sold_at'
    ];

    protected $casts = [
        'sold_at'       => 'datetime',
        'total_revenue' => 'decimal:2',
        'total_profit'  => 'decimal:2',
    ];

    public function platform() {
        return $this->belongsTo(Platform::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}