<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'address',
        'city',
        'manager_id',
        'status',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function collectionCenters()
    {
        return $this->hasMany(CollectionCenter::class);
    }

    public function posCounters()
    {
        return $this->hasMany(PosCounter::class);
    }

    public function deliveryRoutes()
    {
        return $this->hasMany(DeliveryRoute::class);
    }
}
