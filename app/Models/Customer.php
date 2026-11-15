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
        'billing_period',
        'account_type',
        'credit_limit',
        'stop_delivery_on_min_balance',
        'min_balance',
        'current_balance',
        'delivery_sequence',
        'delivery_instructions',
        'rate_chart_id',
        'status',
    ];

    public function rateChart()
    {
        return $this->belongsTo(RateChart::class);
    }

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'min_balance' => 'decimal:2',
        'stop_delivery_on_min_balance' => 'boolean',
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

    public function vacations()
    {
        return $this->hasMany(CustomerVacation::class);
    }

    /**
     * Determine if customer is on vacation for a given date and optional shift.
     */
    public function isOnVacation($date, $shift = null): bool
    {
        $d = \Carbon\Carbon::parse($date)->toDateString();

        return $this->vacations()
            ->where('status', 'active')
            ->whereDate('from_date', '<=', $d)
            ->whereDate('to_date', '>=', $d)
            ->when($shift && $shift !== 'both', function ($q) use ($shift) {
                $q->where(function ($sub) use ($shift) {
                    $sub->where('shift', 'full_day')
                        ->orWhere('shift', 'both')
                        ->orWhere('shift', $shift);
                });
            })
            ->exists();
    }

    /**
     * Get the active vacation covering a date.
     */
    public function getVacationOn($date, $shift = null)
    {
        $d = \Carbon\Carbon::parse($date)->toDateString();

        return $this->vacations()
            ->where('status', 'active')
            ->whereDate('from_date', '<=', $d)
            ->whereDate('to_date', '>=', $d)
            ->when($shift && $shift !== 'both', function ($q) use ($shift) {
                $q->where(function ($sub) use ($shift) {
                    $sub->where('shift', 'full_day')
                        ->orWhere('shift', 'both')
                        ->orWhere('shift', $shift);
                });
            })
            ->first();
    }
}
