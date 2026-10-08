<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkDispatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'challan_date' => 'date',
        'dispatch_date' => 'date',
        'purchase_qty' => 'decimal:2',
        'quantity_ltr' => 'decimal:2',
        'total_milk_quantity' => 'decimal:2',
        'prev_balance' => 'decimal:2',
        'balance' => 'decimal:2',
        'loss' => 'decimal:2',
        'fat' => 'decimal:2',
        'snf' => 'decimal:2',
        'clr' => 'decimal:2',
        'temperature' => 'decimal:1',
        'acidity' => 'decimal:2',
        'amount' => 'decimal:2',
        'headload_kms' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
