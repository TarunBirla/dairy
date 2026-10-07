<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerGroup;
use App\Models\Customer;
use App\Models\AuditLog;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::withCount('customers')->latest()->get();
        return view('customers.groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:30',
        ]);

        $group = CustomerGroup::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . rand(100, 999),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? '#10B981',
        ]);

        AuditLog::log('Created Customer Group', 'CustomerGroup', $group->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'group' => $group]);
        }

        return back()->with('success', "Customer Group {$group->name} created successfully!");
    }

    public function destroy(CustomerGroup $group)
    {
        $group->customers()->detach();
        $group->delete();
        AuditLog::log('Deleted Customer Group', 'CustomerGroup', $group->id);

        return back()->with('success', "Customer Group deleted.");
    }
}
