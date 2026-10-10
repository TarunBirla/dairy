<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Graceful automatic schema check in case migration has not been triggered yet
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'product_for')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('product_for')->default('customer')->after('code');
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'image')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('image')->nullable()->after('in_stock');
            });
        }

        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('in_stock', true);
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('in_stock', false);
            }
        }

        if ($request->filled('product_for') && in_array($request->product_for, ['farmer', 'customer', 'both'])) {
            if ($request->product_for === 'farmer') {
                $query->whereIn('product_for', ['farmer', 'both']);
            } elseif ($request->product_for === 'customer') {
                $query->whereIn('product_for', ['customer', 'both']);
            } elseif ($request->product_for === 'both') {
                $query->where('product_for', 'both');
            }
        }

        $products = $query->orderBy('id', 'asc')->paginate(20)->withQueryString();

        $totalProducts = Product::count();
        $inStockCount = Product::where('in_stock', true)->count();
        $outOfStockCount = Product::where('in_stock', false)->count();

        if (Category::count() === 0) {
            Category::firstOrCreate(['name' => 'Fresh Dairy'], ['slug' => 'fresh-dairy', 'description' => 'Daily farm-fresh cow and buffalo milk products']);
            Category::firstOrCreate(['name' => 'Traditional Sweets & Mawa'], ['slug' => 'sweets-mawa', 'description' => 'Pure khoya, mawa and dairy sweets']);
            Category::firstOrCreate(['name' => 'Cattle Feed & Supplements'], ['slug' => 'cattle-feed', 'description' => 'Nutritional feed for dairy cattle']);
        }
        $categories = Category::orderBy('name', 'asc')->get();
        $nextCode = 'PRD-' . sprintf('%03d', (Product::max('id') ?? 0) + 1);

        return view('products.index', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'inStockCount' => $inStockCount,
            'outOfStockCount' => $outOfStockCount,
            'categories' => $categories,
            'nextCode' => $nextCode,
        ]);
    }

    public function create()
    {
        return redirect()->route('products.index', ['open_create' => 1]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code',
            'product_for' => 'nullable|in:farmer,customer,both',
            'category_id' => 'nullable|exists:categories,id',
            'product_type' => 'nullable|string|max:50',
            'unit' => 'required|string|max:50',
            'pack_size' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'subscription_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:3072',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['product_for'])) {
            $validated['product_for'] = 'customer';
        }

        if (empty($validated['product_type'])) {
            $validated['product_type'] = 'milk';
        }

        if ($request->hasFile('image')) {
            $destPath = public_path('uploads/products');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move($destPath, $filename);
            $validated['image'] = 'uploads/products/' . $filename;
        }

        $validated['in_stock'] = ($validated['current_stock'] ?? 0) > 0;

        $product = Product::create($validated);

        if (($validated['current_stock'] ?? 0) > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'purchase_inward',
                'quantity' => $validated['current_stock'],
                'unit_cost' => $validated['cost_price'] ?? 0,
                'balance_after' => $validated['current_stock'],
                'recorded_by' => Auth::id(),
                'notes' => 'Opening Stock entry',
            ]);
        }

        AuditLog::log('Created Product', 'Product', $product->id);

        return redirect()->route('products.index')->with('success', "Product {$product->name} created successfully!");
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'product_for' => 'nullable|in:farmer,customer,both',
            'category_id' => 'nullable|exists:categories,id',
            'product_type' => 'nullable|string|max:50',
            'unit' => 'required|string|max:50',
            'pack_size' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'subscription_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:3072',
            'description' => 'nullable|string',
        ]);

        if (empty($validated['product_for'])) {
            $validated['product_for'] = $product->product_for ?: 'customer';
        }

        if (empty($validated['product_type'])) {
            $validated['product_type'] = $product->product_type ?: 'milk';
        }

        if ($request->hasFile('image')) {
            $destPath = public_path('uploads/products');
            if (!file_exists($destPath)) {
                @mkdir($destPath, 0755, true);
            }
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move($destPath, $filename);
            $validated['image'] = 'uploads/products/' . $filename;
        } elseif ($request->boolean('remove_image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }
            $validated['image'] = null;
        }

        $product->update($validated);
        AuditLog::log('Updated Product', 'Product', $product->id);

        return redirect()->route('products.index')->with('success', "Product {$product->name} updated!");
    }

    public function storeCategoryAjax(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::firstOrCreate(
            ['name' => trim($validated['name'])],
            [
                'slug' => \Illuminate\Support\Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Category saved successfully'
        ]);
    }

    public function toggleStock(Product $product)
    {
        $product->in_stock = !$product->in_stock;
        $product->save();

        AuditLog::log('Toggled Stock Availability', 'Product', $product->id, ['in_stock' => $product->in_stock]);

        return response()->json([
            'success' => true,
            'in_stock' => $product->in_stock,
            'message' => "{$product->name} status changed to " . ($product->in_stock ? 'In Stock' : 'Out of Stock'),
        ]);
    }

    public function quickStockAdd(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.1',
            'notes' => 'nullable|string',
        ]);

        $newStock = $product->current_stock + $validated['quantity'];
        $product->update([
            'current_stock' => $newStock,
            'in_stock' => $newStock > 0,
        ]);

        InventoryTransaction::create([
            'product_id' => $product->id,
            'transaction_type' => 'production_inward',
            'quantity' => $validated['quantity'],
            'balance_after' => $newStock,
            'recorded_by' => Auth::id(),
            'notes' => $validated['notes'] ?? 'Quick stock top-up from catalog',
        ]);

        AuditLog::log('Quick Stock Added', 'Product', $product->id, ['added' => $validated['quantity']]);

        return back()->with('success', "Added {$validated['quantity']} {$product->unit} to {$product->name}! New stock: {$newStock} {$product->unit}");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();
        AuditLog::log('Deleted Product', 'Product', $product->id);

        return redirect()->route('products.index')->with('success', "Product {$name} deleted.");
    }
}
