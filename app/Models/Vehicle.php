<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'driver_id',
        'driver_name',
        'driver_phone',
        'capacity',
        'current_km',
        'per_km_rate',
        'assigned_route',
        'route_id',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'current_km' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function advances()
    {
        return $this->hasMany(VehicleAdvance::class);
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sales()
    {
        return $this->hasMany(MilkSale::class);
    }
}
