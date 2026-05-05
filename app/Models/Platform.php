<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $table      = 'platforms';
    protected $primaryKey = 'platform_id';

    protected $fillable = [
        'nama',
        'conversion_rate',
        'fee_percentage',
    ];

    // Relasi ke Order
    public function orders()
    {
        return $this->hasMany(Order::class, 'platform_id', 'platform_id');
    }
}