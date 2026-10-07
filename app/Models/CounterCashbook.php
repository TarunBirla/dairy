<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounterCashbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'counter_id',
        'operator_id',
        'entry_date',
        'opening_cash',
        'cash_sales',
        'cash_expenses',
        'closing_cash',
        'actual_counted_cash',
        'variance',
        'status',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'opening_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'cash_expenses' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'actual_counted_cash' => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    public function counter()
    {
        return $this->belongsTo(PosCounter::class, 'counter_id');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
