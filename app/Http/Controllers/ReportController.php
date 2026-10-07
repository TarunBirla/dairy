<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MilkCollection;
use App\Models\Farmer;
use App\Models\Customer;
use App\Models\DailyDelivery;
use App\Models\PosOrder;
use App\Models\Expense;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function collections(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $collections = MilkCollection::with(['farmer', 'collectionCenter'])
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->latest('collection_date')
            ->paginate(25)
            ->withQueryString();

        $totalLiters = MilkCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('quantity_liters');
        $totalPayout = MilkCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('net_amount');
        $avgFat = MilkCollection::whereBetween('collection_date', [$startDate, $endDate])->avg('fat') ?? 0;
        $avgSnf = MilkCollection::whereBetween('collection_date', [$startDate, $endDate])->avg('snf') ?? 0;

        return view('reports.collections', compact('collections', 'startDate', 'endDate', 'totalLiters', 'totalPayout', 'avgFat', 'avgSnf'));
    }

    public function deliveries(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $deliveries = DailyDelivery::with(['customer', 'product', 'deliveryBoy'])
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->latest('delivery_date')
            ->paginate(25)
            ->withQueryString();

        $totalDelivered = DailyDelivery::whereBetween('delivery_date', [$startDate, $endDate])->where('status', 'delivered')->count();
        $totalSkipped = DailyDelivery::whereBetween('delivery_date', [$startDate, $endDate])->where('status', 'skipped')->count();
        $totalCash = DailyDelivery::whereBetween('delivery_date', [$startDate, $endDate])->sum('cash_collected');

        return view('reports.deliveries', compact('deliveries', 'startDate', 'endDate', 'totalDelivered', 'totalSkipped', 'totalCash'));
    }

    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        $orders = PosOrder::with('items.product')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $totalSales = PosOrder::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->sum('grand_total');

        return view('reports.sales', compact('orders', 'startDate', 'endDate', 'totalSales'));
    }

    public function dues(Request $request)
    {
        $customersWithDues = Customer::where('current_balance', '>', 0)->orderByDesc('current_balance')->paginate(20);
        $totalCustomerDues = Customer::sum('current_balance');
        $farmersWithPayable = Farmer::where('current_balance', '>', 0)->orderByDesc('current_balance')->paginate(20);
        $totalFarmerPayable = Farmer::sum('current_balance');

        return view('reports.dues', compact('customersWithDues', 'totalCustomerDues', 'farmersWithPayable', 'totalFarmerPayable'));
    }
}
