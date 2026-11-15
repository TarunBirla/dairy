<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerVacation;
use App\Models\DailyDelivery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CustomerVacationController extends Controller
{
    private function ensureSchemaMigrated()
    {
        try {
            if (!Schema::hasTable('customer_vacations')) {
                Schema::create('customer_vacations', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                    $table->date('from_date');
                    $table->date('to_date');
                    $table->string('shift', 20)->default('full_day');
                    $table->string('reason')->nullable();
                    $table->string('added_by', 50)->default('admin');
                    $table->string('status', 20)->default('active');
                    $table->timestamps();

                    $table->index(['customer_id', 'status']);
                    $table->index(['from_date', 'to_date']);
                });
            }
        } catch (\Throwable $e) {
            // Ignore if handled by migrations
        }
    }

    public function index(Request $request)
    {
        $this->ensureSchemaMigrated();

        $today = Carbon::today();
        $quickRange = $request->get('quick_range', 'today');

        // Determine date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Carbon::parse($request->from_date);
            $toDate = Carbon::parse($request->to_date);
        } else {
            switch ($quickRange) {
                case 'tomorrow':
                    $fromDate = $today->copy()->addDay();
                    $toDate = $today->copy()->addDay();
                    break;
                case 'this_week':
                    $fromDate = $today->copy()->startOfWeek();
                    $toDate = $today->copy()->endOfWeek();
                    break;
                case 'this_month':
                    $fromDate = $today->copy()->startOfMonth();
                    $toDate = $today->copy()->endOfMonth();
                    break;
                case 'next_30_days':
                    $fromDate = $today->copy();
                    $toDate = $today->copy()->addDays(30);
                    break;
                case 'today':
                default:
                    $fromDate = $today->copy();
                    $toDate = $today->copy();
                    break;
            }
        }

        $customerId = $request->get('customer_id');

        // Base query for current filter
        $query = CustomerVacation::with('customer')
            ->where('status', 'active');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $query->where(function ($q) use ($fromDate, $toDate) {
            $f = $fromDate->toDateString();
            $t = $toDate->toDateString();
            $q->whereBetween('from_date', [$f, $t])
              ->orWhereBetween('to_date', [$f, $t])
              ->orWhere(function ($sub) use ($f, $t) {
                  $sub->where('from_date', '<=', $f)->where('to_date', '>=', $t);
              });
        });

        $vacations = $query->orderBy('from_date', 'desc')->paginate(20)->withQueryString();

        // KPI Metrics
        $totalDays = 0;
        $uniqueCustomerIds = [];
        $activeRangeVacations = (clone $query)->get();

        foreach ($activeRangeVacations as $v) {
            $totalDays += $v->days_count;
            $uniqueCustomerIds[] = $v->customer_id;
        }

        $onVacationToday = CustomerVacation::where('status', 'active')
            ->whereDate('from_date', '<=', $today->toDateString())
            ->whereDate('to_date', '>=', $today->toDateString())
            ->distinct('customer_id')
            ->count('customer_id');

        $upcomingCount = CustomerVacation::where('status', 'active')
            ->whereDate('from_date', '>', $today->toDateString())
            ->count();

        // Calendar Month calculation
        $calMonth = $request->filled('cal_month') ? Carbon::parse($request->cal_month) : $today->copy();
        $startOfMonth = $calMonth->copy()->startOfMonth();
        $endOfMonth = $calMonth->copy()->endOfMonth();

        // Fetch all active vacations that touch this calendar month
        $monthVacations = CustomerVacation::where('status', 'active')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $f = $startOfMonth->toDateString();
                $t = $endOfMonth->toDateString();
                $q->whereBetween('from_date', [$f, $t])
                  ->orWhereBetween('to_date', [$f, $t])
                  ->orWhere(function ($sub) use ($f, $t) {
                      $sub->where('from_date', '<=', $f)->where('to_date', '>=', $t);
                  });
            })
            ->get();

        // Map dates in month to vacation counts
        $datesWithVacation = [];
        foreach ($monthVacations as $mv) {
            $start = Carbon::parse($mv->from_date);
            $end = Carbon::parse($mv->to_date);

            $curr = $start->copy();
            while ($curr->lte($end)) {
                $ds = $curr->toDateString();
                if (!isset($datesWithVacation[$ds])) {
                    $datesWithVacation[$ds] = 0;
                }
                $datesWithVacation[$ds]++;
                $curr->addDay();
            }
        }

        $allCustomers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('vacations.index', compact(
            'vacations',
            'totalDays',
            'uniqueCustomerIds',
            'onVacationToday',
            'upcomingCount',
            'fromDate',
            'toDate',
            'quickRange',
            'customerId',
            'allCustomers',
            'today',
            'calMonth',
            'datesWithVacation'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureSchemaMigrated();

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'shift' => 'required|in:morning,evening,full_day',
            'reason' => 'nullable|string|max:255',
            'added_by' => 'nullable|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $addedBy = $request->input('added_by', Auth::user()?->role === 'customer' ? 'Customer' : 'Admin');

        $vacation = CustomerVacation::create([
            'customer_id' => $customer->id,
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'shift' => $validated['shift'],
            'reason' => $validated['reason'] ?? 'Not available / Leave',
            'added_by' => $addedBy,
            'status' => 'active',
        ]);

        // Auto-skip pending deliveries for this customer within vacation range
        $deliveriesQuery = DailyDelivery::where('customer_id', $customer->id)
            ->whereBetween('delivery_date', [$validated['from_date'], $validated['to_date']])
            ->where('status', 'pending');

        if ($validated['shift'] !== 'full_day') {
            $deliveriesQuery->where('shift', $validated['shift']);
        }

        $skippedCount = $deliveriesQuery->update([
            'status' => 'skipped',
            'failure_reason' => 'On Vacation: ' . ($validated['reason'] ?: 'Customer on leave'),
        ]);

        AuditLog::log('Scheduled Customer Vacation', 'CustomerVacation', $vacation->id, [
            'customer' => $customer->name,
            'dates' => "{$validated['from_date']} to {$validated['to_date']}",
            'shift' => $validated['shift'],
            'skipped_deliveries' => $skippedCount,
        ]);

        $skipMsg = $skippedCount > 0 ? " ({$skippedCount} scheduled delivery stops automatically skipped)" : "";

        return back()->with('success', "Vacation scheduled for {$customer->name} from {$validated['from_date']} to {$validated['to_date']}{$skipMsg}.");
    }

    public function destroy(CustomerVacation $vacation)
    {
        $customer = $vacation->customer;

        // Restore pending deliveries if within today/future and was skipped due to vacation
        DailyDelivery::where('customer_id', $vacation->customer_id)
            ->whereBetween('delivery_date', [$vacation->from_date->toDateString(), $vacation->to_date->toDateString()])
            ->where('status', 'skipped')
            ->where('delivery_date', '>=', now()->toDateString())
            ->where('failure_reason', 'like', '%On Vacation%')
            ->update([
                'status' => 'pending',
                'failure_reason' => null,
            ]);

        $vacation->delete();

        AuditLog::log('Cancelled Customer Vacation', 'CustomerVacation', $vacation->id);

        return back()->with('success', "Vacation for {$customer->name} cancelled and future delivery stops resumed.");
    }

    public function cancelRange(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $query = CustomerVacation::where('status', 'active')
            ->whereBetween('from_date', [$validated['from_date'], $validated['to_date']]);

        if (!empty($validated['customer_id'])) {
            $query->where('customer_id', $validated['customer_id']);
        }

        $vacations = $query->get();
        foreach ($vacations as $v) {
            $this->destroy($v);
        }

        return back()->with('success', "Cancelled {$vacations->count()} vacation records in selected range.");
    }
}
