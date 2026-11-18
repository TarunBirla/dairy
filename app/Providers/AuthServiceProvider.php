<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin has unrestricted access to all capabilities
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });

        // 1. View Wallet
        Gate::define('wallet.view', function ($user, $farmer = null) {
            if (in_array($user->role, ['super_admin', 'dairy_admin', 'accountant', 'branch_manager'])) {
                return true;
            }
            if ($user->role === 'farmer' && $farmer && isset($farmer->user_id) && $user->id === $farmer->user_id) {
                return true;
            }
            return false;
        });

        // 2. Add Wallet Balance
        Gate::define('wallet.add', function ($user) {
            return in_array($user->role, ['super_admin', 'dairy_admin', 'accountant', 'branch_manager']);
        });

        // 3. Edit Wallet Balance
        Gate::define('wallet.edit', function ($user) {
            return in_array($user->role, ['super_admin', 'dairy_admin', 'accountant']);
        });

        // 4. Deduct Wallet Balance
        Gate::define('wallet.deduct', function ($user) {
            return in_array($user->role, ['super_admin', 'dairy_admin', 'accountant']);
        });

        // 5. View Transaction History
        Gate::define('wallet.history', function ($user, $farmer = null) {
            if (in_array($user->role, ['super_admin', 'dairy_admin', 'accountant', 'branch_manager'])) {
                return true;
            }
            if ($user->role === 'farmer' && $farmer && isset($farmer->user_id) && $user->id === $farmer->user_id) {
                return true;
            }
            return false;
        });

        // 6. Add Missing Milk Entries directly to Invoice
        Gate::define('invoice.add_milk_entry', function ($user) {
            return in_array($user->role, ['super_admin', 'dairy_admin', 'accountant', 'branch_manager', 'collection_manager', 'collection_operator']);
        });

        // 7. Generate Invoice
        Gate::define('invoice.generate', function ($user) {
            return in_array($user->role, ['super_admin', 'dairy_admin', 'accountant', 'branch_manager']);
        });

        // 8. Print / Download Invoice
        Gate::define('invoice.print', function ($user, $farmer = null) {
            if (in_array($user->role, ['super_admin', 'dairy_admin', 'accountant', 'branch_manager', 'collection_manager'])) {
                return true;
            }
            if ($user->role === 'farmer' && $farmer && isset($farmer->user_id) && $user->id === $farmer->user_id) {
                return true;
            }
            return true;
        });
    }
}
