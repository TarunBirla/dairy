<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CollectionCenter;
use App\Models\Farmer;
use App\Models\FarmerInvoice;
use App\Models\MilkCollection;
use App\Models\RateChart;
use App\Models\RateChartSlab;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FarmerInvoiceService
{
    /**
     * Resolve milk rate based on Rate Chart, farmer configuration, FAT, SNF, CLR, and milk type.
     */
    public static function resolveMilkRate(
        float $fat,
        float $snf,
        float $clr,
        string $milkType,
        ?Farmer $farmer = null,
        ?string $collectionDate = null,
        string $shift = 'morning'
    ): array {
        $milkType = strtolower(trim($milkType)) ?: 'cow';
        $collectionType = SystemSetting::get('collection_type', 'FAT + SNF');

        // Normalize parameters
        if ($clr > 0 && $fat > 0 && $snf <= 0) {
            $snf = round(($clr / 4.0) + (0.21 * $fat) + 0.36, 2);
        } elseif ($snf > 0 && $fat > 0 && $clr <= 0) {
            $clr = round(($snf - (0.21 * $fat) - 0.36) * 4.0, 2);
        }

        $rate = 0.0;
        $chartName = 'Standard Dairy Formula';
        $chartId = null;

        // 1. Check Farmer Fixed Rate
        $isFixed = false;
        if ($farmer) {
            if ($farmer->pricing_type === 'fixed_rate') {
                $isFixed = true;
            } elseif (empty($farmer->pricing_type) && ($farmer->cow_milk_rate > 0 || $farmer->buffalo_milk_rate > 0) && !$farmer->rate_chart_id) {
                $isFixed = true;
            }
        }

        if ($farmer && $isFixed) {
            $fixedRate = ($milkType === 'buffalo') ? (float) $farmer->buffalo_milk_rate : (float) $farmer->cow_milk_rate;
            if ($fixedRate <= 0) {
                $fixedRate = (float) ($farmer->cow_milk_rate ?: $farmer->buffalo_milk_rate ?: 0);
            }
            $rate = round($fixedRate, 2);
            $chartName = "Fixed Rate (" . ucfirst($milkType) . ")";
            return [
                'rate' => $rate,
                'chart_name' => $chartName,
                'chart_id' => null,
                'fat' => $fat,
                'snf' => $snf,
                'clr' => $clr,
            ];
        }

        // 2. Custom Farmer Rate Override
        if ($farmer && $farmer->custom_rate_override > 0) {
            $rate = round((float) $farmer->custom_rate_override, 2);
            return [
                'rate' => $rate,
                'chart_name' => "Custom Farmer Rate (₹{$rate})",
                'chart_id' => null,
                'fat' => $fat,
                'snf' => $snf,
                'clr' => $clr,
            ];
        }

        // 3. Match Rate Chart
        $chart = null;
        if ($farmer && $farmer->rate_chart_id) {
            $chart = RateChart::where('id', $farmer->rate_chart_id)
                ->where('status', 'active')
                ->first();
        }

        if (!$chart && $farmer && $farmer->collection_center_id) {
            $center = CollectionCenter::find($farmer->collection_center_id);
            if ($center && $center->rate_chart_id) {
                $chart = RateChart::where('id', $center->rate_chart_id)->where('status', 'active')->first();
            }
        }

        if (!$chart) {
            $chart = RateChart::where(function ($q) use ($milkType) {
                    $q->where('milk_type', $milkType)
                      ->orWhereIn('milk_type', ['both', 'mixed', 'all']);
                })
                ->where('status', 'active')
                ->latest('id')
                ->first();
        }

        if ($chart) {
            $chartName = $chart->name;
            $chartId = $chart->id;

            // Check slabs
            $slab = RateChartSlab::where('rate_chart_id', $chart->id)
                ->where('fat_min', '<=', $fat)
                ->where('fat_max', '>=', $fat)
                ->where(function ($q) use ($snf) {
                    $q->whereNull('snf_min')
                      ->orWhere(function ($sq) use ($snf) {
                          $sq->where('snf_min', '<=', $snf)
                             ->where('snf_max', '>=', $snf);
                      });
                })
                ->first();

            if ($slab && $slab->rate > 0) {
                $rate = (float) $slab->rate;
            } elseif ($chart->base_rate > 0) {
                $fatDiff = max(0, $fat - (float) ($chart->fat_standard ?: 4.0));
                $snfDiff = max(0, $snf - (float) ($chart->snf_standard ?: 8.5));
                $rate = (float) $chart->base_rate + ($fatDiff * (float) ($chart->fat_rate ?: 6.5)) + ($snfDiff * (float) ($chart->snf_rate ?: 4.0));
            }
        }

        if ($rate <= 0) {
            // Standard fallback rate formula
            if ($milkType === 'buffalo') {
                $rate = 35.0 + ($fat * 4.5) + ($snf * 1.5);
            } else {
                $rate = 28.0 + ($fat * 3.8) + ($snf * 1.2);
            }
        }

        $rate = round($rate, 2);

        return [
            'rate' => $rate,
            'chart_name' => $chartName,
            'chart_id' => $chartId,
            'fat' => $fat,
            'snf' => $snf,
            'clr' => $clr,
        ];
    }

    /**
     * Add a missing milk collection entry directly to an existing invoice.
     * Prevents duplicate collection entries, resolves rate, and recalculates invoice totals & wallet deductions.
     */
    public static function addMissingMilkEntry(
        FarmerInvoice $invoice,
        array $data,
        ?int $createdBy = null
    ): MilkCollection {
        FarmerWalletService::ensureSchemaMigrated();

        $createdBy = $createdBy ?: Auth::id();
        $farmer = $invoice->farmer;
        $farmerId = $invoice->farmer_id;

        $date = Carbon::parse($data['collection_date'])->format('Y-m-d');
        $shift = strtolower(trim($data['shift'] ?? 'morning'));
        $milkType = strtolower(trim($data['milk_type'] ?? 'cow'));
        $qty = round((float) ($data['quantity_liters'] ?? 0), 2);
        $fat = round((float) ($data['fat'] ?? 0), 2);
        $snf = round((float) ($data['snf'] ?? 0), 2);
        $clr = round((float) ($data['clr'] ?? 0), 2);

        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity in liters must be greater than zero.');
        }

        // Prevent duplicate collection entries for same farmer, date, shift, and milk type
        $duplicate = MilkCollection::where('farmer_id', $farmerId)
            ->whereDate('collection_date', $date)
            ->where('shift', $shift)
            ->where('milk_type', $milkType)
            ->first();

        if ($duplicate) {
            throw new \Exception("A milk collection entry already exists for {$date} ({$shift} shift, {$milkType} milk) with Receipt #{$duplicate->receipt_number}.");
        }

        // Resolve rate automatically from Rate Chart / formula
        $resolved = self::resolveMilkRate($fat, $snf, $clr, $milkType, $farmer, $date, $shift);
        $rate = isset($data['applied_rate']) && (float) $data['applied_rate'] > 0
            ? round((float) $data['applied_rate'], 2)
            : $resolved['rate'];

        $gross = round($qty * $rate, 2);

        // Per-liter collection charges
        $charges = CenterAdditionDeductionService::calculateForCollection($farmer, $qty, $gross);
        $bonus = (float) ($data['bonus'] ?? $charges['total_additions']);
        $deduction = (float) ($data['deduction'] ?? $charges['total_deductions']);
        $net = max(0, round($gross + $bonus - $deduction, 2));

        return DB::transaction(function () use ($invoice, $farmer, $date, $shift, $milkType, $qty, $fat, $snf, $clr, $rate, $gross, $bonus, $deduction, $net, $resolved, $createdBy) {
            $nextReceiptId = (MilkCollection::max('id') ?? 0) + 1001;
            $receiptNo = 'COL-' . $nextReceiptId;

            $collection = MilkCollection::create([
                'receipt_number' => $receiptNo,
                'farmer_id' => $farmer->id,
                'collection_center_id' => $farmer->collection_center_id,
                'operator_id' => $createdBy,
                'collection_date' => $date,
                'shift' => $shift,
                'milk_type' => $milkType,
                'quantity_liters' => $qty,
                'fat' => $fat,
                'snf' => $snf,
                'clr' => $clr,
                'calculated_rate' => $resolved['rate'],
                'applied_rate' => $rate,
                'pricing_type' => $resolved['chart_name'],
                'rate_chart_id' => $resolved['chart_id'],
                'gross_amount' => $gross,
                'bonus' => $bonus,
                'deduction' => $deduction,
                'net_amount' => $net,
                'payment_status' => 'invoiced', // Marked as invoiced immediately
                'farmer_invoice_id' => $invoice->id, // Linked to this invoice
                'notes' => 'Added manually via Invoice #' . $invoice->invoice_number,
            ]);

            // Re-sync invoice metrics with this new entry included
            self::recalculateInvoice($invoice);

            AuditLog::log("Added missing milk collection #{$receiptNo} to Invoice #{$invoice->invoice_number}", 'MilkCollection', $collection->id, [
                'farmer' => $farmer->name,
                'qty' => $qty,
                'net' => $net,
                'invoice_id' => $invoice->id,
            ]);

            return $collection;
        });
    }

    /**
     * Recalculate an existing invoice's collections, credits, and wallet deductions.
     */
    public static function recalculateInvoice(FarmerInvoice $invoice): FarmerInvoice
    {
        FarmerWalletService::ensureSchemaMigrated();

        // Query all collections linked to this invoice or within date range
        $collections = MilkCollection::where(function ($q) use ($invoice) {
                $q->where('farmer_invoice_id', $invoice->id)
                  ->orWhere(function ($sq) use ($invoice) {
                      $sq->where('farmer_id', $invoice->farmer_id)
                         ->whereNull('farmer_invoice_id')
                         ->whereBetween('collection_date', [$invoice->period_start, $invoice->period_end]);
                  });
            })
            ->orderBy('collection_date', 'asc')
            ->orderBy('shift', 'asc')
            ->get();

        // Mark all these collections as linked to this invoice
        MilkCollection::whereIn('id', $collections->pluck('id'))->update([
            'farmer_invoice_id' => $invoice->id,
            'payment_status' => 'invoiced',
        ]);

        $totalQty = round((float) $collections->sum('quantity_liters'), 2);
        $milkAmount = round((float) $collections->sum('net_amount'), 2);

        $farmer = $invoice->farmer;
        $totalCredit = (float) $invoice->total_credit;

        // Apply wallet deductions (reverses old deductions and applies current breakdown)
        $walletSummary = FarmerWalletService::applyInvoiceDeductions($invoice, [], $invoice->created_by);
        $totalDeductions = (float) $invoice->total_deduction ?: (float) $walletSummary['wallet_deducted_amount'];

        $totalAmount = round($milkAmount + $totalCredit, 2);
        $prevBal = round(max(0, $totalDeductions - $totalAmount), 2);
        $netPayment = round(max(0, $totalAmount - $totalDeductions), 2);

        $invoice->update([
            'total_quantity' => $totalQty,
            'milk_amount' => $milkAmount,
            'total_amount' => $totalAmount,
            'previous_balance' => $prevBal,
            'net_payment' => $netPayment,
        ]);

        return $invoice;
    }

    /**
     * Delete an invoice safely:
     * 1. Reverses previous wallet deductions (recording reversal transactions).
     * 2. Restores all milk collections to Pending (payment_status = 'pending', farmer_invoice_id = null).
     * 3. Deletes the invoice record without losing original milk collection records.
     */
    public static function deleteInvoice(FarmerInvoice $invoice, ?int $deletedBy = null): void
    {
        FarmerWalletService::ensureSchemaMigrated();

        $deletedBy = $deletedBy ?: Auth::id();
        $invNum = $invoice->invoice_number;
        $invId = $invoice->id;

        DB::transaction(function () use ($invoice, $invNum, $invId, $deletedBy) {
            // 1. Reverse wallet deductions
            FarmerWalletService::reverseInvoiceDeductions($invoice, "Invoice #{$invNum} deleted", $deletedBy);

            // 2. Restore all linked milk collections back to 'pending' state
            if (Schema::hasColumn('milk_collections', 'farmer_invoice_id')) {
                MilkCollection::where('farmer_invoice_id', $invId)->update([
                    'farmer_invoice_id' => null,
                    'payment_status' => 'pending',
                ]);
            }

            // Also restore any collections by date if they were marked invoiced
            MilkCollection::where('farmer_id', $invoice->farmer_id)
                ->whereBetween('collection_date', [$invoice->period_start, $invoice->period_end])
                ->where('payment_status', 'invoiced')
                ->update([
                    'payment_status' => 'pending',
                ]);

            // 3. Delete the invoice record
            $invoice->delete();

            AuditLog::log("Deleted Farmer Invoice #{$invNum} and restored milk collections to pending", 'FarmerInvoice', $invId, [
                'invoice_number' => $invNum,
            ]);
        });
    }
}
