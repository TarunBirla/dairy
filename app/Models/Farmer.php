<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farmer_code',
        'user_id',
        'branch_id',
        'collection_center_id',
        'rate_chart_id',
        'pricing_type',
        'name',
        'name_hi',
        'photo',
        'phone',
        'village',
        'address',
        'vehicle',
        'route_id',
        'supplier_type',
        'animal_type',
        'cow_milk_rate',
        'buffalo_milk_rate',
        'branch_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'upi_id',
        'custom_rate_override',
        'current_balance',
        'anamat',
        'building_fund',
        'installment',
        'etc_amount',
        'grant_amount',
        'extra_rates',
        'status',
    ];

    /**
     * Get pricing type with intelligent fallback
     */
    public function getPricingTypeAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        if ((float) $this->cow_milk_rate > 0 || (float) $this->buffalo_milk_rate > 0) {
            return 'fixed_rate';
        }
        return 'rate_chart';
    }

    protected $casts = [
        'current_balance' => 'decimal:2',
        'cow_milk_rate' => 'decimal:2',
        'buffalo_milk_rate' => 'decimal:2',
        'anamat' => 'decimal:2',
        'building_fund' => 'decimal:2',
        'installment' => 'decimal:2',
        'etc_amount' => 'decimal:2',
        'grant_amount' => 'decimal:2',
        'extra_rates' => 'array',
    ];

    /**
     * Get farmer-specific rate/amount for a category configured as Farmer Wise.
     * Supports exact match, case-insensitive match, and legacy database columns.
     */
    public function getFarmerWiseRate(string $categoryName, float $default = 0.0): float
    {
        $key = trim($categoryName);
        $rates = is_array($this->extra_rates) ? $this->extra_rates : [];

        // 1. Direct match in extra_rates
        if (isset($rates[$key]) && is_numeric($rates[$key])) {
            return (float) $rates[$key];
        }

        // 2. Case-insensitive match in extra_rates
        foreach ($rates as $k => $v) {
            if (strcasecmp(trim($k), $key) === 0 && is_numeric($v)) {
                return (float) $v;
            }
        }

        // 3. Fallback to legacy columns
        $legacyMap = [
            'anamat' => 'anamat',
            'building fund' => 'building_fund',
            'installment' => 'installment',
            'etc' => 'etc_amount',
            'grant' => 'grant_amount',
        ];
        $lower = strtolower($key);
        if (isset($legacyMap[$lower]) && isset($this->{$legacyMap[$lower]}) && is_numeric($this->{$legacyMap[$lower]})) {
            return (float) $this->{$legacyMap[$lower]};
        }

        return (float) $default;
    }

    /**
     * Get farmer's advance installment deduction type (Auto, DeductionOff, Fixed)
     */
    public function getInstallmentDeductionType(): string
    {
        $rates = is_array($this->extra_rates) ? $this->extra_rates : [];
        if (!empty($rates['installment_deduction_type'])) {
            return $rates['installment_deduction_type'];
        }
        if ((float)($this->installment ?? 0) > 0) {
            return 'Fixed';
        }
        return 'Auto';
    }

    /**
     * Get farmer's installment amount
     */
    public function getInstallmentAmount(): float
    {
        return (float) ($this->installment ?? 0.0);
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
                return $this->photo;
            }
            if (file_exists(public_path('uploads/farmers/' . $this->photo))) {
                return asset('uploads/farmers/' . $this->photo);
            }
            if (file_exists(public_path('storage/' . $this->photo))) {
                return asset('storage/' . $this->photo);
            }
            return asset($this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=10b981&color=ffffff&bold=true';
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function collectionCenter()
    {
        return $this->belongsTo(CollectionCenter::class);
    }

    public function rateChart()
    {
        return $this->belongsTo(RateChart::class);
    }

    public function collections()
    {
        return $this->hasMany(MilkCollection::class);
    }

    public function advances()
    {
        return $this->hasMany(FarmerAdvance::class);
    }

    public function deductions()
    {
        return $this->hasMany(FarmerDeduction::class);
    }

    public function farmerInvoices()
    {
        return $this->hasMany(FarmerInvoice::class);
    }

    public function loans()
    {
        return $this->hasMany(FarmerLoan::class);
    }

    public function settlements()
    {
        return $this->hasMany(FarmerSettlement::class);
    }

    public function ledgers()
    {
        return $this->hasMany(FarmerLedger::class);
    }

    public function productSales()
    {
        return $this->hasMany(ProductSale::class);
    }

    public function emergencyFunds()
    {
        return $this->hasMany(FarmerEmergencyFund::class);
    }

    public function wallet()
    {
        return $this->hasOne(FarmerWallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(FarmerWalletTransaction::class);
    }

    /**
     * Get or create the deduction wallet for this farmer.
     */
    public function getOrCreateWallet(): FarmerWallet
    {
        return FarmerWallet::firstOrCreate(
            ['farmer_id' => $this->id],
            ['balance' => 0.00]
        );
    }

    /**
     * Check if farmer has any pending financial transactions, dues, or balances.
     */
    public function getPendingDuesDetails(): array
    {
        $dues = [];

        // 1. Current Ledger Balance
        $bal = (float) ($this->current_balance ?? 0);
        if (abs($bal) > 0.01) {
            $dues[] = "Ledger Balance: ₹" . number_format($bal, 2);
        }

        // 2. Active Advances
        try {
            $advances = $this->advances()->where('status', '!=', 'settled')->get();
            $pendingAdvance = 0.0;
            foreach ($advances as $adv) {
                $rem = (float) $adv->amount - ((float) $adv->deducted_amount + (float) $adv->paid_amount);
                if ($rem > 0.01) {
                    $pendingAdvance += $rem;
                }
            }
            if ($pendingAdvance > 0.01) {
                $dues[] = "Advance Balance: ₹" . number_format($pendingAdvance, 2);
            }
        } catch (\Throwable $e) {}

        // 3. Active Loans
        try {
            $pendingLoans = (float) $this->loans()->where('status', '!=', 'completed')->where('status', '!=', 'closed')->sum('remaining_amount');
            if ($pendingLoans > 0.01) {
                $dues[] = "Loan Balance: ₹" . number_format($pendingLoans, 2);
            }
        } catch (\Throwable $e) {}

        // 4. Unpaid Product / Cattle Feed Sales
        try {
            $pendingSales = (float) $this->productSales()->where('is_paid', false)->where('remaining_amount', '>', 0.01)->sum('remaining_amount');
            if ($pendingSales > 0.01) {
                $dues[] = "Product Sales Dues: ₹" . number_format($pendingSales, 2);
            }
        } catch (\Throwable $e) {}

        // 5. Unbilled Milk Collections
        try {
            $unbilledMilkCount = $this->collections()->whereNull('invoice_id')->count();
            if ($unbilledMilkCount > 0) {
                $dues[] = "Unbilled Milk Collections: {$unbilledMilkCount} entries";
            }
        } catch (\Throwable $e) {}

        return $dues;
    }

    public function hasPendingTransactions(): bool
    {
        return count($this->getPendingDuesDetails()) > 0;
    }

    /**
     * Consolidate duplicate farmer accounts with identical phone number or name into a single primary farmer.
     * Moves all milk collections, advances, deductions, product sales, and wallet transactions to the primary farmer.
     * Soft-deletes/deactivates duplicate records and prevents duplicate multi-invoicing.
     */
    public static function consolidateDuplicateFarmers(): array
    {
        $consolidated = [];

        try {
            // Find duplicate active farmers by phone
            $duplicatePhones = self::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where('phone', '!=', '-')
                ->select('phone')
                ->groupBy('phone')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('phone');

            foreach ($duplicatePhones as $phone) {
                $farmers = self::where('phone', $phone)->orderBy('id', 'asc')->get();
                if ($farmers->count() <= 1) continue;

                // Keeper farmer: prefer the oldest or one with standard FAR- code
                $primary = $farmers->first(fn($f) => str_starts_with($f->farmer_code, 'FAR-')) ?: $farmers->first();
                $duplicates = $farmers->where('id', '!=', $primary->id);

                $primaryWallet = null;
                try {
                    $primaryWallet = \App\Services\FarmerWalletService::getOrCreateWallet($primary);
                } catch (\Throwable $e) {}

                foreach ($duplicates as $dup) {
                    // 1. Move Milk Collections
                    \App\Models\MilkCollection::where('farmer_id', $dup->id)->update([
                        'farmer_id' => $primary->id,
                    ]);

                    // 2. Move Farmer Advances
                    if (\Illuminate\Support\Facades\Schema::hasTable('farmer_advances')) {
                        \App\Models\FarmerAdvance::where('farmer_id', $dup->id)->update([
                            'farmer_id' => $primary->id,
                        ]);
                    }

                    // 3. Move Farmer Deductions
                    if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
                        \App\Models\FarmerDeduction::where('farmer_id', $dup->id)->update([
                            'farmer_id' => $primary->id,
                        ]);
                    }

                    // 4. Move Product / Feed Sales
                    if (\Illuminate\Support\Facades\Schema::hasTable('product_sales')) {
                        \App\Models\ProductSale::where('farmer_id', $dup->id)->update([
                            'farmer_id' => $primary->id,
                            'farmer_code' => $primary->farmer_code,
                        ]);
                    }

                    // 5. Move Wallet Transactions
                    if ($primaryWallet && \Illuminate\Support\Facades\Schema::hasTable('farmer_wallet_transactions')) {
                        \App\Models\FarmerWalletTransaction::where('farmer_id', $dup->id)->update([
                            'farmer_id' => $primary->id,
                            'wallet_id' => $primaryWallet->id,
                        ]);
                    }

                    // 6. Delete duplicate pending invoices
                    if (\Illuminate\Support\Facades\Schema::hasTable('farmer_invoices')) {
                        \App\Models\FarmerInvoice::where('farmer_id', $dup->id)->delete();
                    }

                    // 7. Delete duplicate wallet if exists
                    if (\Illuminate\Support\Facades\Schema::hasTable('farmer_wallets')) {
                        \App\Models\FarmerWallet::where('farmer_id', $dup->id)->delete();
                    }

                    // 8. Soft-delete / deactivate the duplicate farmer
                    $dup->update([
                        'status' => 'inactive',
                        'name' => $dup->name . ' [Merged into ' . $primary->farmer_code . ']',
                    ]);
                    $dup->delete();

                    $consolidated[] = [
                        'primary' => $primary->farmer_code . ' (' . $primary->name . ')',
                        'merged' => $dup->farmer_code . ' (' . $dup->name . ')',
                    ];
                }

                if ($primaryWallet) {
                    $primaryWallet->recalculateBalance();
                    \App\Models\FarmerInvoice::where('farmer_id', $primary->id)
                        ->where('status', 'pending')
                        ->update(['closing_wallet_balance' => (float) $primaryWallet->balance]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Farmer duplicate consolidation warning: ' . $e->getMessage());
        }

        return $consolidated;
    }
}

