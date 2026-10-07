<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\AuditLog;

class CategoryController extends Controller
{
    private function ensureDefaultCategories(): void
    {
        if (Category::count() === 0) {
            Category::firstOrCreate(['name' => 'Fresh Dairy'], ['slug' => 'fresh-dairy', 'description' => 'Daily farm-fresh cow and buffalo milk products']);
            Category::firstOrCreate(['name' => 'Traditional Sweets & Mawa'], ['slug' => 'sweets-mawa', 'description' => 'Pure khoya, mawa and dairy sweets']);
            Category::firstOrCreate(['name' => 'Cattle Feed & Supplements'], ['slug' => 'cattle-feed', 'description' => 'Nutritional feed for dairy cattle']);
        }
    }

    public function index(Request $request)
    {
        $this->ensureDefaultCategories();

        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $totalCategories = Category::count();
        $totalProductsCategorized = Product::whereNotNull('category_id')->count();

        return view('products.categories.index', compact('categories', 'totalCategories', 'totalProductsCategorized'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        AuditLog::log('Created Product Category', 'Category', $category->id);

        return redirect()->route('products.categories.index')->with('success', "Category '{$category->name}' created successfully!");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        AuditLog::log('Updated Product Category', 'Category', $category->id);

        return redirect()->route('products.categories.index')->with('success', "Category '{$category->name}' updated successfully!");
    }

    public function destroy(Category $category)
    {
        $productsCount = $category->products()->count();
        if ($productsCount > 0) {
            return redirect()->route('products.categories.index')->with('error', "Cannot delete category '{$category->name}' because {$productsCount} products are assigned to it. Please reassign or delete those products first.");
        }

        $name = $category->name;
        $category->delete();
        AuditLog::log('Deleted Product Category', 'Category', $category->id);

        return redirect()->route('products.categories.index')->with('success', "Category '{$name}' deleted successfully!");
    }

    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $name = trim($validated['name']);
        $category = Category::firstOrCreate(
            ['name' => $name],
            [
                'slug' => Str::slug($name),
                'description' => $validated['description'] ?? null,
            ]
        );

        AuditLog::log('Created Category via Modal', 'Category', $category->id);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Category created successfully'
        ]);
    }

    public function listAjax(Request $request)
    {
        $this->ensureDefaultCategories();

        $query = Category::query();
        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->q}%");
        }
        $categories = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'results' => $categories->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'text' => $cat->name
                ];
            })
        ]);
    }
}
