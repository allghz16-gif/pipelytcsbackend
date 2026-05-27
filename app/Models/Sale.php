<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model {
    protected $fillable = [
        'user_id',
        'platform_name',
        'product_name',
        'product_category',
        'waktu',
        'quantity',
        'price_per_unit',
        'total_revenue',
        'total_profit',
        'sold_at'
    ];

    protected $casts = [
        'sold_at'       => 'datetime',
        'total_revenue' => 'decimal:2',
        'total_profit'  => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}