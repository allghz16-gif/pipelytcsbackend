<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $table      = 'sellers';
    protected $primaryKey = 'seller_id';

    protected $fillable = [
        'nama',
        'email',
    ];

    // Relasi ke Order
    public function orders()
    {
        return $this->hasMany(Order::class, 'seller_id', 'seller_id');
    }
}