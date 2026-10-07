<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'operator_id',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function orders()
    {
        return $this->hasMany(PosOrder::class, 'counter_id');
    }

    public function cashbooks()
    {
        return $this->hasMany(CounterCashbook::class, 'counter_id');
    }
}
