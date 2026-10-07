<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_DAIRY_ADMIN = 'dairy_admin';
    const ROLE_BRANCH_MANAGER = 'branch_manager';
    const ROLE_COLLECTION_MANAGER = 'collection_manager';
    const ROLE_COLLECTION_OPERATOR = 'collection_operator';
    const ROLE_ACCOUNTANT = 'accountant';
    const ROLE_DELIVERY_MANAGER = 'delivery_manager';
    const ROLE_DELIVERY_BOY = 'delivery_boy';
    const ROLE_SALES_OPERATOR = 'sales_operator';
    const ROLE_INVENTORY_MANAGER = 'inventory_manager';
    const ROLE_FARMER = 'farmer';
    const ROLE_CUSTOMER = 'customer';
    const ROLE_SUPPORT_STAFF = 'support_staff';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'branch_id',
        'status',
        'avatar',
        'permissions',
        'two_factor_otp',
        'otp_expires_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'permissions' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function farmer()
    {
        return $this->hasOne(Farmer::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isDairyAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_DAIRY_ADMIN]);
    }

    public function isBranchManager(): bool
    {
        return $this->role === self::ROLE_BRANCH_MANAGER;
    }

    public function isCollectionStaff(): bool
    {
        return in_array($this->role, [self::ROLE_COLLECTION_MANAGER, self::ROLE_COLLECTION_OPERATOR]);
    }

    public function isDeliveryStaff(): bool
    {
        return in_array($this->role, [self::ROLE_DELIVERY_MANAGER, self::ROLE_DELIVERY_BOY]);
    }

    public function isAccountant(): bool
    {
        return $this->role === self::ROLE_ACCOUNTANT;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isFarmer(): bool
    {
        return $this->role === self::ROLE_FARMER;
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    public function getRoleBadgeClassAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
            'dairy_admin' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'branch_manager', 'collection_manager' => 'bg-blue-100 text-blue-800 border-blue-200',
            'collection_operator' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'accountant' => 'bg-amber-100 text-amber-800 border-amber-200',
            'delivery_manager', 'delivery_boy' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'sales_operator' => 'bg-teal-100 text-teal-800 border-teal-200',
            'farmer' => 'bg-lime-100 text-lime-800 border-lime-200',
            'customer' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
