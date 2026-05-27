<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model {
    protected $fillable = [
        'user_id',
        'platform_id',
        'name',
        'impressions',
        'clicks',
        'conversions',
        'ad_spend',
        'revenue',
        'roas',
        'period_start',
        'period_end'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'ad_spend'     => 'decimal:2',
        'revenue'      => 'decimal:2',
        'roas'         => 'decimal:2',
    ];

    public function platform() {
        return $this->belongsTo(Platform::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}