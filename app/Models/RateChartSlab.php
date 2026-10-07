<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateChartSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_chart_id',
        'fat_from',
        'fat_to',
        'snf_from',
        'snf_to',
        'rate',
    ];

    public function rateChart()
    {
        return $this->belongsTo(RateChart::class);
    }
}
