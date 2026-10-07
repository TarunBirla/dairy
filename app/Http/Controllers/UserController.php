<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Branch;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('branch');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $branches = Branch::where('status', 'active')->get();

        return view('users.index', compact('users', 'branches'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        AuditLog::log('Created User', 'User', $user->id, ['role' => $user->role]);

        return redirect()->route('users.index')->with('success', "User {$user->name} created!");
    }

    public function matrix()
    {
        // Role & Permission Matrix (Section 4 in Specification Document)
        $matrix = [
            'Dashboard' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'View', 'collection_operator' => 'View', 'accountant' => 'View', 'delivery_manager' => 'View', 'delivery_boy' => 'View', 'customer' => 'View'],
            'Farmers' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Center', 'collection_operator' => 'View', 'accountant' => 'None', 'delivery_manager' => 'View', 'delivery_boy' => 'None', 'customer' => 'None'],
            'Milk Collection' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Full', 'collection_operator' => 'Create/Edit', 'accountant' => 'None', 'delivery_manager' => 'View', 'delivery_boy' => 'None', 'customer' => 'None'],
            'Customers' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Full', 'collection_operator' => 'View', 'accountant' => 'None', 'delivery_manager' => 'View', 'delivery_boy' => 'None', 'customer' => 'Self'],
            'Subscriptions' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'View', 'collection_operator' => 'None', 'accountant' => 'None', 'delivery_manager' => 'View', 'delivery_boy' => 'None', 'customer' => 'Self'],
            'Delivery' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'View', 'collection_operator' => 'None', 'accountant' => 'None', 'delivery_manager' => 'Manage', 'delivery_boy' => 'Execute', 'customer' => 'Self'],
            'POS Counter' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'View', 'collection_operator' => 'None', 'accountant' => 'None', 'delivery_manager' => 'View', 'delivery_boy' => 'None', 'customer' => 'Self'],
            'Inventory' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Center', 'collection_operator' => 'None', 'accountant' => 'Manage', 'delivery_manager' => 'View', 'delivery_boy' => 'None', 'customer' => 'Limited'],
            'Payments/Accounts' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'View', 'collection_operator' => 'Full', 'accountant' => 'None', 'delivery_manager' => 'Collect', 'delivery_boy' => 'Collect', 'customer' => 'Self'],
            'Reports' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Center', 'collection_operator' => 'Finance', 'accountant' => 'Inventory', 'delivery_manager' => 'Delivery', 'delivery_boy' => 'Own', 'customer' => 'Own'],
            'Users/Roles' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Limited', 'collection_operator' => 'None', 'accountant' => 'None', 'delivery_manager' => 'None', 'delivery_boy' => 'None', 'customer' => 'None'],
            'Settings' => ['super_admin' => 'Full', 'dairy_admin' => 'Full', 'branch_manager' => 'Center', 'collection_operator' => 'None', 'accountant' => 'None', 'delivery_manager' => 'None', 'delivery_boy' => 'None', 'customer' => 'Self'],
        ];

        return view('users.matrix', compact('matrix'));
    }
}
