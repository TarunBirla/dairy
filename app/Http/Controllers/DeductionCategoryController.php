<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\DeductionCategory;
use App\Models\FarmerDeduction;
use App\Models\AuditLog;

class DeductionCategoryController extends Controller
{
    public function __construct()
    {
        DeductionCategory::ensureTableExistsAndSeeded();
    }

    /**
     * Display listing of deduction categories
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        $query = DeductionCategory::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_hi', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $categories = $query->latest('id')->paginate(15)->withQueryString();

        $totalCount = DeductionCategory::count();
        $activeCount = DeductionCategory::where('status', 'active')->count();
        $inactiveCount = DeductionCategory::where('status', 'inactive')->count();

        return view('deduction_categories.index', compact(
            'categories',
            'search',
            'status',
            'totalCount',
            'activeCount',
            'inactiveCount'
        ));
    }

    /**
     * Store a new deduction category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'name_hi' => 'nullable|string|max:150',
            'code' => 'nullable|string|max:100|unique:deduction_categories,code',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        if (empty($validated['code'])) {
            $baseCode = Str::slug($validated['name'], '_');
            $code = $baseCode;
            $counter = 1;
            while (DeductionCategory::where('code', $code)->exists()) {
                $code = $baseCode . '_' . $counter++;
            }
            $validated['code'] = $code;
        } else {
            $validated['code'] = Str::slug($validated['code'], '_');
        }

        $validated['created_by'] = Auth::id();

        $category = DeductionCategory::create($validated);
        AuditLog::log("Added Deduction Category {$category->name}", 'DeductionCategory', $category->id);

        return redirect()->route('deduction-categories.index')->with('success', "Deduction Category '{$category->name}' created successfully!");
    }

    /**
     * Update an existing deduction category
     */
    public function update(Request $request, DeductionCategory $deductionCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'name_hi' => 'nullable|string|max:150',
            'code' => "required|string|max:100|unique:deduction_categories,code,{$deductionCategory->id}",
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');

        $deductionCategory->update($validated);
        AuditLog::log("Updated Deduction Category {$deductionCategory->name}", 'DeductionCategory', $deductionCategory->id);

        return redirect()->route('deduction-categories.index')->with('success', "Deduction Category '{$deductionCategory->name}' updated successfully!");
    }

    /**
     * Delete a deduction category (with safety check)
     */
    public function destroy(DeductionCategory $deductionCategory)
    {
        // Check if deductions are linked to this category
        $linkedCount = FarmerDeduction::where('deduction_type', $deductionCategory->code)->count();
        if ($linkedCount > 0) {
            return redirect()->back()->with('error', "Cannot delete '{$deductionCategory->name}' because {$linkedCount} deduction records are linked to it. Please set its status to 'Inactive' instead.");
        }

        $name = $deductionCategory->name;
        $deductionCategory->delete();
        AuditLog::log("Deleted Deduction Category {$name}", 'DeductionCategory', $deductionCategory->id);

        return redirect()->route('deduction-categories.index')->with('success', "Deduction Category '{$name}' deleted successfully!");
    }
}
