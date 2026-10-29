<div class="space-y-4">
    <!-- Top Action Banner -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center font-bold">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Direct Milk Sale Intake</h3>
                <p class="text-xs text-slate-500">Record wholesale or retail milk distribution to buyers & centers</p>
            </div>
        </div>
        <a href="{{ route('milk-sales.index') }}" class="px-3.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
            <span>Open Dedicated Milk Sales Page</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <!-- Milk Sale Intake Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>New Milk Sale Entry</span>
            </h4>
            <span class="text-[11px] text-slate-400 font-medium">Fields with <span class="text-rose-500">*</span> are required</span>
        </div>

        <form method="POST" action="{{ route('milk-sales.store') }}" id="tabMilkSaleForm" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="collections">

            <!-- Row 1: Date, Shift, Milk Type -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sale Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Shift <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="shift" value="morning" checked class="peer sr-only">
                            <div class="peer-checked:bg-emerald-600 peer-checked:text-white py-1.5 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Morning
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="shift" value="evening" class="peer sr-only">
                            <div class="peer-checked:bg-emerald-600 peer-checked:text-white py-1.5 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Evening
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Type <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-xl">
                        <label class="cursor-pointer">
                            <input type="radio" name="milk_type" value="cow" checked class="peer sr-only">
                            <div class="peer-checked:bg-emerald-600 peer-checked:text-white py-1.5 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Cow
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="milk_type" value="buffalo" class="peer sr-only">
                            <div class="peer-checked:bg-emerald-600 peer-checked:text-white py-1.5 text-center text-xs font-bold rounded-lg text-slate-600 transition">
                                Buffalo
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Row 2: Customer, Liters, Kg, FAT, CLR, SNF -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3">
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Customer (Buyer) <span class="text-rose-500">*</span></label>
                    <select name="buyer_id" id="tabBuyerSelect" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none font-medium">
                        <option value="">Select Customer...</option>
                        @foreach($buyers ?? [] as $b)
                            <option value="{{ $b->id }}" data-cow="{{ $b->cow_fixed_rate }}" data-buffalo="{{ $b->buffalo_fixed_rate }}">
                                [{{ $b->buyer_code }}] {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Qty. (Ltr.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity_liters" id="tabQtyInput" placeholder="0.00" required oninput="calcTabSale()" class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Milk Qty. (kg.)</label>
                    <input type="number" step="0.01" min="0" name="quantity_kg" id="tabQtyKgInput" placeholder="0.00" class="w-full px-3 py-2 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">FAT (%)</label>
                    <input type="number" step="0.1" min="0" max="20" name="fat_percentage" id="tabFatInput" placeholder="0.00" class="w-full px-3 py-2 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">CLR</label>
                    <input type="number" step="0.5" min="0" max="50" name="clr_reading" id="tabClrInput" placeholder="0.00" class="w-full px-3 py-2 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">SNF (%)</label>
                    <input type="number" step="0.01" min="0" max="20" name="snf_percentage" id="tabSnfInput" placeholder="0.00" class="w-full px-3 py-2 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <!-- Row 3: Rate, Paid, Balance, Description, Vehicle -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate (₹/Ltr.) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="rate_per_liter" id="tabRateInput" placeholder="0.00" required oninput="calcTabSale()" class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Paid (₹)</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="tabPaidInput" placeholder="0.00" oninput="calcTabSale()" class="w-full px-3 py-2 text-xs font-mono border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Balance (₹)</label>
                    <input type="text" id="tabBalanceInput" readonly placeholder="0.00" class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-100 text-rose-700 outline-none">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description / Vehicle</label>
                    <div class="grid grid-cols-2 gap-2">
                        <select name="vehicle_id" class="w-full px-2.5 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                            <option value="">No Vehicle</option>
                            @foreach($vehicles ?? [] as $v)
                                <option value="{{ $v->id }}">{{ $v->vehicle_number }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="description" placeholder="Remarks..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none">
                    </div>
                </div>
            </div>

            <!-- Submit Bar -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-semibold">Total Amount:</span>
                    <span class="text-base font-black text-emerald-700 ml-1">₹ <span id="tabTotalDisplay">0.00</span></span>
                </div>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Milk Sale</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Milk Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Recent Milk Sales</h4>
            <a href="{{ route('milk-sales.index') }}" class="text-xs text-emerald-700 font-bold hover:underline">
                View All &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">Sale #</th>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Buyer</th>
                        <th class="py-2.5 px-3">Shift / Type</th>
                        <th class="py-2.5 px-3 text-right">Qty (Ltr)</th>
                        <th class="py-2.5 px-3 text-right">Qty (kg)</th>
                        <th class="py-2.5 px-3 text-right">Rate</th>
                        <th class="py-2.5 px-3 text-right">Total</th>
                        <th class="py-2.5 px-3 text-right">Paid</th>
                        <th class="py-2.5 px-3 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentMilkSales ?? [] as $ms)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-2 px-3 font-mono font-bold text-slate-800">{{ $ms->sale_number }}</td>
                            <td class="py-2 px-3 font-medium">{{ $ms->sale_date ? $ms->sale_date->format('d M') : '—' }}</td>
                            <td class="py-2 px-3 font-bold text-slate-900">{{ $ms->buyer->name ?? 'Buyer' }}</td>
                            <td class="py-2 px-3 capitalize text-[11px]">{{ $ms->shift }} • {{ $ms->milk_type }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold">{{ number_format($ms->quantity_liters, 2) }} L</td>
                            <td class="py-2 px-3 text-right font-mono text-slate-600">
                                {{ number_format((float)($ms->quantity_kg ?? 0), 2) }} kg
                            </td>
                            <td class="py-2 px-3 text-right font-mono">₹ {{ number_format($ms->rate_per_liter, 2) }}</td>
                            <td class="py-2 px-3 text-right font-mono font-black text-emerald-700">₹ {{ number_format($ms->total_amount, 2) }}</td>
                            <td class="py-2 px-3 text-right font-mono text-emerald-600">₹ {{ number_format($ms->paid_amount, 2) }}</td>
                            <td class="py-2 px-3 text-right font-mono text-rose-600 font-bold">₹ {{ number_format($ms->balance_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-slate-400">No milk sales recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function calcTabSale() {
        const qty = parseFloat(document.getElementById('tabQtyInput')?.value) || 0;
        const rate = parseFloat(document.getElementById('tabRateInput')?.value) || 0;
        const paid = parseFloat(document.getElementById('tabPaidInput')?.value) || 0;
        const total = (qty * rate);
        const bal = Math.max(0, total - paid);

        const totalDisp = document.getElementById('tabTotalDisplay');
        const balInput = document.getElementById('tabBalanceInput');
        if (totalDisp) totalDisp.textContent = total.toFixed(2);
        if (balInput) balInput.value = bal.toFixed(2);
    }
</script>
