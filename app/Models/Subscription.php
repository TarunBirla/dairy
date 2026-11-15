<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_code',
        'customer_id',
        'product_id',
        'route_id',
        'quantity',
        'frequency',
        'custom_days',
        'shift',
        'unit_price',
        'start_date',
        'end_date',
        'status',
        'pause_from',
        'pause_until',
    ];

    protected $casts = [
        'custom_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'pause_from' => 'date',
        'pause_until' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class);
    }

    public function isPausedOn(\DateTimeInterface $date): bool
    {
        if ($this->status === 'paused') {
            return true;
        }

        if ($this->pause_from && $this->pause_until) {
            $formatted = $date->format('Y-m-d');
            return $formatted >= $this->pause_from->format('Y-m-d') && $formatted <= $this->pause_until->format('Y-m-d');
        }

        return false;
    }

    public function shouldDeliverOn(\DateTimeInterface $date, ?string $shift = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->isPausedOn($date)) {
            return false;
        }

        $dateCarbon = \Carbon\Carbon::parse($date)->startOfDay();
        $startCarbon = \Carbon\Carbon::parse($this->start_date)->startOfDay();

        if ($dateCarbon->lt($startCarbon)) {
            return false;
        }

        if ($this->end_date) {
            $endCarbon = \Carbon\Carbon::parse($this->end_date)->startOfDay();
            if ($dateCarbon->gt($endCarbon)) {
                return false;
            }
        }

        if ($shift && $this->shift !== 'both' && $this->shift !== $shift) {
            return false;
        }

        $freq = $this->frequency;
        if ($freq === 'daily') {
            return true;
        }

        if ($freq === 'alternate_day' || $freq === 'alternate_days') {
            $diffDays = $startCarbon->diffInDays($dateCarbon);
            return ($diffDays % 2) === 0;
        }

        if ($freq === 'every_3rd_day' || $freq === 'every_3rd_days') {
            $diffDays = $startCarbon->diffInDays($dateCarbon);
            return ($diffDays % 3) === 0;
        }

        if ($freq === 'weekdays' || $freq === 'custom_days') {
            $customDays = $this->custom_days;
            if (is_array($customDays) && count($customDays) > 0) {
                $dayShort = strtolower($dateCarbon->format('D')); // 'mon', 'tue', etc.
                $dayFull = strtolower($dateCarbon->format('l')); // 'monday', etc.
                $dayNum = (int)$dateCarbon->format('N'); // 1 (Mon) - 7 (Sun)
                return in_array($dayShort, $customDays) || in_array($dayFull, $customDays) || in_array($dayNum, $customDays);
            }
            return !$dateCarbon->isWeekend();
        }

        return true;
    }
}
