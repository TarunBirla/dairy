<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_code',
        'name',
        'phone',
        'license_number',
        'aadhaar_card_no',
        'pan_card_no',
        'account_holder',
        'account_number',
        'bank_name',
        'ifsc_code',
        'bank_branch',
        'status',
        'address',
        'created_by',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function advances()
    {
        return $this->hasMany(VehicleAdvance::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
