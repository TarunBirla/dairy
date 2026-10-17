<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farmer_code',
        'user_id',
        'branch_id',
        'collection_center_id',
        'rate_chart_id',
        'pricing_type',
        'name',
        'name_hi',
        'photo',
        'phone',
        'village',
        'address',
        'vehicle',
        'route_id',
        'supplier_type',
        'animal_type',
        'cow_milk_rate',
        'buffalo_milk_rate',
        'branch_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'custom_rate_override',
        'current_balance',
        'anamat',
        'building_fund',
        'installment',
        'etc_amount',
        'grant_amount',
        'extra_rates',
        'status',
    ];

    /**
     * Get pricing type with intelligent fallback
     */
    public function getPricingTypeAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        if ((float) $this->cow_milk_rate > 0 || (float) $this->buffalo_milk_rate > 0) {
            return 'fixed_rate';
        }
        return 'rate_chart';
    }

    protected $casts = [
        'current_balance' => 'decimal:2',
        'cow_milk_rate' => 'decimal:2',
        'buffalo_milk_rate' => 'decimal:2',
        'anamat' => 'decimal:2',
        'building_fund' => 'decimal:2',
        'installment' => 'decimal:2',
        'etc_amount' => 'decimal:2',
        'grant_amount' => 'decimal:2',
        'extra_rates' => 'array',
    ];

    /**
     * Get farmer-specific rate/amount for a category configured as Farmer Wise.
     * Supports exact match, case-insensitive match, and legacy database columns.
     */
    public function getFarmerWiseRate(string $categoryName, float $default = 0.0): float
    {
        $key = trim($categoryName);
        $rates = is_array($this->extra_rates) ? $this->extra_rates : [];

        // 1. Direct match in extra_rates
        if (isset($rates[$key]) && is_numeric($rates[$key])) {
            return (float) $rates[$key];
        }

        // 2. Case-insensitive match in extra_rates
        foreach ($rates as $k => $v) {
            if (strcasecmp(trim($k), $key) === 0 && is_numeric($v)) {
                return (float) $v;
            }
        }

        // 3. Fallback to legacy columns
        $legacyMap = [
            'anamat' => 'anamat',
            'building fund' => 'building_fund',
            'installment' => 'installment',
            'etc' => 'etc_amount',
            'grant' => 'grant_amount',
        ];
        $lower = strtolower($key);
        if (isset($legacyMap[$lower]) && isset($this->{$legacyMap[$lower]}) && is_numeric($this->{$legacyMap[$lower]})) {
            return (float) $this->{$legacyMap[$lower]};
        }

        return (float) $default;
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
                return $this->photo;
            }
            if (file_exists(public_path('uploads/farmers/' . $this->photo))) {
                return asset('uploads/farmers/' . $this->photo);
            }
            if (file_exists(public_path('storage/' . $this->photo))) {
                return asset('storage/' . $this->photo);
            }
            return asset($this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=10b981&color=ffffff&bold=true';
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function collectionCenter()
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function rateChart()
    {
        return $this->belongsTo(RateChart::class);
    }

    public function collections()
    {
        return $this->hasMany(MilkCollection::class);
    }

    public function advances()
    {
        return $this->hasMany(FarmerAdvance::class);
    }

    public function deductions()
    {
        return $this->hasMany(FarmerDeduction::class);
    }

    public function farmerInvoices()
    {
        return $this->hasMany(FarmerInvoice::class);
    }

    public function loans()
    {
        return $this->hasMany(FarmerLoan::class);
    }

    public function settlements()
    {
        return $this->hasMany(FarmerSettlement::class);
    }

    public function ledgers()
    {
        return $this->hasMany(FarmerLedger::class);
    }

    public function productSales()
    {
        return $this->hasMany(ProductSale::class);
    }

    public function emergencyFunds()
    {
        return $this->hasMany(FarmerEmergencyFund::class);
    }
}

