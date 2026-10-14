<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'filter_type',
        'category',
        'apply_to',
        'from_date',
        'to_date',
        'start_date',
        'end_date',
        'shift',
        'milk_type',
        'rate_chart_id',
        'selected_ids',
        'farmer_ids',
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

    public function setStartDateAttribute($value)
    {
        $this->attributes['from_date'] = $value;
        if (\Illuminate\Support\Facades\Schema::hasColumn('rate_corrections', 'start_date')) {
            $this->attributes['start_date'] = $value;
        }
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['to_date'] = $value;
        if (\Illuminate\Support\Facades\Schema::hasColumn('rate_corrections', 'end_date')) {
            $this->attributes['end_date'] = $value;
        }
    }

    public function setCategoryAttribute($value)
    {
        $this->attributes['filter_type'] = $value;
        if (\Illuminate\Support\Facades\Schema::hasColumn('rate_corrections', 'category')) {
            $this->attributes['category'] = $value;
        }
    }

    public function setFarmerIdsAttribute($value)
    {
        $this->attributes['selected_ids'] = is_array($value) ? json_encode($value) : $value;
    }

    public function rateChart()
    {
        return $this->belongsTo(RateChart::class);
    }

    public function appliedByUser()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
