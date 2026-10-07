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
        'name',
        'phone',
        'village',
        'address',
        'supplier_type',
        'animal_type',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'custom_rate_override',
        'current_balance',
        'status',
    ];

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
}
