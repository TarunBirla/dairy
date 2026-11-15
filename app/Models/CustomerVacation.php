<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerVacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'from_date',
        'to_date',
        'shift',
        'reason',
        'added_by',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDate($query, $date)
    {
        $d = Carbon::parse($date)->toDateString();
        return $query->where('status', 'active')
            ->whereDate('from_date', '<=', $d)
            ->whereDate('to_date', '>=', $d);
    }

    public function scopeForRange($query, $from, $to)
    {
        $f = Carbon::parse($from)->toDateString();
        $t = Carbon::parse($to)->toDateString();
        return $query->where('status', 'active')
            ->where(function ($q) use ($f, $t) {
                $q->whereBetween('from_date', [$f, $t])
                  ->orWhereBetween('to_date', [$f, $t])
                  ->orWhere(function ($sub) use ($f, $t) {
                      $sub->where('from_date', '<=', $f)->where('to_date', '>=', $t);
                  });
            });
    }

    /**
     * Check if this vacation covers a specific date and optional shift.
     */
    public function coversDate($date, $shift = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $d = Carbon::parse($date)->startOfDay();
        $start = Carbon::parse($this->from_date)->startOfDay();
        $end = Carbon::parse($this->to_date)->startOfDay();

        if ($d->lt($start) || $d->gt($end)) {
            return false;
        }

        if (!$shift || $this->shift === 'full_day' || $this->shift === 'both') {
            return true;
        }

        return $this->shift === $shift;
    }

    /**
     * Number of days covered by this vacation.
     */
    public function getDaysCountAttribute(): int
    {
        if (!$this->from_date || !$this->to_date) {
            return 1;
        }
        return (int)Carbon::parse($this->from_date)->diffInDays(Carbon::parse($this->to_date)) + 1;
    }
}
