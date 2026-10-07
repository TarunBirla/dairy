<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'color'];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_group_pivot');
    }
}
