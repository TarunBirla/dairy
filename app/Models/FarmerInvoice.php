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
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get milk collection records within invoice billing period
     */
    public function getMilkCollectionsAttribute()
    {
        return MilkCollection::where('farmer_id', $this->farmer_id)
            ->whereBetween('collection_date', [$this->period_start, $this->period_end])
            ->orderBy('collection_date', 'asc')
            ->orderBy('shift', 'asc')
            ->get();
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
        // If invoice is already verified/generated and has saved deductions_breakdown, return it
        if (!empty($this->deductions_breakdown) && is_array($this->deductions_breakdown)) {
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

        // 2. Feed / Food Outstanding
        $feedOutstanding = (float) $this->feed_deduction;
        if (\Illuminate\Support\Facades\Schema::hasTable('product_sales') && $feedOutstanding <= 0) {
            $farmerCode = $farmer ? $farmer->farmer_code : null;
            $feedOutstanding = round((float) \App\Models\ProductSale::where(function ($q) use ($farmerId, $farmerCode) {
                    $q->where('farmer_id', $farmerId);
                    if (!empty($farmerCode)) $q->orWhere('farmer_code', $farmerCode);
                })
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount'), 2);
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
                        $extraCategories[] = [
                            'key' => \Illuminate\Support\Str::slug($cName, '_'),
                            'name' => $cName,
                            'category' => $cName,
                            'original_balance' => $lineAmt,
                            'deducted_amount' => $lineAmt,
                            'remaining_balance' => 0.00,
                        ];
                    }
                }
            }
        } catch (\Throwable $ex) {}

        $breakdown = [];

        // Advance / Loan row
        $breakdown[] = [
            'key' => 'advance',
            'name' => 'Advance / Loan (ऋण / अग्रिम)',
            'category' => 'Advance',
            'original_balance' => $loanOutstanding,
            'deducted_amount' => $advDeductible,
            'remaining_balance' => $advRemaining,
        ];

        // Food / Feed row
        $breakdown[] = [
            'key' => 'feed',
            'name' => 'Food / Cattle Feed (पशु आहार)',
            'category' => 'Feed',
            'original_balance' => $feedOutstanding,
            'deducted_amount' => $feedDeductible,
            'remaining_balance' => $feedRemaining,
        ];

        // Stationary row
        $breakdown[] = [
            'key' => 'stationary',
            'name' => 'Stationary (स्टेशनरी)',
            'category' => 'Stationary',
            'original_balance' => $statOutstanding,
            'deducted_amount' => $statDeductible,
            'remaining_balance' => $statRemaining,
        ];

        // Add any other dynamic categories
        foreach ($extraCategories as $ec) {
            $breakdown[] = $ec;
        }

        return $breakdown;
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
