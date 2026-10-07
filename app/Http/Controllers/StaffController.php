<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\StaffAttendance;
use App\Models\StaffAdvance;
use App\Models\StaffSalary;
use App\Models\AuditLog;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staffMembers = User::whereNotIn('role', ['customer', 'farmer'])->with('branch')->latest()->paginate(15);
        $totalStaff = User::whereNotIn('role', ['customer', 'farmer'])->count();
        $todayPresent = StaffAttendance::whereDate('date', Carbon::today())->where('status', 'present')->count();
        $pendingAdvances = StaffAdvance::where('status', 'pending')->sum('amount');

        return view('staff.index', compact('staffMembers', 'totalStaff', 'todayPresent', 'pendingAdvances'));
    }

    public function attendance(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $staff = User::whereNotIn('role', ['customer', 'farmer'])->get();
        $attendances = StaffAttendance::whereDate('date', $date)->get()->keyBy('user_id');

        return view('staff.attendance', compact('staff', 'attendances', 'date'));
    }

    public function saveAttendance(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $statuses = $request->input('status', []);

        foreach ($statuses as $userId => $status) {
            StaffAttendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $date],
                ['status' => $status]
            );
        }

        AuditLog::log('Updated Staff Attendance', 'StaffAttendance', 0, ['date' => $date]);

        return back()->with('success', 'Staff attendance updated successfully.');
    }

    public function advances()
    {
        $advances = StaffAdvance::with('user')->latest()->paginate(15);
        $staff = User::whereNotIn('role', ['customer', 'farmer'])->get();
        $totalAdvances = StaffAdvance::where('status', 'approved')->sum('amount');

        return view('staff.advances', compact('advances', 'staff', 'totalAdvances'));
    }

    public function storeAdvance(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:10',
            'advance_date' => 'required|date',
            'payment_mode' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        StaffAdvance::create($validated);
        AuditLog::log('Created Staff Advance', 'StaffAdvance', 0, ['amount' => $validated['amount']]);

        return back()->with('success', 'Staff advance recorded successfully.');
    }

    public function salaries(Request $request)
    {
        $month = $request->get('month', Carbon::today()->format('Y-m'));
        $salaries = StaffSalary::with('user')->where('salary_month', $month)->get();
        $staff = User::whereNotIn('role', ['customer', 'farmer'])->get();

        return view('staff.salaries', compact('salaries', 'staff', 'month'));
    }

    public function generateSalary(Request $request)
    {
        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $staff = User::whereNotIn('role', ['customer', 'farmer'])->get();

        foreach ($staff as $st) {
            $base = 15000; // Standard base default
            $advances = StaffAdvance::where('user_id', $st->id)
                ->where('status', 'approved')
                ->whereBetween('advance_date', [Carbon::parse($month.'-01'), Carbon::parse($month.'-01')->endOfMonth()])
                ->sum('amount');

            StaffSalary::updateOrCreate(
                ['user_id' => $st->id, 'salary_month' => $month],
                [
                    'base_salary' => $base,
                    'present_days' => 28,
                    'advance_deduction' => $advances,
                    'net_payable' => max(0, $base - $advances),
                    'payment_status' => 'paid',
                    'disbursed_at' => Carbon::today(),
                ]
            );
        }

        AuditLog::log('Generated Staff Salaries', 'StaffSalary', 0, ['month' => $month]);

        return back()->with('success', "Salaries generated for {$month}!");
    }
}
