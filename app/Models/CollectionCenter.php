<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'location',
        'morning_shift_time',
        'evening_shift_time',
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

    public function collections()
    {
        return $this->hasMany(MilkCollection::class);
    }

    public function farmers()
    {
        return $this->hasMany(Farmer::class);
    }
}
