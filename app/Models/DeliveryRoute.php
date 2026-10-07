<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'area_name',
        'delivery_boy_id',
        'description',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function deliveryBoy()
    {
        return $this->belongsTo(User::class, 'delivery_boy_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'route_id');
    }

    public function dailyDeliveries()
    {
        return $this->hasMany(DailyDelivery::class, 'route_id');
    }
}
