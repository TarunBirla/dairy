<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Dealer;
use App\Models\AuditLog;

class DealerController extends Controller
{
    public function __construct()
    {
        $this->ensureSchemaAndDefaults();
    }

    /**
     * Ensure database columns and reference default dealers exist.
     */
    private function ensureSchemaAndDefaults(): void
    {
        try {
            if (!Schema::hasTable('dealers')) {
                Schema::create('dealers', function (Blueprint $table) {
                    $table->id();
                    $table->string('code')->unique();
                    $table->string('name');
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->text('address')->nullable();
                    $table->text('details')->nullable();
                    $table->string('bank_name')->nullable();
                    $table->string('account_number')->nullable();
                    $table->string('ifsc_code')->nullable();
                    $table->string('branch')->nullable();
                    $table->string('status')->default('active');
                    $table->timestamps();
                    $table->softDeletes();
                });
            } else {
                Schema::table('dealers', function (Blueprint $table) {
                    if (!Schema::hasColumn('dealers', 'details')) {
                        $table->text('details')->nullable()->after('address');
                    }
                    if (!Schema::hasColumn('dealers', 'bank_name')) {
                        $table->string('bank_name')->nullable()->after('details');
                    }
                    if (!Schema::hasColumn('dealers', 'account_number')) {
                        $table->string('account_number')->nullable()->after('bank_name');
                    }
                    if (!Schema::hasColumn('dealers', 'ifsc_code')) {
                        $table->string('ifsc_code')->nullable()->after('account_number');
                    }
                    if (!Schema::hasColumn('dealers', 'branch')) {
                        $table->string('branch')->nullable()->after('ifsc_code');
                    }
                });
            }

            // Seed dealers matching reference screenshot if table has few/no rows
            if (Dealer::count() === 0) {
                Dealer::create([
                    'code' => '001',
                    'name' => 'Asim Kumar',
                    'phone' => '1234561231',
                    'address' => 'Ranchi',
                    'details' => 'Feed & Dairy Minerals Supplier',
                    'bank_name' => 'State Bank of India',
                    'account_number' => '1234567895',
                    'ifsc_code' => 'SBIN0001281',
                    'branch' => 'Ranchi Main',
                    'status' => 'active',
                ]);
                Dealer::create([
                    'code' => '002',
                    'name' => 'anish',
                    'phone' => '9826011111',
                    'address' => 'Industrial Area',
                    'details' => 'Packaging & Bottles Supplier',
                    'bank_name' => 'HDFC Bank',
                    'account_number' => '50100234567',
                    'ifsc_code' => 'HDFC0001234',
                    'branch' => 'City Center',
                    'status' => 'active',
                ]);
                Dealer::create([
                    'code' => '003',
                    'name' => 'Akarsh',
                    'phone' => '9876543212',
                    'address' => 'New Road',
                    'details' => 'Veterinary Medicines',
                    'bank_name' => 'Punjab National Bank',
                    'account_number' => '1234567899',
                    'ifsc_code' => 'PUNB0123456',
                    'branch' => 'Civil Lines',
                    'status' => 'active',
                ]);
                Dealer::create([
                    'code' => '004',
                    'name' => 'Lokesh',
                    'phone' => '3216549074',
                    'address' => 'Station road',
                    'details' => 'Cattle Feed Wholesale',
                    'bank_name' => 'Bank of Baroda',
                    'account_number' => '1234567899',
                    'ifsc_code' => 'BARB0STATION',
                    'branch' => 'Station Branch',
                    'status' => 'active',
                ]);
                Dealer::create([
                    'code' => '005',
                    'name' => 'akshay',
                    'phone' => '4567891236',
                    'address' => 'Road No 0',
                    'details' => 'Dairy Equipment & Milk Cans',
                    'bank_name' => 'ICICI Bank',
                    'account_number' => '1234567899',
                    'ifsc_code' => 'ICIC0001234',
                    'branch' => 'Main Road',
                    'status' => 'active',
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore in constructor
        }
    }

    /**
     * Display dealer listing with filters and in-page modal management.
     */
    public function index(Request $request)
    {
        $query = Dealer::query();

        // Search: Code, name, mobile, address, bank
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%")
                  ->orWhere('branch', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $dealers = $query->latest('id')->paginate(15)->withQueryString();

        $totalDealers = Dealer::count();
        $activeDealers = Dealer::where('status', 'active')->count();

        // Next sequential dealer code
        $nextNum = Dealer::max('id') + 1;
        $nextCode = str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return view('dealers.index', compact(
            'dealers',
            'totalDealers',
            'activeDealers',
            'nextCode'
        ));
    }

    /**
     * Store new dealer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:dealers,code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'details' => 'nullable|string',
            'branch' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        try {
            $cols = Schema::getColumnListing('dealers');
            $data = array_intersect_key($validated, array_flip($cols));
        } catch (\Throwable $e) {
            $data = $validated;
        }

        $dealer = Dealer::create($data);
        AuditLog::log('Created Product Dealer', 'Dealer', $dealer->id, ['code' => $dealer->code, 'name' => $dealer->name]);

        return redirect()->route('dealers.index')->with('success', "Dealer {$dealer->name} ({$dealer->code}) created successfully!");
    }

    /**
     * Update dealer.
     */
    public function update(Request $request, Dealer $dealer)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:dealers,code,' . $dealer->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'details' => 'nullable|string',
            'branch' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $cols = Schema::getColumnListing('dealers');
            $data = array_intersect_key($validated, array_flip($cols));
        } catch (\Throwable $e) {
            $data = $validated;
        }

        $dealer->update($data);
        AuditLog::log('Updated Product Dealer', 'Dealer', $dealer->id, ['code' => $dealer->code, 'name' => $dealer->name]);

        return redirect()->route('dealers.index')->with('success', "Dealer {$dealer->name} ({$dealer->code}) updated successfully!");
    }

    /**
     * Delete dealer.
     */
    public function destroy(Dealer $dealer)
    {
        $name = $dealer->name;
        $code = $dealer->code;

        $dealer->delete();
        AuditLog::log('Deleted Product Dealer', 'Dealer', $dealer->id, ['code' => $code, 'name' => $name]);

        return redirect()->route('dealers.index')->with('success', "Dealer {$name} ({$code}) deleted successfully.");
    }

    /**
     * Print Dealer List matching reference Print action.
     */
    public function printList(Request $request)
    {
        $query = Dealer::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        $dealers = $query->orderBy('code', 'asc')->get();

        return view('dealers.print_list', compact('dealers'));
    }

    /**
     * Print single dealer profile statement.
     */
    public function printSlip(Dealer $dealer)
    {
        $dealer->load(['purchases', 'payments']);
        return view('dealers.print_slip', compact('dealer'));
    }
}
