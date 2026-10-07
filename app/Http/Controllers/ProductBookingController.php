<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductBooking;
use App\Models\Product;
use App\Models\Customer;
use App\Models\AuditLog;
use Carbon\Carbon;

class ProductBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = ProductBooking::with(['product', 'customer'])->latest()->paginate(15);
        $totalBookings = ProductBooking::count();
        $confirmedCount = ProductBooking::where('status', 'confirmed')->count();
        $products = Product::where('status', 'active')->get();
        $customers = Customer::where('status', 'active')->get();

        return view('bookings.index', compact('bookings', 'totalBookings', 'confirmedCount', 'products', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.5',
            'unit_price' => 'required|numeric|min:0',
            'advance_paid' => 'nullable|numeric|min:0',
            'delivery_date' => 'required|date',
            'shift' => 'required|in:morning,evening,any',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $bookingNumber = 'BKG-' . date('Ymd') . '-' . sprintf('%03d', ProductBooking::whereDate('created_at', Carbon::today())->count() + 1);
        $total = $validated['quantity'] * $validated['unit_price'];

        $booking = ProductBooking::create(array_merge($validated, [
            'booking_number' => $bookingNumber,
            'booking_date' => Carbon::today(),
            'total_amount' => $total,
            'status' => 'confirmed',
        ]));

        AuditLog::log('Created Product Booking', 'ProductBooking', $booking->id);

        return back()->with('success', "Pre-order booking {$bookingNumber} created successfully!");
    }
}
