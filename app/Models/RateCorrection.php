<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'filter_type',
        'apply_to',
        'from_date',
        'to_date',
        'shift',
        'milk_type',
        'rate_chart_id',
        'selected_ids',
        'records_updated',
        'total_difference',
        'applied_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'selected_ids' => 'array',
        'records_updated' => 'integer',
        'total_difference' => 'decimal:2',
    ];

    public function rateChart()
    {
        return $this->belongsTo(RateChart::class);
    }

    public function appliedByUser()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
