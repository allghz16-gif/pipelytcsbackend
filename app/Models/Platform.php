<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model {
    protected $fillable = ['name', 'slug', 'color'];

    public function sales() {
        return $this->hasMany(Sale::class);
    }

    public function campaigns() {
        return $this->hasMany(Campaign::class);
    }
}