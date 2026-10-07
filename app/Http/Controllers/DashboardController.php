<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MilkCollection;
use App\Models\DailyDelivery;
use App\Models\PosOrder;
use App\Models\Customer;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Expense;
use App\Models\Subscription;
use App\Models\DeliveryRoute;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 1. Role-based redirected experience if requested
        if ($user->isDeliveryStaff() && $user->role === 'delivery_boy') {
            return redirect()->route('delivery.boy-app');
        }

        if ($user->isFarmer()) {
            $farmer = Farmer::where('user_id', $user->id)->first();
            if ($farmer) {
                $myCollections = MilkCollection::where('farmer_id', $farmer->id)->latest()->take(10)->get();
                $myLedgers = $farmer->ledgers()->latest()->take(10)->get();
                return view('dashboard.farmer', compact('user', 'farmer', 'myCollections', 'myLedgers'));
            }
        }

        if ($user->isCustomer()) {
            $customer = Customer::where('user_id', $user->id)->first();
            if ($customer) {
                $mySubscriptions = Subscription::where('customer_id', $customer->id)->with('product')->get();
                $todayDelivery = DailyDelivery::where('customer_id', $customer->id)->whereDate('delivery_date', $today)->first();
                $myInvoices = $customer->invoices()->latest()->take(5)->get();
                return view('dashboard.customer', compact('user', 'customer', 'mySubscriptions', 'todayDelivery', 'myInvoices'));
            }
        }

        // 2. Full Executive & Management Dashboard
        $todayCollections = MilkCollection::whereDate('collection_date', $today)->get();
        $todayCollectionLiters = $todayCollections->sum('quantity_liters');
        $todayCollectionAmount = $todayCollections->sum('net_amount');
        $todayMorningLiters = $todayCollections->where('shift', 'morning')->sum('quantity_liters');
        $todayEveningLiters = $todayCollections->where('shift', 'evening')->sum('quantity_liters');
        $todayFarmerCount = $todayCollections->pluck('farmer_id')->unique()->count();

        $todayDeliveries = DailyDelivery::whereDate('delivery_date', $today)->get();
        $deliveryTotal = $todayDeliveries->count();
        $deliveryCompleted = $todayDeliveries->where('status', 'delivered')->count();
        $deliveryPending = $todayDeliveries->where('status', 'pending')->count();
        $deliverySkipped = $todayDeliveries->where('status', 'skipped')->count();

        $todaySales = PosOrder::whereDate('created_at', $today)->sum('grand_total');
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');

        $totalCustomers = Customer::where('status', 'active')->count();
        $totalFarmers = Farmer::where('status', 'active')->count();
        $customerDues = Customer::sum('current_balance');
        $farmerPayable = Farmer::sum('current_balance');

        $totalProducts = Product::count();
        $inStockProducts = Product::where('in_stock', true)->count();
        $outOfStockProducts = Product::where('in_stock', false)->count();

        $recentCollections = MilkCollection::with(['farmer', 'collectionCenter'])->latest()->take(6)->get();
        $recentDeliveries = DailyDelivery::with(['customer', 'product', 'deliveryBoy'])->latest()->take(6)->get();
        $lowStockProducts = Product::where('current_stock', '<=', 5)->get();

        return view('dashboard.index', compact(
            'todayCollectionLiters',
            'todayCollectionAmount',
            'todayMorningLiters',
            'todayEveningLiters',
            'todayFarmerCount',
            'deliveryTotal',
            'deliveryCompleted',
            'deliveryPending',
            'deliverySkipped',
            'todaySales',
            'todayExpenses',
            'totalCustomers',
            'totalFarmers',
            'customerDues',
            'farmerPayable',
            'totalProducts',
            'inStockProducts',
            'outOfStockProducts',
            'recentCollections',
            'recentDeliveries',
            'lowStockProducts'
        ));
    }
}
