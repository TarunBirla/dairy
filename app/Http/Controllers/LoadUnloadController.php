<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductLoad;
use App\Models\ProductLoadItem;
use App\Models\Product;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;

class LoadUnloadController extends Controller
{
    public function index(Request $request)
    {
        $loadType = $request->get('type', 'counter_sale');
        if (!in_array($loadType, ['counter_sale', 'delivery_sale'])) {
            $loadType = 'counter_sale';
        }

        $query = ProductLoad::with(['items', 'deliveryPerson'])->where('load_type', $loadType);

        // Search by delivery person name, phone, or product name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('delivery_person_name', 'like', "%{$search}%")
                  ->orWhere('delivery_person_phone', 'like', "%{$search}%")
                  ->orWhereHas('deliveryPerson', function ($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function ($itemQ) use ($search) {
                      $itemQ->where('product_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        if ($request->filled('shift') && $request->shift !== 'all') {
            $query->where('shift', $request->shift);
        }

        $records = $query->latest('id')->paginate(15)->withQueryString();

        // Delivery Persons
        $deliveryPersons = User::whereIn('role', [User::ROLE_DELIVERY_BOY, 'staff', 'sales_operator', 'super_admin'])
            ->orderBy('name')
            ->get();

        if ($deliveryPersons->isEmpty()) {
            $deliveryPersons = User::orderBy('name')->take(20)->get();
        }

        // Products for dropdown
        $products = Product::where('status', 'active')
            ->orWhereNull('status')
            ->orderBy('name')
            ->get();

        return view('load-unload.index', compact(
            'loadType',
            'records',
            'deliveryPersons',
            'products'
        ));
    }

    public function counterSale(Request $request)
    {
        $request->merge(['type' => 'counter_sale']);
        return $this->index($request);
    }

    public function deliverySale(Request $request)
    {
        $request->merge(['type' => 'delivery_sale']);
        return $this->index($request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'load_type' => 'required|in:counter_sale,delivery_sale',
            'date' => 'required|date',
            'delivery_person' => 'required',
            'shift' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'remark' => 'nullable|string|max:500',
        ]);

        $deliveryPersonId = null;
        $deliveryPersonName = '';
        $deliveryPersonPhone = '';

        if (is_numeric($request->delivery_person)) {
            $user = User::find($request->delivery_person);
            if ($user) {
                $deliveryPersonId = $user->id;
                $deliveryPersonName = $user->name;
                $deliveryPersonPhone = $user->phone ?? '';
            } else {
                $deliveryPersonName = (string)$request->delivery_person;
            }
        } else {
            // Might be formatted as "Name (Phone)"
            $val = trim($request->delivery_person);
            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $val, $matches)) {
                $deliveryPersonName = trim($matches[1]);
                $deliveryPersonPhone = trim($matches[2]);
            } else {
                $deliveryPersonName = $val;
            }
        }

        $load = ProductLoad::create([
            'load_type' => $validated['load_type'],
            'date' => $validated['date'],
            'delivery_person_id' => $deliveryPersonId,
            'delivery_person_name' => $deliveryPersonName,
            'delivery_person_phone' => $deliveryPersonPhone,
            'shift' => $validated['shift'],
            'remark' => $request->remark,
            'created_by' => auth()->id(),
        ]);

        foreach ($request->products as $item) {
            $prodId = null;
            if (!empty($item['product_id']) && is_numeric($item['product_id'])) {
                $prodId = $item['product_id'];
            } else {
                $matched = Product::where('name', $item['name'])->first();
                if ($matched) {
                    $prodId = $matched->id;
                }
            }

            ProductLoadItem::create([
                'product_load_id' => $load->id,
                'product_id' => $prodId,
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
            ]);
        }

        $typeName = $validated['load_type'] === 'counter_sale' ? 'Counter Sale' : 'Delivery Sale';
        AuditLog::log("Created {$typeName} Load", 'ProductLoad', $load->id);

        return redirect()->route('load-unload.index', ['type' => $validated['load_type']])
            ->with('success', "{$typeName} record saved successfully!");
    }

    public function show(ProductLoad $load)
    {
        return response()->json($load->load(['items', 'deliveryPerson']));
    }

    public function destroy(ProductLoad $load)
    {
        $type = $load->load_type;
        $typeName = $type === 'counter_sale' ? 'Counter Sale' : 'Delivery Sale';
        $load->delete();

        AuditLog::log("Deleted {$typeName} Load", 'ProductLoad', $load->id);

        return redirect()->route('load-unload.index', ['type' => $type])
            ->with('success', "{$typeName} record deleted successfully.");
    }
}
