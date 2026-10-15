@php
    $s = $suite ?? $mobileDairyData ?? [];
    $totalMilkQuantity = (float) ($s['totalMilkQuantity'] ?? 0);
    $currentPayableBalance = (float) ($s['currentPayableBalance'] ?? ($farmer->current_balance ?? 0));
    $outstandingLoan = (float) ($s['outstandingLoan'] ?? 0);
    $productCreditBalance = (float) ($s['productCreditBalance'] ?? 0);
    $emergencyBalance = (float) ($s['emergencyBalance'] ?? 0);
    $resolvedCowRate = (float) ($s['resolvedCowRate'] ?? ($farmer->cow_milk_rate ?: 42.0));
    $resolvedBuffaloRate = (float) ($s['resolvedBuffaloRate'] ?? ($farmer->buffalo_milk_rate ?: 62.0));
    $recentCollections = $s['recentCollections'] ?? collect();
    $todayMorningLitres = (float) ($s['todayMorningLitres'] ?? 0);
    $todayEveningLitres = (float) ($s['todayEveningLitres'] ?? 0);
    $loans = $s['loans'] ?? collect();
    $totalLoanAmount = (float) ($s['totalLoanAmount'] ?? 0);
    $totalLoanRepaid = (float) ($s['totalLoanRepaid'] ?? 0);
    $deductions = $s['deductions'] ?? collect();
    $totalDeductions = (float) ($s['totalDeductions'] ?? 0);
    $appliedDeductions = (float) ($s['appliedDeductions'] ?? 0);
    $pendingDeductions = (float) ($s['pendingDeductions'] ?? 0);
    $products = $s['products'] ?? collect();
    $farmerProductSales = $s['farmerProductSales'] ?? collect();
    $totalProductPurchases = (float) ($s['totalProductPurchases'] ?? 0);
    $totalProductPaid = (float) ($s['totalProductPaid'] ?? 0);
    $emergencyList = $s['emergencyList'] ?? collect();
    $totalEmergencyAmount = (float) ($s['totalEmergencyAmount'] ?? 0);
    $totalEmergencyRecovered = (float) ($s['totalEmergencyRecovered'] ?? 0);
    $invoices = $s['invoices'] ?? collect();
    $passbookEntries = $s['passbookEntries'] ?? [];
    $openingBalance = (float) ($s['openingBalance'] ?? 0);
    $totalCredit = (float) ($s['totalCredit'] ?? 0);
    $totalDebit = (float) ($s['totalDebit'] ?? 0);
    $closingBalance = (float) ($s['closingBalance'] ?? $currentPayableBalance);
    $stmtFrom = $s['stmtFrom'] ?? request('from_date', now()->startOfMonth()->format('Y-m-d'));
    $stmtTo = $s['stmtTo'] ?? request('to_date', now()->format('Y-m-d'));
@endphp

