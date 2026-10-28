<div class="space-y-4">
    <!-- Top Action Banner -->
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center justify-center font-bold">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Direct Product & Cattle Feed Sales</h3>
                <p class="text-xs text-slate-500">Sell feed, supplements, mineral mixtures, and dairy products to farmers</p>
            </div>
        </div>
        <a href="{{ route('product-sales.index') }}" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-300 text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5">
            <span>Open Dedicated Product Sales Page</span>
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <!-- Product Sale Entry Form -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>New Product Sale Entry</span>
            </h4>
            <span class="text-[11px] text-slate-400 font-medium">Fields with <span class="text-rose-500">*</span> are required</span>
        </div>

        <form method="POST" action="{{ route('product-sales.store') }}" id="tabProductSaleForm" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="collections">

            <!-- Row 1: Date, Farmer, Product, Unit -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sale Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-indigo-500 outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Farmer / Customer</label>
                    <select name="farmer_id" id="tabProdFarmer" onchange="onTabFarmerSelect(this)" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-indigo-500 outline-none font-medium">
                        <option value="">Walk-in / Cash Customer</option>
                        @foreach($farmers ?? [] as $f)
                            <option value="{{ $f->id }}" data-name="{{ $f->name }}" data-code="{{ $f->farmer_code }}">
                                {{ $f->farmer_code }} - {{ $f->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="farmer_name" id="tabProdFarmerName" value="Walk-in Customer">
                    <input type="hidden" name="farmer_code" id="tabProdFarmerCode" value="">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Product / Item <span class="text-rose-500">*</span></label>
                    <select name="product_id" id="tabProdSelect" required onchange="onTabProductChange(this)" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-indigo-500 outline-none font-medium">
                        <option value="">-- Choose Product --</option>
                        @foreach($products ?? [] as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price ?? $p->price }}" data-unit="{{ $p->unit ?? 'kg' }}" data-name="{{ $p->name }}">
                                {{ $p->name }} (₹{{ number_format($p->selling_price ?? $p->price ?? 0, 2) }}/{{ $p->unit ?? 'unit' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit</label>
                    <input type="text" name="unit" id="tabProdUnit" value="kg" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-indigo-500 outline-none font-medium">
                </div>
            </div>

            <!-- Row 2: Qty, Rate, Total, Payment Mode, Paid Amount -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantity <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="quantity" id="tabProdQty" placeholder="1.00" value="1.00" required oninput="calcTabProdSale()" class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-indigo-500 outline-none text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="rate" id="tabProdRate" placeholder="0.00" required oninput="calcTabProdSale()" class="w-full px-3 py-2 text-xs font-mono font-bold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:border-indigo-500 outline-none text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Total (₹)</label>
                    <input type="text" id="tabProdTotal" readonly placeholder="0.00" class="w-full px-3 py-2 text-xs font-mono font-black border border-slate-200 rounded-xl bg-slate-100 text-indigo-700 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Mode</label>
                    <select name="payment_mode" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                        <option value="Cash">Cash</option>
                        <option value="Ledger">Deduct from Milk / Ledger</option>
                        <option value="UPI">UPI</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Remarks</label>
                    <input type="text" name="remarks" placeholder="Notes..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:bg-white outline-none font-medium">
                </div>
            </div>

            <!-- Submit Bar -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-semibold">Net Sale:</span>
                    <span class="text-base font-black text-indigo-700 ml-1">₹ <span id="tabProdTotalDisp">0.00</span></span>
                </div>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Product Sale</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Product Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Recent Product Sales</h4>
            <a href="{{ route('product-sales.index') }}" class="text-xs text-indigo-700 font-bold hover:underline">
                View All &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">Invoice #</th>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Customer / Farmer</th>
                        <th class="py-2.5 px-3">Product</th>
                        <th class="py-2.5 px-3 text-right">Quantity</th>
                        <th class="py-2.5 px-3 text-right">Rate</th>
                        <th class="py-2.5 px-3 text-right">Total</th>
                        <th class="py-2.5 px-3">Mode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentProductSales ?? [] as $ps)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-2 px-3 font-mono font-bold text-slate-800">{{ $ps->sale_number }}</td>
                            <td class="py-2 px-3 font-medium">{{ $ps->sale_date ? date('d M', strtotime($ps->sale_date)) : '—' }}</td>
                            <td class="py-2 px-3 font-bold text-slate-900">
                                {{ $ps->farmer_name ?? ($ps->farmer->name ?? 'Walk-in') }}
                                @if(!empty($ps->farmer_code))
                                    <span class="text-[10px] text-slate-400 font-normal">({{ $ps->farmer_code }})</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 font-semibold text-indigo-800">{{ $ps->product_name ?? ($ps->product->name ?? 'Item') }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold">{{ number_format($ps->quantity, 2) }} {{ $ps->unit }}</td>
                            <td class="py-2 px-3 text-right font-mono">₹ {{ number_format($ps->rate, 2) }}</td>
                            <td class="py-2 px-3 text-right font-mono font-black text-indigo-700">₹ {{ number_format($ps->total_amount, 2) }}</td>
                            <td class="py-2 px-3 capitalize text-[11px] font-medium">{{ $ps->payment_mode }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">No product sales recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function onTabProductChange(select) {
        const opt = select.options[select.selectedIndex];
        if (!opt) return;
        const price = opt.dataset.price || '0';
        const unit = opt.dataset.unit || 'kg';
        const rateInput = document.getElementById('tabProdRate');
        const unitInput = document.getElementById('tabProdUnit');
        if (rateInput && (!rateInput.value || parseFloat(rateInput.value) === 0)) {
            rateInput.value = price;
        }
        if (unitInput && unit) {
            unitInput.value = unit;
        }
        calcTabProdSale();
    }

    function onTabFarmerSelect(select) {
        const opt = select.options[select.selectedIndex];
        const nameInput = document.getElementById('tabProdFarmerName');
        const codeInput = document.getElementById('tabProdFarmerCode');
        if (opt && opt.value) {
            if (nameInput) nameInput.value = opt.dataset.name || '';
            if (codeInput) codeInput.value = opt.dataset.code || '';
        } else {
            if (nameInput) nameInput.value = 'Walk-in Customer';
            if (codeInput) codeInput.value = '';
        }
    }

    function calcTabProdSale() {
        const qty = parseFloat(document.getElementById('tabProdQty')?.value) || 0;
        const rate = parseFloat(document.getElementById('tabProdRate')?.value) || 0;
        const total = (qty * rate);

        const totalInput = document.getElementById('tabProdTotal');
        const totalDisp = document.getElementById('tabProdTotalDisp');
        if (totalInput) totalInput.value = total.toFixed(2);
        if (totalDisp) totalDisp.textContent = total.toFixed(2);
    }
</script>
