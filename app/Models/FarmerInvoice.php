<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'farmer_id',
        'period_start',
        'period_end',
        'total_quantity',
        'milk_amount',
        'stationary_deduction',
        'opening_wallet_balance',
        'wallet_deducted_amount',
        'closing_wallet_balance',
        'wallet_deductions_breakdown',
        'feed_deduction',
        'advance_deduction',
        'other_deduction',
        'deductions_breakdown',
        'total_deduction',
        'other_credit',
        'transport_credit',
        'subsidy_credit',
        'incentive_credit',
        'total_credit',
        'total_amount',
        'round_off',
        'previous_balance',
        'net_payment',
        'status',
        'is_edited',
        'payment_mode',
        'payment_reference',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_quantity' => 'decimal:2',
        'milk_amount' => 'decimal:2',
        'stationary_deduction' => 'decimal:2',
        'feed_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'other_credit' => 'decimal:2',
        'transport_credit' => 'decimal:2',
        'subsidy_credit' => 'decimal:2',
        'incentive_credit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'net_payment' => 'decimal:2',
        'is_edited' => 'boolean',
        'deductions_breakdown' => 'array',
        'opening_wallet_balance' => 'decimal:2',
        'wallet_deducted_amount' => 'decimal:2',
        'closing_wallet_balance' => 'decimal:2',
        'wallet_deductions_breakdown' => 'array',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function walletTransactions()
    {
        return $this->hasMany(FarmerWalletTransaction::class, 'farmer_invoice_id');
    }

    public function settlement()
    {
        return $this->hasOne(FarmerSettlement::class, 'farmer_id', 'farmer_id')
            ->whereDate('period_start', $this->period_start)
            ->whereDate('period_end', $this->period_end);
    }

    /**
     * Get milk collection records linked directly or within invoice billing period
     */
    public function getMilkCollectionsAttribute()
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('milk_collections', 'farmer_invoice_id')) {
            $linked = MilkCollection::where('farmer_invoice_id', $this->id)
                ->orderBy('collection_date', 'asc')
                ->orderBy('shift', 'asc')
                ->get();

            if ($linked->count() > 0) {
                return $linked;
            }
        }

        // Exclude collections covered by another settled invoice
        $settledRanges = self::where('farmer_id', $this->farmer_id)
            ->where('id', '!=', $this->id)
            ->whereIn('status', ['settled', 'paid'])
            ->get(['period_start', 'period_end']);

        $query = MilkCollection::where('farmer_id', $this->farmer_id)
            ->whereBetween('collection_date', [$this->period_start, $this->period_end]);

        if (\Illuminate\Support\Facades\Schema::hasColumn('milk_collections', 'farmer_invoice_id')) {
            $query->where(function ($q) {
                $q->whereNull('farmer_invoice_id')
                  ->orWhere('farmer_invoice_id', $this->id);
            });
        }

        $all = $query->orderBy('collection_date', 'asc')
            ->orderBy('shift', 'asc')
            ->get();

        return $all->filter(function ($c) use ($settledRanges) {
            $colDate = $c->collection_date ? $c->collection_date->format('Y-m-d') : $c->created_at->format('Y-m-d');
            foreach ($settledRanges as $inv) {
                $pStart = $inv->period_start instanceof \Carbon\Carbon ? $inv->period_start->format('Y-m-d') : $inv->period_start;
                $pEnd = $inv->period_end instanceof \Carbon\Carbon ? $inv->period_end->format('Y-m-d') : $inv->period_end;
                if ($colDate >= $pStart && $colDate <= $pEnd) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get deductions (including in-hand advances and unpaid product purchases) within invoice billing period
     */
    public function getDeductionsListAttribute()
    {
        $items = collect();

        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
            $deductions = FarmerDeduction::where('farmer_id', $this->farmer_id)
                ->whereBetween('entry_date', [$this->period_start, $this->period_end])
                ->latest('entry_date')
                ->get()
                ->map(function ($d) {
                    if (empty($d->remarks) && !empty($d->comments)) {
                        $d->remarks = $d->comments;
                    }
                    return $d;
                });
            $items = $items->concat($deductions);
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('product_sales')) {
            $farmerCode = $this->farmer ? $this->farmer->farmer_code : null;
            $sales = \App\Models\ProductSale::where(function ($q) use ($farmerCode) {
                    $q->where('farmer_id', $this->farmer_id);
                    if (!empty($farmerCode)) {
                        $q->orWhere('farmer_code', $farmerCode);
                    }
                })
                ->whereBetween('sale_date', [$this->period_start, $this->period_end])
                ->latest('sale_date')
                ->get();

            foreach ($sales as $sale) {
                $due = (float) $sale->remaining_amount;
                if ($due > 0) {
                    $items->push((object) [
                        'id' => 'sale-' . $sale->id,
                        'entry_date' => $sale->sale_date ? $sale->sale_date->format('Y-m-d') : null,
                        'deduction_type' => 'cattle_feed',
                        'transaction_type' => 'given',
                        'amount' => $due,
                        'comments' => "Purchase #{$sale->sale_number}: {$sale->product_name} ({$sale->quantity} {$sale->unit})",
                        'remarks' => "Purchase #{$sale->sale_number}: {$sale->product_name} ({$sale->quantity} {$sale->unit})",
                    ]);
                }
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_advances')) {
            $advances = FarmerAdvance::where('farmer_id', $this->farmer_id)
                ->whereBetween('advance_date', [$this->period_start, $this->period_end])
                ->where('status', '!=', 'recovered')
                ->latest('advance_date')
                ->get();

            foreach ($advances as $adv) {
                $bal = (float) $adv->principal_balance;
                if ($bal > 0) {
                    $items->push((object) [
                        'id' => 'adv-' . $adv->id,
                        'entry_date' => $adv->advance_date ? $adv->advance_date->format('Y-m-d') : null,
                        'deduction_type' => 'in_hand_advance',
                        'transaction_type' => 'given',
                        'amount' => $bal,
                        'comments' => ($adv->voucher_no ? "Voucher #{$adv->voucher_no} - " : '') . ($adv->purpose ?: ($adv->notes ?: 'In Hand Advance')),
                        'remarks' => ($adv->voucher_no ? "Voucher #{$adv->voucher_no} - " : '') . ($adv->purpose ?: ($adv->notes ?: 'In Hand Advance')),
                    ]);
                }
            }
        }

        return $items;
    }

    /**
     * Get structured category-wise deductions breakdown for current invoice.
     * Shows Original Balance, Deducted Amount in this Invoice, and Remaining Balance.
     */
    public function getCategoryDeductionsBreakdown(): array
    {
        // If invoice is already verified/generated (NOT pending) and has saved deductions_breakdown, return it
        if ($this->status !== 'pending' && !empty($this->deductions_breakdown) && is_array($this->deductions_breakdown)) {
            return $this->deductions_breakdown;
        }

        $farmer = $this->farmer;
        $farmerId = $this->farmer_id;
        $startDate = $this->period_start ? $this->period_start->format('Y-m-d') : null;
        $endDate = $this->period_end ? $this->period_end->format('Y-m-d') : null;

        // 1. Advance / Loan Outstanding
        $loanOutstanding = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_advances')) {
            $advs = FarmerAdvance::where('farmer_id', $farmerId)
                ->where('status', '!=', 'recovered')
                ->get();
            $loanOutstanding = round((float) $advs->sum(function ($a) {
                return (float) $a->principal_balance;
            }), 2);
        }

        // Installment setting for advance
        $instType = $farmer ? $farmer->getInstallmentDeductionType() : 'Auto';
        $instAmount = $farmer ? (float) $farmer->getInstallmentAmount() : 0.0;
        $advDeductible = 0.00;
        if ($instType === 'DeductionOff') {
            $advDeductible = 0.00;
        } elseif ($instType === 'Fixed' && $instAmount > 0) {
            $advDeductible = min($instAmount, $loanOutstanding);
        } else {
            $advDeductible = (float) $this->advance_deduction ?: $loanOutstanding;
        }
        $advRemaining = max(0, $loanOutstanding - $advDeductible);

        // 2. Feed / Product Outstanding
        $feedOutstanding = (float) $this->feed_deduction;
        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
            $productDedsQuery = \App\Models\FarmerDeduction::where('farmer_id', $farmerId)
                ->whereIn('deduction_type', ['cattle_feed', 'store_purchase', 'ghee_butter', 'product', 'feed', 'food'])
                ->where('transaction_type', 'given');
            if ($startDate && $endDate) {
                $productDedsQuery->whereBetween('entry_date', [$startDate, $endDate]);
            }
            $pSum = (float) $productDedsQuery->sum('amount');
            if ($pSum > $feedOutstanding) {
                $feedOutstanding = round($pSum, 2);
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('product_sales')) {
            $farmerCode = $farmer ? $farmer->farmer_code : null;
            $salesDue = round((float) \App\Models\ProductSale::where(function ($q) use ($farmerId, $farmerCode) {
                    $q->where('farmer_id', $farmerId);
                    if (!empty($farmerCode)) $q->orWhere('farmer_code', $farmerCode);
                })
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount'), 2);
            $feedOutstanding = max($feedOutstanding, round($feedOutstanding + $salesDue, 2));
        }
        $feedDeductible = (float) $this->feed_deduction ?: $feedOutstanding;
        $feedRemaining = max(0, $feedOutstanding - $feedDeductible);

        // 3. Stationary Outstanding
        $statOutstanding = (float) $this->stationary_deduction;
        $statDeductible = $statOutstanding;
        $statRemaining = max(0, $statOutstanding - $statDeductible);

        // 4. Center-wide deductions (e.g. Building fund, Grant, Anamat, etc.)
        $extraCategories = [];
        try {
            $dedTypes = \App\Services\CenterAdditionDeductionService::getDeductionTypes();
            foreach ($dedTypes as $dt) {
                $cName = $dt['name'];
                $low = strtolower($cName);
                if (str_contains($low, 'advance') || str_contains($low, 'loan') || str_contains($low, 'feed') || str_contains($low, 'food') || str_contains($low, 'stationary') || str_contains($low, 'round off')) {
                    continue;
                }

                $rate = $dt['is_farmer_wise'] ? ($farmer ? $farmer->getFarmerWiseRate($cName) : 0.0) : (float) $dt['amount'];
                if ($rate > 0) {
                    $liters = (float) $this->total_quantity;
                    $lineAmt = \App\Services\CenterAdditionDeductionService::computeAmount($rate, $dt['unit'], $cName, $liters, (float) $this->milk_amount);
                    if ($lineAmt > 0) {
                        $isMgmt = str_contains($low, 'management') || str_contains($low, 'mgmt');
                        $extraCategories[] = [
                            'key' => \Illuminate\Support\Str::slug($cName, '_'),
                            'name' => $cName,
                            'category' => $cName,
                            'original_balance' => $lineAmt,
                            'deducted_amount' => $lineAmt,
                            'remaining_balance' => 0.00,
                            'is_editable' => !$isMgmt,
                        ];
                    }
                }
            }
        } catch (\Throwable $ex) {}

        $breakdown = [];

        // 1. Product row (Combined Cattle feed, Store purchases, supplies)
        if ($feedOutstanding > 0) {
            $breakdown[] = [
                'key' => 'product',
                'name' => 'Product',
                'category' => 'Product',
                'original_balance' => $feedOutstanding,
                'deducted_amount' => $feedDeductible,
                'remaining_balance' => $feedRemaining,
                'is_wallet' => false,
                'is_editable' => true,
            ];
        }

        // 2. Stationary row
        if ($statOutstanding > 0) {
            $breakdown[] = [
                'key' => 'stationary',
                'name' => 'Stationary',
                'category' => 'Stationary',
                'original_balance' => $statOutstanding,
                'deducted_amount' => $statDeductible,
                'remaining_balance' => $statRemaining,
                'is_wallet' => false,
                'is_editable' => true,
            ];
        }

        // 3. Advance / Loan row
        if ($loanOutstanding > 0 || $advDeductible > 0) {
            $breakdown[] = [
                'key' => 'advance',
                'name' => 'Advance / Loan',
                'category' => 'Advance',
                'original_balance' => $loanOutstanding,
                'deducted_amount' => $advDeductible,
                'remaining_balance' => $advRemaining,
                'is_wallet' => false,
                'is_editable' => true,
            ];
        }

        // 4. Add any other dynamic categories (Management Charges C, etc.)
        foreach ($extraCategories as $ec) {
            $low = strtolower($ec['name'] ?? '');
            $isMgmt = str_contains($low, 'management') || str_contains($low, 'mgmt');
            $ec['is_editable'] = !$isMgmt;
            $breakdown[] = $ec;
        }

        // Fallback for saved other_deduction if extraCategories was empty
        if ((float) $this->other_deduction > 0 && empty($extraCategories)) {
            $breakdown[] = [
                'key' => 'other_deductions',
                'name' => 'Other Charges (Per Invoice)',
                'category' => 'Charges',
                'original_balance' => (float) $this->other_deduction,
                'deducted_amount' => (float) $this->other_deduction,
                'remaining_balance' => 0.00,
                'is_editable' => false,
            ];
        }

        // 5. Existing Deduction Wallet Balance
        try {
            $wallet = \App\Services\FarmerWalletService::getOrCreateWallet($farmerId);
            $catBals = $wallet->getCategoryBalances();
            foreach ($catBals as $cName => $cBal) {
                $cBal = (float) $cBal;
                if ($cBal <= 0) continue;
                $cSlug = \Illuminate\Support\Str::slug($cName, '_');
                $cLower = strtolower($cName);

                $isProductCat = in_array($cSlug, ['product', 'cattle_feed', 'store_purchase', 'ghee_butter', 'feed', 'food'])
                    || str_contains($cLower, 'feed') || str_contains($cLower, 'food') || str_contains($cLower, 'store') || str_contains($cLower, 'ghee') || str_contains($cLower, 'butter') || str_contains($cLower, 'product');
                $isAdvanceCat = str_contains($cLower, 'advance') || str_contains($cLower, 'loan') || $cSlug === 'advance';
                $isStationaryCat = str_contains($cLower, 'stationary') || $cSlug === 'stationary';

                // Prevent duplicating categories already bundled into Product, Advance, or Stationary
                if ($isProductCat && $feedOutstanding > 0) {
                    continue;
                }
                if ($isAdvanceCat && ($loanOutstanding > 0 || $advDeductible > 0)) {
                    continue;
                }
                if ($isStationaryCat && $statOutstanding > 0) {
                    continue;
                }

                if ($isProductCat && $feedOutstanding <= 0) {
                    $breakdown[] = [
                        'key' => 'product',
                        'name' => 'Product',
                        'category' => 'Product',
                        'original_balance' => $cBal,
                        'deducted_amount' => $cBal,
                        'remaining_balance' => 0.00,
                        'is_wallet' => true,
                        'is_editable' => true,
                    ];
                    $feedOutstanding = $cBal;
                    continue;
                }

                $alreadyExists = collect($breakdown)->contains(function ($item) use ($cSlug, $cName) {
                    return $item['key'] === $cSlug || strtolower($item['name']) === strtolower($cName) || strtolower($item['category']) === strtolower($cName);
                });
                if (!$alreadyExists) {
                    $breakdown[] = [
                        'key' => 'wallet_' . $cSlug,
                        'name' => $cName,
                        'category' => $cName,
                        'original_balance' => $cBal,
                        'deducted_amount' => $cBal,
                        'remaining_balance' => 0.00,
                        'is_wallet' => true,
                        'is_editable' => true,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // Cap initial deducted_amount values to total earnings pool so invoice does not open in validation error
        $totalEarnings = (float) $this->total_amount;
        if ($totalEarnings <= 0 && (float) $this->milk_amount > 0) {
            $totalEarnings = (float) $this->milk_amount + (float) $this->total_credit;
        }

        if ($totalEarnings > 0) {
            $pool = $totalEarnings;

            // Pass 1: Fixed dairy charges (Management Charges C, etc.)
            foreach ($breakdown as &$b) {
                if (empty($b['is_editable'])) {
                    $orig = (float) ($b['original_balance'] ?? 0);
                    $ded = round(min($orig, $pool), 2);
                    $b['deducted_amount'] = $ded;
                    $b['remaining_balance'] = round(max(0, $orig - $ded), 2);
                    $pool = max(0, $pool - $ded);
                }
            }
            unset($b);

            // Pass 2: Variable deductions (Product, Advance, etc.)
            foreach ($breakdown as &$b) {
                if (!empty($b['is_editable'])) {
                    $orig = (float) ($b['original_balance'] ?? 0);
                    $target = (float) ($b['deducted_amount'] ?? $orig);
                    $ded = round(min($target, $pool), 2);
                    $b['deducted_amount'] = $ded;
                    $b['remaining_balance'] = round(max(0, $orig - $ded), 2);
                    $pool = max(0, $pool - $ded);
                }
            }
            unset($b);
        }

        return $breakdown;
    }

    /**
     * Check if a farmer is locked for milk collection on a given date due to a finalized/generated invoice.
     * Invoices with status != 'pending' (i.e. 'generated', 'finalized', 'paid') lock milk collections in their date range.
     */
    public static function getLockingInvoice(int $farmerId, string $date): ?self
    {
        return self::where('farmer_id', $farmerId)
            ->where('status', '!=', 'pending')
            ->whereDate('period_start', '<=', $date)
            ->whereDate('period_end', '>=', $date)
            ->first();
    }

    /**
     * Get cattle feed & product purchases within invoice period
     */
    public function getCattleFeedsListAttribute()
    {
        $feeds = collect();

        if (\Illuminate\Support\Facades\Schema::hasTable('farmer_deductions')) {
            $deds = FarmerDeduction::where('farmer_id', $this->farmer_id)
                ->where('deduction_type', 'cattle_feed')
                ->whereBetween('entry_date', [$this->period_start, $this->period_end])
                ->latest('entry_date')
                ->get();
            foreach ($deds as $d) {
                $feeds->push((object) [
                    'entry_date' => $d->entry_date ? $d->entry_date->format('Y-m-d') : null,
                    'item_name' => $d->comments ?: 'Cattle Feed',
                    'quantity' => 1,
                    'unit' => 'Bag',
                    'rate' => (float) $d->amount,
                    'amount' => (float) $d->amount,
                    'paid_amount' => $d->transaction_type === 'received' ? (float) $d->amount : 0.00,
                    'remaining_amount' => $d->transaction_type === 'given' ? (float) $d->amount : 0.00,
                    'remarks' => $d->comments ?: 'Cattle Feed',
                ]);
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('product_sales')) {
            $farmerCode = $this->farmer ? $this->farmer->farmer_code : null;
            $sales = \App\Models\ProductSale::where(function ($q) use ($farmerCode) {
                    $q->where('farmer_id', $this->farmer_id);
                    if (!empty($farmerCode)) {
                        $q->orWhere('farmer_code', $farmerCode);
                    }
                })
                ->whereBetween('sale_date', [$this->period_start, $this->period_end])
                ->latest('sale_date')
                ->get();

            foreach ($sales as $s) {
                $feeds->push((object) [
                    'entry_date' => $s->sale_date ? $s->sale_date->format('Y-m-d') : null,
                    'item_name' => $s->product_name . " (#{$s->sale_number})",
                    'quantity' => (float) $s->quantity,
                    'unit' => $s->unit ?: 'Bag',
                    'rate' => (float) $s->rate,
                    'amount' => (float) $s->total_amount,
                    'paid_amount' => (float) $s->paid_amount,
                    'remaining_amount' => (float) $s->remaining_amount,
                    'remarks' => "{$s->product_name} ({$s->quantity} {$s->unit} @ ₹{$s->rate})",
                ]);
            }
        }

        return $feeds;
    }
}