<div x-data="{
    mdTab: '{{ request('md_tab', 'milk_entry') }}',
    // Modals
    showLoanModal: false,
    showRepayModal: false,
    repayLoanId: '',
    repayLoanMax: 0,
    showDeductionModal: false,
    showProductModal: false,
    showEmergencyModal: false,
    showEmergencyRecoverModal: false,
    emergencyFundId: '',
    emergencyFundMax: 0,
    showCorrectionModal: false,
    corrItem: null,
    
    // Live Milk Entry state
    milkType: '{{ strtolower($farmer->animal_type ?? 'cow') === 'buffalo' ? 'buffalo' : 'cow' }}',
    collectionDate: '{{ now()->format('Y-m-d') }}',
    shift: '{{ now()->hour < 15 ? 'Morning' : 'Evening' }}',
    liters: '',
    fat: '{{ strtolower($farmer->animal_type ?? 'cow') === 'buffalo' ? '6.5' : '4.0' }}',
    snf: '{{ strtolower($farmer->animal_type ?? 'cow') === 'buffalo' ? '9.0' : '8.5' }}',
    clr: '',
    rate: '{{ strtolower($farmer->animal_type ?? 'cow') === 'buffalo' ? $resolvedBuffaloRate : $resolvedCowRate }}',
    rateLoading: false,
    remarks: '',

    get totalAmount() {
        const q = parseFloat(this.liters) || 0;
        const r = parseFloat(this.rate) || 0;
        return (q * r).toFixed(2);
    },

    recalculateRate() {
        const f = parseFloat(this.fat) || 0;
        const s = parseFloat(this.snf) || 0;
        const c = parseFloat(this.clr) || 0;
        if (!f && !s) return;

        this.rateLoading = true;
        fetch('{{ route('mobile-dairy.calc-rate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                farmer_id: {{ $farmer->id }},
                milk_type: this.milkType,
                fat: f,
                snf: s,
                clr: c
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.rate > 0) {
                this.rate = data.rate;
            }
            this.rateLoading = false;
        })
        .catch(err => {
            this.rateLoading = false;
        });
    },

    openCorrection(item) {
        this.corrItem = item;
        this.showCorrectionModal = true;
    },

    openRepay(loanId, remaining) {
        this.repayLoanId = loanId;
        this.repayLoanMax = remaining;
        this.showRepayModal = true;
    },

    openEmergencyRecover(fundId, balance) {
        this.emergencyFundId = fundId;
        this.emergencyFundMax = balance;
        this.showEmergencyRecoverModal = true;
    }
}" class="space-y-6">

    <!-- ========================================== -->
    <!-- 1. TOP MOBILE DAIRY 8-METRICS BANNER       -->
    <!-- ========================================== -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 text-white rounded-2xl p-5 shadow-lg border border-slate-700">
        <!-- Farmer Identity Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-700/80">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center text-emerald-400 font-extrabold text-lg shrink-0">
                    {{ substr($farmer->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-lg font-bold tracking-tight text-white">{{ $farmer->name }}</h3>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono text-xs font-bold border border-emerald-500/30">
                            {{ $farmer->farmer_code }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $farmer->status === 'active' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                            {{ $farmer->status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-300 mt-1 flex-wrap">
                        <span><i class="inline-block w-3.5 h-3.5 mr-1 text-slate-400" data-lucide="phone"></i>{{ $farmer->phone ?: 'No phone' }}</span>
                        <span><i class="inline-block w-3.5 h-3.5 mr-1 text-slate-400" data-lucide="map-pin"></i>{{ $farmer->village ?: 'Village N/A' }}</span>
                        <span><i class="inline-block w-3.5 h-3.5 mr-1 text-slate-400" data-lucide="building"></i>{{ $farmer->collectionCenter ? $farmer->collectionCenter->name : 'Main Center' }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Access Full-Screen App Mode Button & Phone link -->
            <div class="flex items-center gap-2 self-start lg:self-center">
                @if($farmer->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $farmer->phone) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        <span>WhatsApp</span>
                    </a>
                @endif
                <a href="{{ route('farmers.mobile-dairy', $farmer->id) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-700/80 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center gap-1.5 border border-slate-600 transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Full Software View</span>
                </a>
            </div>
        </div>

        <!-- 8 Mandatory Banner Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 pt-4 text-center">
            <!-- 1. Farmer Code & Name -->
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Farmer Code</span>
                <span class="text-sm font-extrabold text-white font-mono mt-0.5 block truncate">{{ $farmer->farmer_code }}</span>
                <span class="text-[10px] text-slate-400 truncate block">{{ Str::limit($farmer->name, 12) }}</span>
            </div>

            <!-- 2. Mobile Number -->
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Mobile</span>
                <span class="text-xs font-bold text-white mt-1 block truncate">{{ $farmer->phone ?: '—' }}</span>
                <span class="text-[10px] text-emerald-400 block">Verified SMS</span>
            </div>

            <!-- 3. Village & Center -->
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Village / Center</span>
                <span class="text-xs font-bold text-white mt-1 block truncate">{{ $farmer->village ?: '—' }}</span>
                <span class="text-[10px] text-slate-400 block truncate">{{ $farmer->collectionCenter ? Str::limit($farmer->collectionCenter->name, 12) : 'Main' }}</span>
            </div>

            <!-- 4. Current Milk Rate -->
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Current Milk Rate</span>
                <div class="text-xs font-bold text-amber-300 mt-1">
                    <span>C: ₹{{ number_format($resolvedCowRate, 1) }}</span>
                    <span class="mx-0.5">|</span>
                    <span>B: ₹{{ number_format($resolvedBuffaloRate, 1) }}</span>
                </div>
                <span class="text-[10px] text-slate-400 block">Per Litre</span>
            </div>

            <!-- 5. Total Milk Quantity -->
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700/60">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Milk Qty</span>
                <span class="text-sm font-black text-sky-400 mt-0.5 block">{{ number_format($totalMilkQuantity, 1) }} L</span>
                <span class="text-[10px] text-slate-400 block">All-Time Intake</span>
            </div>

            <!-- 6. Current Payable Balance -->
            <div class="bg-emerald-900/40 p-2.5 rounded-xl border border-emerald-500/40">
                <span class="text-[10px] uppercase font-bold text-emerald-300 block tracking-wider">Payable Balance</span>
                <span class="text-sm font-black text-emerald-300 mt-0.5 block">₹ {{ number_format($currentPayableBalance, 2) }}</span>
                <span class="text-[10px] text-emerald-400 font-semibold block">Dairy Dues</span>
            </div>

            <!-- 7. Outstanding Loan -->
            <div class="bg-rose-950/40 p-2.5 rounded-xl border border-rose-500/30">
                <span class="text-[10px] uppercase font-bold text-rose-300 block tracking-wider">Outstanding Loan</span>
                <span class="text-sm font-black text-rose-300 mt-0.5 block">₹ {{ number_format($outstandingLoan, 2) }}</span>
                <span class="text-[10px] text-rose-400 block">Recoverable</span>
            </div>

            <!-- 8. Product Credit Balance -->
            <div class="bg-slate-800/80 p-2.5 rounded-xl border border-slate-700/60">
                <span class="text-[10px] uppercase font-bold text-amber-300 block tracking-wider">Product Credit</span>
                <span class="text-sm font-black text-amber-300 mt-0.5 block">₹ {{ number_format($productCreditBalance, 2) }}</span>
                <span class="text-[10px] text-slate-400 block">Feed / Meds Due</span>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. THE 8 DEDICATED MOBILE DAIRY TABS       -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Tab Navigation Bar -->
        <div class="border-b border-slate-200 bg-slate-50/70 px-4 pt-3 overflow-x-auto">
            <nav class="flex space-x-2 sm:space-x-3 text-xs font-bold whitespace-nowrap min-w-max pb-3">
                <!-- Tab 1: Milk Entry -->
                <button type="button" @click="mdTab = 'milk_entry'" :class="mdTab === 'milk_entry' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="droplet" class="w-4 h-4"></i>
                    <span>Milk Entry</span>
                    <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="mdTab === 'milk_entry' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'">{{ $recentCollections->count() }}</span>
                </button>

                <!-- Tab 2: Loan -->
                <button type="button" @click="mdTab = 'loan'" :class="mdTab === 'loan' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Loan</span>
                    @if($outstandingLoan > 0)
                        <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full bg-rose-100 text-rose-700 font-bold">₹{{ number_format($outstandingLoan, 0) }}</span>
                    @endif
                </button>

                <!-- Tab 3: Deduction -->
                <button type="button" @click="mdTab = 'deduction'" :class="mdTab === 'deduction' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="scissors" class="w-4 h-4"></i>
                    <span>Deduction</span>
                    <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="mdTab === 'deduction' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'">{{ $deductions->count() }}</span>
                </button>

                <!-- Tab 4: Product -->
                <button type="button" @click="mdTab = 'product'" :class="mdTab === 'product' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Product</span>
                    <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="mdTab === 'product' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'">{{ $farmerProductSales->count() }}</span>
                </button>

                <!-- Tab 5: Emergency Fund -->
                <button type="button" @click="mdTab = 'emergency'" :class="mdTab === 'emergency' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Emergency Fund</span>
                    @if($emergencyBalance > 0)
                        <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full bg-amber-100 text-amber-800 font-bold">₹{{ number_format($emergencyBalance, 0) }}</span>
                    @endif
                </button>

                <!-- Tab 6: Invoice -->
                <button type="button" @click="mdTab = 'invoice'" :class="mdTab === 'invoice' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Invoice</span>
                    <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="mdTab === 'invoice' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'">{{ $invoices->count() }}</span>
                </button>

                <!-- Tab 7: Statement -->
                <button type="button" @click="mdTab = 'statement'" :class="mdTab === 'statement' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Statement</span>
                </button>

                <!-- Tab 8: All Passbook -->
                <button type="button" @click="mdTab = 'all_passbook'" :class="mdTab === 'all_passbook' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white border border-slate-200'" class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    <span>All Passbook</span>
                    <span class="ml-1 text-[10px] px-1.5 py-0.2 rounded-full" :class="mdTab === 'all_passbook' ? 'bg-emerald-700 text-white' : 'bg-slate-100 text-slate-600'">{{ count($passbookEntries) }}</span>
                </button>
            </nav>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1 CONTENT: MILK ENTRY                  -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'milk_entry'" class="p-5 space-y-6">
            <!-- Fast Milk Collection Form -->
            <div class="bg-gradient-to-r from-emerald-50/50 via-teal-50/30 to-slate-50 p-5 rounded-2xl border border-emerald-200/80">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100 mb-4">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Daily Milk Collection Entry</span>
                        </h4>
                        <p class="text-[11px] text-slate-500">Auto-rate lookup based on configured dairy rate chart and farmer profile.</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Today Collected</span>
                        <span class="text-xs font-black text-emerald-700">M: {{ number_format($todayMorningLitres, 1) }} L | E: {{ number_format($todayEveningLitres, 1) }} L</span>
                    </div>
                </div>

                <form action="{{ route('mobile-dairy.milk-entry.store', $farmer->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                        <!-- Date -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Date *</label>
                            <input type="date" name="collection_date" x-model="collectionDate" required class="w-full text-xs font-semibold px-2.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>

                        <!-- Shift -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Shift *</label>
                            <select name="shift" x-model="shift" required class="w-full text-xs font-semibold px-2.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                                <option value="Morning">Morning</option>
                                <option value="Evening">Evening</option>
                            </select>
                        </div>

                        <!-- Milk Type -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Milk Type *</label>
                            <select name="milk_type" x-model="milkType" @change="recalculateRate()" required class="w-full text-xs font-bold px-2.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white text-emerald-800">
                                <option value="cow">Cow Milk</option>
                                <option value="buffalo">Buffalo Milk</option>
                            </select>
                        </div>

                        <!-- Quantity (Liters) -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Quantity (L) *</label>
                            <input type="number" step="0.01" name="quantity_liters" x-model="liters" required placeholder="0.00" class="w-full text-xs font-black px-2.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>

                        <!-- FAT % -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Fat % *</label>
                            <input type="number" step="0.01" name="fat" x-model="fat" @input.debounce.400ms="recalculateRate()" required placeholder="4.0" class="w-full text-xs font-bold px-2.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>

                        <!-- SNF % -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">SNF % *</label>
                            <input type="number" step="0.01" name="snf" x-model="snf" @input.debounce.400ms="recalculateRate()" required placeholder="8.5" class="w-full text-xs font-bold px-2.5 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>

                        <!-- Rate (Per Litre) -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-700">Rate (₹) *</label>
                                <span x-show="rateLoading" class="text-[9px] text-emerald-600 font-bold animate-spin">⟳</span>
                            </div>
                            <input type="number" step="0.01" name="applied_rate" x-model="rate" required class="w-full text-xs font-black px-2.5 py-2 rounded-xl border border-emerald-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-emerald-50/50 text-emerald-900">
                        </div>

                        <!-- Milk Amount Display -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Total (₹)</label>
                            <div class="w-full text-xs font-black px-2.5 py-2 rounded-xl bg-emerald-600 text-white text-center flex items-center justify-center">
                                ₹ <span x-text="totalAmount">0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lower action line: Remarks, Duplicate override, and Save Button -->
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-emerald-100/70">
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                <input type="checkbox" name="allow_duplicate" value="1" id="chk_allow_duplicate" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                <label for="chk_allow_duplicate" class="text-[11px] font-semibold text-slate-600 cursor-pointer">Allow duplicate shift entry</label>
                            </div>
                            <input type="text" name="remarks" x-model="remarks" placeholder="Optional operator remarks / can number..." class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white placeholder-slate-400 w-64">
                        </div>

                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Save Milk Collection</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Procurement Records Table -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider">Recent Milk Collections (Scoped to {{ $farmer->farmer_code }})</h5>
                    <span class="text-xs text-slate-500">Showing latest entries with audit history</span>
                </div>

                <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-4">Receipt</th>
                                <th class="py-2.5 px-4">Date & Shift</th>
                                <th class="py-2.5 px-4">Type</th>
                                <th class="py-2.5 px-4 text-right">Quantity</th>
                                <th class="py-2.5 px-4 text-center">Fat / SNF</th>
                                <th class="py-2.5 px-4 text-right">Rate</th>
                                <th class="py-2.5 px-4 text-right">Total Amount</th>
                                <th class="py-2.5 px-4 text-center">Status</th>
                                <th class="py-2.5 px-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentCollections as $col)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $col->receipt_number }}</td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-800">{{ $col->collection_date->format('d M Y') }}</span>
                                        <span class="block text-[10px] font-bold text-slate-400 capitalize">{{ $col->shift }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold capitalize {{ $col->milk_type === 'buffalo' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ $col->milk_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900">{{ number_format($col->quantity_liters, 2) }} L</td>
                                    <td class="py-3 px-4 text-center font-semibold">
                                        <span class="text-slate-800">{{ number_format($col->fat, 1) }}%</span> / 
                                        <span class="text-slate-500">{{ number_format($col->snf, 1) }}%</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold">₹ {{ number_format($col->applied_rate, 2) }}</td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-700">₹ {{ number_format($col->net_amount, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $col->payment_status === 'settled' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $col->payment_status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" @click="openCorrection({{ json_encode($col) }})" class="p-1 rounded hover:bg-slate-100 text-slate-500 hover:text-emerald-600 transition" title="Correct / Adjust Entry">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">No milk entries recorded yet for this farmer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2 CONTENT: LOAN MANAGEMENT             -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'loan'" class="p-5 space-y-6">
            <!-- Loan KPI Cards & Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total Loans Disbursed</span>
                    <span class="text-xl font-black text-slate-900 mt-1 block">₹ {{ number_format($totalLoanAmount, 2) }}</span>
                    <span class="text-[10px] text-slate-500">Cumulative principal</span>
                </div>
                <div class="bg-emerald-50/60 p-4 rounded-xl border border-emerald-200">
                    <span class="text-[10px] uppercase font-bold text-emerald-700 block tracking-wider">Total Repaid</span>
                    <span class="text-xl font-black text-emerald-700 mt-1 block">₹ {{ number_format($totalLoanRepaid, 2) }}</span>
                    <span class="text-[10px] text-emerald-600">Settled via deductions & cash</span>
                </div>
                <div class="bg-rose-50/60 p-4 rounded-xl border border-rose-200 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-rose-700 block tracking-wider">Outstanding Balance</span>
                        <span class="text-xl font-black text-rose-700 mt-1 block">₹ {{ number_format($outstandingLoan, 2) }}</span>
                        <span class="text-[10px] text-rose-600">Active dues</span>
                    </div>
                    <button type="button" @click="showLoanModal = true" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>New Loan</span>
                    </button>
                </div>
            </div>

            <!-- Loans & Repayments Table -->
            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Loan #</th>
                            <th class="py-2.5 px-4">Purpose / Approved By</th>
                            <th class="py-2.5 px-4 text-right">Original Amount</th>
                            <th class="py-2.5 px-4 text-right">Repaid</th>
                            <th class="py-2.5 px-4 text-right">Balance Due</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                            <th class="py-2.5 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($loans as $ln)
                            @php
                                $repaid = (float) $ln->amount - (float) $ln->principal_balance;
                                $bal = (float) $ln->principal_balance;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono">{{ $ln->advance_date ? \Carbon\Carbon::parse($ln->advance_date)->format('d M Y') : '—' }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $ln->reference_no ?: 'LN-' . $ln->id }}</td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-800 block">{{ $ln->purpose ?: 'General Advance' }}</span>
                                    <span class="text-[10px] text-slate-400">Approved by: {{ $ln->approved_by ?: 'Dairy Admin' }}</span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">₹ {{ number_format($ln->amount, 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600">₹ {{ number_format($repaid, 2) }}</td>
                                <td class="py-3 px-4 text-right font-black text-rose-600">₹ {{ number_format($bal, 2) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $bal <= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $bal <= 0 ? 'Fully Paid' : 'Active' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($bal > 0)
                                        <button type="button" @click="openRepay({{ $ln->id }}, {{ $bal }})" class="px-2 py-1 rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[10px] font-bold border border-emerald-200 transition">
                                            + Repay
                                        </button>
                                    @else
                                        <span class="text-slate-300 text-[10px]">Closed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">No loans or advances recorded for this farmer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3 CONTENT: DEDUCTION MANAGEMENT        -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'deduction'" class="p-5 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="grid grid-cols-3 gap-3 w-full sm:w-auto">
                    <div class="bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Deductions</span>
                        <span class="text-base font-black text-slate-900">₹ {{ number_format($totalDeductions, 2) }}</span>
                    </div>
                    <div class="bg-emerald-50/60 px-4 py-2.5 rounded-xl border border-emerald-200">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 block">Applied</span>
                        <span class="text-base font-black text-emerald-700">₹ {{ number_format($appliedDeductions, 2) }}</span>
                    </div>
                    <div class="bg-amber-50/60 px-4 py-2.5 rounded-xl border border-amber-200">
                        <span class="text-[10px] uppercase font-bold text-amber-700 block">Pending</span>
                        <span class="text-base font-black text-amber-700">₹ {{ number_format($pendingDeductions, 2) }}</span>
                    </div>
                </div>

                <button type="button" @click="showDeductionModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>+ Add Deduction</span>
                </button>
            </div>

            <!-- Deductions Table -->
            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Type</th>
                            <th class="py-2.5 px-4">Description</th>
                            <th class="py-2.5 px-4">Reference No</th>
                            <th class="py-2.5 px-4 text-right">Amount</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($deductions as $ded)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono">{{ $ded->deduction_date ? \Carbon\Carbon::parse($ded->deduction_date)->format('d M Y') : '—' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 capitalize">
                                        {{ str_replace('_', ' ', $ded->deduction_type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $ded->description ?: '—' }}</td>
                                <td class="py-3 px-4 font-mono text-slate-500">{{ $ded->reference_number ?: '—' }}</td>
                                <td class="py-3 px-4 text-right font-black text-rose-600">₹ {{ number_format($ded->amount, 2) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $ded->status === 'applied' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $ded->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">No deductions registered for this farmer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4 CONTENT: PRODUCT SALES               -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'product'" class="p-5 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="grid grid-cols-3 gap-3 w-full sm:w-auto">
                    <div class="bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Purchases</span>
                        <span class="text-base font-black text-slate-900">₹ {{ number_format($totalProductPurchases, 2) }}</span>
                    </div>
                    <div class="bg-emerald-50/60 px-4 py-2.5 rounded-xl border border-emerald-200">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 block">Paid</span>
                        <span class="text-base font-black text-emerald-700">₹ {{ number_format($totalProductPaid, 2) }}</span>
                    </div>
                    <div class="bg-amber-50/60 px-4 py-2.5 rounded-xl border border-amber-200">
                        <span class="text-[10px] uppercase font-bold text-amber-700 block">Credit Balance Due</span>
                        <span class="text-base font-black text-amber-700">₹ {{ number_format($productCreditBalance, 2) }}</span>
                    </div>
                </div>

                <button type="button" @click="showProductModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                    <span>+ Issue Product / Feed</span>
                </button>
            </div>

            <!-- Product Purchases Table -->
            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Invoice #</th>
                            <th class="py-2.5 px-4">Product</th>
                            <th class="py-2.5 px-4 text-center">Qty</th>
                            <th class="py-2.5 px-4 text-right">Unit Price</th>
                            <th class="py-2.5 px-4 text-right">Total</th>
                            <th class="py-2.5 px-4 text-right">Remaining Due</th>
                            <th class="py-2.5 px-4 text-center">Payment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($farmerProductSales as $sale)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono">{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') : '—' }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $sale->invoice_number ?: '#' . $sale->id }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $sale->product ? $sale->product->name : ($sale->item_name ?: 'Feed / Supply') }}</td>
                                <td class="py-3 px-4 text-center font-bold">{{ $sale->quantity }} {{ $sale->unit ?: 'Bag' }}</td>
                                <td class="py-3 px-4 text-right">₹ {{ number_format($sale->unit_price, 2) }}</td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">₹ {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="py-3 px-4 text-right font-bold text-amber-700">₹ {{ number_format($sale->remaining_amount, 2) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ (float)$sale->remaining_amount <= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ (float)$sale->remaining_amount <= 0 ? 'Paid' : 'Credit Due' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">No product purchases recorded for this farmer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5 CONTENT: EMERGENCY FUND              -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'emergency'" class="p-5 space-y-6">
            <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-200/80 flex items-start gap-3">
                <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                <div class="text-xs text-amber-900">
                    <span class="font-bold block">Isolated Emergency Assistance Protection</span>
                    Emergency funds are tracked separately from commercial loans and are not automatically debited against milk payouts unless explicitly authorized in the settlement.
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="grid grid-cols-3 gap-3 w-full sm:w-auto">
                    <div class="bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Total Granted</span>
                        <span class="text-base font-black text-slate-900">₹ {{ number_format($totalEmergencyAmount, 2) }}</span>
                    </div>
                    <div class="bg-emerald-50/60 px-4 py-2.5 rounded-xl border border-emerald-200">
                        <span class="text-[10px] uppercase font-bold text-emerald-700 block">Recovered</span>
                        <span class="text-base font-black text-emerald-700">₹ {{ number_format($totalEmergencyRecovered, 2) }}</span>
                    </div>
                    <div class="bg-rose-50/60 px-4 py-2.5 rounded-xl border border-rose-200">
                        <span class="text-[10px] uppercase font-bold text-rose-700 block">Active Advance</span>
                        <span class="text-base font-black text-rose-700">₹ {{ number_format($emergencyBalance, 2) }}</span>
                    </div>
                </div>

                <button type="button" @click="showEmergencyModal = true" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>+ New Emergency Assistance</span>
                </button>
            </div>

            <!-- Emergency Fund Table -->
            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Ref #</th>
                            <th class="py-2.5 px-4">Reason / Purpose</th>
                            <th class="py-2.5 px-4">Approved By</th>
                            <th class="py-2.5 px-4 text-right">Amount</th>
                            <th class="py-2.5 px-4 text-right">Recovered</th>
                            <th class="py-2.5 px-4 text-right">Balance</th>
                            <th class="py-2.5 px-4 text-center">Settlement Link</th>
                            <th class="py-2.5 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($emergencyList as $em)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono">{{ $em->request_date ? \Carbon\Carbon::parse($em->request_date)->format('d M Y') : '—' }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $em->reference_no }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $em->reason }}</td>
                                <td class="py-3 px-4 text-slate-600">{{ $em->approved_by ?: 'Secretary' }}</td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">₹ {{ number_format($em->amount, 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600">₹ {{ number_format($em->recovered_amount, 2) }}</td>
                                <td class="py-3 px-4 text-right font-black text-rose-600">₹ {{ number_format($em->balance, 2) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $em->allow_settlement_recovery ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $em->allow_settlement_recovery ? 'Auto-Deductible' : 'Manual Only' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($em->balance > 0)
                                        <button type="button" @click="openEmergencyRecover({{ $em->id }}, {{ $em->balance }})" class="px-2 py-1 rounded bg-amber-50 text-amber-700 hover:bg-amber-100 text-[10px] font-bold border border-amber-200 transition">
                                            + Recover
                                        </button>
                                    @else
                                        <span class="text-emerald-600 font-bold text-[10px]">Settled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">No emergency assistance records for this farmer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 6 CONTENT: INVOICE & SETTLEMENT        -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'invoice'" class="p-5 space-y-6">
            <!-- Period Settlement Generator Card -->
            <div class="bg-gradient-to-r from-emerald-50/60 to-slate-50 p-5 rounded-2xl border border-emerald-200">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100 mb-4">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
                            <span>Generate Period Settlement Invoice</span>
                        </h4>
                        <p class="text-[11px] text-slate-500">Invoices are initially pushed to the <b>Pending</b> stage for verification and correction before approval.</p>
                    </div>
                </div>

                <form action="{{ route('mobile-dairy.invoices.store', $farmer->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Settlement Cycle</label>
                            <select name="settlement_cycle" class="w-full text-xs font-semibold px-2.5 py-2 rounded-xl border border-slate-300 bg-white">
                                <option value="fortnightly">Fortnightly (10/15 Days)</option>
                                <option value="weekly">Weekly Cycle</option>
                                <option value="monthly">Monthly Cycle</option>
                                <option value="daily">Daily Cycle</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">From Date *</label>
                            <input type="date" name="from_date" value="{{ now()->subDays(10)->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-2.5 py-2 rounded-xl border border-slate-300 bg-white">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">To Date *</label>
                            <input type="date" name="to_date" value="{{ now()->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-2.5 py-2 rounded-xl border border-slate-300 bg-white">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Deduct Loan (Max: ₹{{ number_format($outstandingLoan, 0) }})</label>
                            <input type="number" step="0.01" name="loan_recovery" max="{{ $outstandingLoan }}" placeholder="0.00" class="w-full text-xs font-bold px-2.5 py-2 rounded-xl border border-slate-300 bg-white">
                        </div>

                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Deduct Product Due (Max: ₹{{ number_format($productCreditBalance, 0) }})</label>
                            <input type="number" step="0.01" name="product_recovery" max="{{ $productCreditBalance }}" placeholder="0.00" class="w-full text-xs font-bold px-2.5 py-2 rounded-xl border border-slate-300 bg-white">
                        </div>

                        <div class="space-y-1 flex flex-col justify-end">
                            <button type="submit" class="w-full py-2 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="file-plus" class="w-4 h-4"></i>
                                <span>Generate Invoice</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Invoices List -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider">Settlement Invoices & Slips</h5>
                    <span class="text-xs text-slate-500">Pending review, approved & paid</span>
                </div>

                <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-4">Invoice #</th>
                                <th class="py-2.5 px-4">Period</th>
                                <th class="py-2.5 px-4 text-right">Milk Liters</th>
                                <th class="py-2.5 px-4 text-right">Milk Earnings</th>
                                <th class="py-2.5 px-4 text-right">Deductions</th>
                                <th class="py-2.5 px-4 text-right">Net Payable</th>
                                <th class="py-2.5 px-4 text-center">Status</th>
                                <th class="py-2.5 px-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoices as $inv)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $inv->invoice_number }}</td>
                                    <td class="py-3 px-4 font-semibold">
                                        {{ \Carbon\Carbon::parse($inv->from_date)->format('d M') }} – {{ \Carbon\Carbon::parse($inv->to_date)->format('d M Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold">{{ number_format($inv->total_liters ?? $inv->milk_liters, 2) }} L</td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900">₹ {{ number_format($inv->milk_amount, 2) }}</td>
                                    <td class="py-3 px-4 text-right font-semibold text-rose-600">₹ {{ number_format($inv->total_deductions, 2) }}</td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-700 text-sm">₹ {{ number_format($inv->net_amount, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                            {{ $inv->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <!-- Print Slip -->
                                            <a href="{{ route('mobile-dairy.invoices.print', $inv->id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition" title="Print Settlement Slip">
                                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            </a>
                                            <!-- Mark Paid if not yet paid -->
                                            @if($inv->status !== 'paid')
                                                <form action="{{ route('mobile-dairy.invoices.mark-paid', $inv->id) }}" method="POST" onsubmit="return confirm('Confirm marking invoice as PAID? This will post the disbursement transaction.');">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold transition flex items-center gap-1">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        <span>Pay</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400">No invoices generated yet for this farmer.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 7 CONTENT: STATEMENT                   -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'statement'" class="p-5 space-y-6">
            <!-- Filter Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <form method="GET" action="{{ route('farmers.show', $farmer->id) }}" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="mobile_dairy">
                    <input type="hidden" name="md_tab" value="statement">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">From:</label>
                        <input type="date" name="from_date" value="{{ $stmtFrom }}" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">To:</label>
                        <input type="date" name="to_date" value="{{ $stmtTo }}" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white">
                    </div>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                        Filter Statement
                    </button>
                </form>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="px-3 py-1.5 rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>

            <!-- Statement Summary KPIs -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Opening Balance</span>
                    <span class="text-base font-black text-slate-900 mt-0.5 block">₹ {{ number_format($openingBalance, 2) }}</span>
                </div>
                <div class="bg-emerald-50/60 p-3 rounded-xl border border-emerald-200">
                    <span class="text-[10px] font-bold text-emerald-700 uppercase block">Total Credits (Milk + Additions)</span>
                    <span class="text-base font-black text-emerald-700 mt-0.5 block">₹ {{ number_format($totalCredit, 2) }}</span>
                </div>
                <div class="bg-rose-50/60 p-3 rounded-xl border border-rose-200">
                    <span class="text-[10px] font-bold text-rose-700 uppercase block">Total Debits (Deductions / Payouts)</span>
                    <span class="text-base font-black text-rose-700 mt-0.5 block">₹ {{ number_format($totalDebit, 2) }}</span>
                </div>
                <div class="bg-emerald-100/50 p-3 rounded-xl border border-emerald-300">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase block">Closing Balance</span>
                    <span class="text-base font-black text-emerald-900 mt-0.5 block">₹ {{ number_format($closingBalance, 2) }}</span>
                </div>
            </div>

            <!-- Statement Rows -->
            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Reference No</th>
                            <th class="py-2.5 px-4">Particulars</th>
                            <th class="py-2.5 px-4 text-right">Debit (₹)</th>
                            <th class="py-2.5 px-4 text-right">Credit (₹)</th>
                            <th class="py-2.5 px-4 text-right">Running Balance (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($passbookEntries as $entry)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono">{{ $entry['date'] }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-700">{{ $entry['ref'] }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $entry['description'] }}</td>
                                <td class="py-3 px-4 text-right font-black text-rose-600">
                                    {{ $entry['debit'] > 0 ? '₹ ' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="py-3 px-4 text-right font-black text-emerald-600">
                                    {{ $entry['credit'] > 0 ? '₹ ' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">
                                    ₹ {{ number_format($entry['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">No transactions recorded for the chosen period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 8 CONTENT: ALL PASSBOOK                -->
        <!-- ========================================== -->
        <div x-show="mdTab === 'all_passbook'" class="p-5 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider">All-in-One Reconciled Farmer Passbook</h5>
                    <p class="text-[11px] text-slate-500">Includes milk supply, advances, repayments, deductions, products, emergency funds & payouts.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('farmers.passbook.statement', $farmer->id) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Download / Print PDF</span>
                    </a>
                </div>
            </div>

            <!-- Passbook Full Table -->
            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Date & Time</th>
                            <th class="py-2.5 px-4">Transaction No</th>
                            <th class="py-2.5 px-4">Category</th>
                            <th class="py-2.5 px-4">Particulars</th>
                            <th class="py-2.5 px-4 text-right">Milk / Credit</th>
                            <th class="py-2.5 px-4 text-right">Debit / Deduction</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                            <th class="py-2.5 px-4 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($passbookEntries as $p)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 font-mono">{{ $p['date'] }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $p['ref'] }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p['credit'] > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $p['type'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">{{ $p['description'] }}</td>
                                <td class="py-3 px-4 text-right font-black text-emerald-600">
                                    {{ $p['credit'] > 0 ? '₹ ' . number_format($p['credit'], 2) : '—' }}
                                </td>
                                <td class="py-3 px-4 text-right font-black text-rose-600">
                                    {{ $p['debit'] > 0 ? '₹ ' . number_format($p['debit'], 2) : '—' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-100 text-slate-600">
                                        {{ $p['status'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">
                                    ₹ {{ number_format($p['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">No passbook records available for this farmer.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODALS SECTION                             -->
    <!-- ========================================== -->

    <!-- Modal 1: Milk Entry Correction Modal -->
    <div x-show="showCorrectionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showCorrectionModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Authorized Milk Entry Correction</h4>
                <button type="button" @click="showCorrectionModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <template x-if="corrItem">
                <form :action="'{{ url('mobile-dairy/milk-entry') }}/' + corrItem.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Quantity (L)</label>
                            <input type="number" step="0.01" name="quantity_liters" :value="corrItem.quantity_liters" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Applied Rate (₹)</label>
                            <input type="number" step="0.01" name="applied_rate" :value="corrItem.applied_rate" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">Fat %</label>
                            <input type="number" step="0.01" name="fat" :value="corrItem.fat" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-slate-700">SNF %</label>
                            <input type="number" step="0.01" name="snf" :value="corrItem.snf" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Correction Reason (Mandatory Audit Log) *</label>
                        <input type="text" name="reason" required placeholder="e.g. Weighing scale recalibrated / lactometer correction" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showCorrectionModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Save Correction</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- Modal 2: Issue New Loan Modal -->
    <div x-show="showLoanModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showLoanModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Issue Farmer Advance / Loan</h4>
                <button type="button" @click="showLoanModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('mobile-dairy.loans.store', $farmer->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Loan Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="5000" class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Disbursement Date *</label>
                        <input type="date" name="advance_date" value="{{ now()->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Purpose / Reason *</label>
                    <input type="text" name="purpose" required placeholder="Cattle purchase, fodder, medical, festival..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Approved By</label>
                        <input type="text" name="approved_by" value="{{ auth()->user()->name ?? 'Admin' }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Payment Mode</label>
                        <select name="payment_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer / NEFT</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showLoanModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700">Disburse Loan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Loan Repayment Modal -->
    <div x-show="showRepayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showRepayModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Record Loan Repayment</h4>
                <button type="button" @click="showRepayModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form :action="'{{ url('mobile-dairy/loans') }}/' + repayLoanId + '/repayment'" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Repayment Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" :max="repayLoanMax" required placeholder="0.00" class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    <span class="text-[10px] text-slate-400">Max remaining balance: ₹<span x-text="repayLoanMax"></span></span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Repayment Date *</label>
                        <input type="date" name="repayment_date" value="{{ now()->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Payment Mode</label>
                        <select name="payment_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                            <option value="Cash">Cash</option>
                            <option value="Milk Deduction">Milk Deduction</option>
                            <option value="Bank">Bank / UPI</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Reference / Remarks</label>
                    <input type="text" name="notes" placeholder="Receipt / receipt reference" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showRepayModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Post Repayment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Add Deduction Modal -->
    <div x-show="showDeductionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showDeductionModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Add Farmer Deduction</h4>
                <button type="button" @click="showDeductionModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('mobile-dairy.deductions.store', $farmer->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Deduction Type *</label>
                    <select name="deduction_type" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        <option value="cattle_feed">Cattle Feed Deduction</option>
                        <option value="medicine">Medicine / Veterinary Doctor</option>
                        <option value="equipment">Equipment or Dairy Supplies</option>
                        <option value="advance_recovery">Advance Recovery</option>
                        <option value="other">Other Approved Charges</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Date *</label>
                        <input type="date" name="deduction_date" value="{{ now()->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Description / Reason *</label>
                    <input type="text" name="description" required placeholder="Item description, bill reference, reason..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Reference Number</label>
                    <input type="text" name="reference_number" placeholder="Optional bill/receipt ref" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showDeductionModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Save Deduction</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Sell Product / Feed Modal -->
    <div x-show="showProductModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showProductModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" x-data="{
            pQty: 1,
            pPrice: 0,
            pDiscount: 0,
            pTax: 0,
            pPaid: 0,
            get pTotal() {
                return Math.max(0, ((parseFloat(this.pQty) || 0) * (parseFloat(this.pPrice) || 0)) - (parseFloat(this.pDiscount) || 0) + (parseFloat(this.pTax) || 0)).toFixed(2);
            },
            onProductChange(e) {
                const opt = e.target.selectedOptions[0];
                if (opt && opt.dataset.price) {
                    this.pPrice = parseFloat(opt.dataset.price);
                }
            }
        }">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Issue Cattle Feed / Product</h4>
                <button type="button" @click="showProductModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('mobile-dairy.products.store', $farmer->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Select Product *</label>
                    <select name="product_id" @change="onProductChange($event)" required class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        <option value="">-- Choose Product --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-price="{{ $prod->price ?? $prod->selling_price }}">
                                {{ $prod->name }} (₹{{ number_format($prod->price ?? $prod->selling_price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Qty *</label>
                        <input type="number" step="0.01" name="quantity" x-model="pQty" required class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Price (₹) *</label>
                        <input type="number" step="0.01" name="unit_price" x-model="pPrice" required class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Total (₹)</label>
                        <div class="w-full text-xs font-black px-2 py-2 rounded-xl bg-slate-100 text-slate-900 text-center flex items-center justify-center">
                            ₹ <span x-text="pTotal">0.00</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Paid Now (₹)</label>
                        <input type="number" step="0.01" name="paid_amount" x-model="pPaid" placeholder="0.00" class="w-full text-xs font-bold px-3 py-2 rounded-xl border border-slate-300">
                        <span class="text-[10px] text-slate-400">Leave 0 for full credit purchase</span>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Payment Mode</label>
                        <select name="payment_method" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                            <option value="Credit">Credit (Milk Deduction)</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showProductModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Complete Sale</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 6: Request Emergency Fund Modal -->
    <div x-show="showEmergencyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEmergencyModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Request / Grant Emergency Assistance</h4>
                <button type="button" @click="showEmergencyModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form action="{{ route('mobile-dairy.emergency.store', $farmer->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Assistance Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="2000" class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Date *</label>
                        <input type="date" name="request_date" value="{{ now()->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Reason / Emergency Situation *</label>
                    <input type="text" name="reason" required placeholder="Medical, urgent veterinary, flood relief..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Approved By</label>
                        <input type="text" name="approved_by" value="{{ auth()->user()->name ?? 'Committee' }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-slate-700">Disbursed Via</label>
                        <select name="disbursement_method" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank Account</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="allow_settlement_recovery" value="1" id="chk_allow_emergency_recovery" class="rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                    <label for="chk_allow_emergency_recovery" class="text-[11px] font-semibold text-slate-700 cursor-pointer">
                        Authorize recovery from future milk settlements
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showEmergencyModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700">Disburse Assistance</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Emergency Fund Recovery Modal -->
    <div x-show="showEmergencyRecoverModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEmergencyRecoverModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h4 class="text-sm font-black text-slate-900">Record Emergency Assistance Recovery</h4>
                <button type="button" @click="showEmergencyRecoverModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form :action="'{{ url('mobile-dairy/emergency') }}/' + emergencyFundId + '/recovery'" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Recovery Amount (₹) *</label>
                    <input type="number" step="0.01" name="amount" :max="emergencyFundMax" required placeholder="0.00" class="w-full text-xs font-black px-3 py-2 rounded-xl border border-slate-300">
                    <span class="text-[10px] text-slate-400">Max recoverable balance: ₹<span x-text="emergencyFundMax"></span></span>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Recovery Date *</label>
                    <input type="date" name="recovery_date" value="{{ now()->format('Y-m-d') }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Recovery Mode</label>
                    <select name="recovery_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-slate-300">
                        <option value="Cash">Cash Receipt</option>
                        <option value="Milk Settlement">Milk Settlement</option>
                        <option value="Bank">Bank / UPI</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Reference / Notes</label>
                    <input type="text" name="reference_no" placeholder="Receipt / transaction number" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showEmergencyRecoverModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700">Record Recovery</button>
                </div>
            </form>
        </div>
    </div>

</div>
