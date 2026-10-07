<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'user_id',
        'branch_id',
        'route_id',
        'name',
        'phone',
        'email',
        'address',
        'locality',
        'category',
        'credit_limit',
        'current_balance',
        'delivery_sequence',
        'delivery_instructions',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function dailyDeliveries()
    {
        return $this->hasMany(DailyDelivery::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgers()
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function bottleTracking()
    {
        return $this->hasOne(BottleTracking::class);
    }

    public function groups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_group_pivot');
    }

    public function specialRates()
    {
        return $this->hasMany(CustomerSpecialRate::class);
    }

    public function bottleOpenings()
    {
        return $this->hasMany(CustomerBottleOpening::class);
    }
}
