@extends('layouts.app')

@section('title', 'Milk Collection Entry')
@section('breadcrumb', 'Procurement')
@section('header_title', 'New Milk Collection Entry')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="collectionForm()">

    <!-- Shift & Center Selector Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Shift:</span>
            <div class="inline-flex p-1 bg-slate-100 rounded-xl">
                @if(in_array('morning', $collectionConfig['allowed_shifts']))
                <button 
                    type="button" 
                    @click="shift = 'morning'; updateRate();" 
                    :class="shift === 'morning' ? 'bg-amber-400 text-slate-900 font-bold shadow-xs' : 'text-slate-500'" 
                    class="px-3 py-1 text-xs rounded-lg transition flex items-center gap-1.5"
                >
                    <i data-lucide="sun" class="w-3.5 h-3.5"></i>
                    <span>Morning</span>
                </button>
                @endif
                @if(in_array('evening', $collectionConfig['allowed_shifts']))
                <button 
                    type="button" 
                    @click="shift = 'evening'; updateRate();" 
                    :class="shift === 'evening' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'text-slate-500'" 
                    class="px-3 py-1 text-xs rounded-lg transition flex items-center gap-1.5"
                >
                    <i data-lucide="moon" class="w-3.5 h-3.5"></i>
                    <span>Evening</span>
                </button>
                @endif
            </div>

            <a href="{{ route('settings.center-information') }}"
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold hover:bg-blue-100 transition">
                <i data-lucide="settings-2" class="w-3.5 h-3.5"></i>
                <span>Mode: {{ $collectionConfig['collection_type'] }}</span>
            </a>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Date:</span>
            <input type="date" x-model="collectionDate" class="text-xs font-semibold px-2.5 py-1.5 border border-slate-200 rounded-lg bg-slate-50">
        </div>
    </div>

    <!-- Main Entry Form & Live Quality Matrix Card -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Input Form (2 cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-4">Farmer & Quality Parameters</h3>

            <form action="{{ route('collections.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="shift" :value="shift">
                <input type="hidden" name="collection_date" :value="collectionDate">

                <!-- Farmer Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Select Farmer / Supplier *</label>
                    <select 
                        name="farmer_id" 
                        x-model="farmerId" 
                        @change="onFarmerChange()" 
                        required 
                        class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                    >
                        <option value="">-- Choose Farmer by Code or Name --</option>
                        @foreach($farmers as $f)
                            <option value="{{ $f->id }}" data-animal="{{ $f->animal_type }}" data-code="{{ $f->farmer_code }}" data-village="{{ $f->village }}">
                                {{ $f->farmer_code }} - {{ $f->name }} ({{ ucfirst($f->animal_type) }} • {{ $f->village }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Milk Type & Center -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Milk Type *</label>
                        <select name="milk_type" x-model="milkType" @change="updateRate()" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            @if(in_array('cow', $collectionConfig['allowed_milk_types']))
                                <option value="cow">Cow Milk (गाय)</option>
                            @endif
                            @if(in_array('buffalo', $collectionConfig['allowed_milk_types']))
                                <option value="buffalo">Buffalo Milk (भैंस)</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Collection Center</label>
                        <select name="collection_center_id" x-model="centerId" @change="updateRate()" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            @foreach($centers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Quantity & Quality Measurements (Dynamic based on Collection Type) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-emerald-50/50 rounded-xl border border-emerald-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Quantity (Liters) *</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="quantity_liters" 
                            x-model.number="liters" 
                            @input="onQuantityChange()" 
                            required 
                            placeholder="e.g. 15.5" 
                            class="w-full px-3 py-2 text-sm font-extrabold text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div x-show="config.show_fat">
                        <label class="block text-xs font-bold text-slate-800 mb-1">FAT % *</label>
                        <input 
                            type="number" 
                            step="0.1" 
                            name="fat" 
                            x-model.number="fat" 
                            @input="onFatChange()" 
                            :required="config.show_fat"
                            placeholder="4.0" 
                            class="w-full px-3 py-2 text-sm font-bold text-slate-900 border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div x-show="config.show_clr">
                        <label class="block text-xs font-bold mb-1" :class="config.auto_clr ? 'text-blue-600' : 'text-slate-800'" x-text="config.auto_clr ? 'CLR (Auto)' : 'CLR *'"></label>
                        <input 
                            type="number" 
                            step="0.5" 
                            name="clr" 
                            x-model.number="clr" 
                            @input="onClrChange()" 
                            :readonly="config.auto_clr"
                            :required="config.show_clr && !config.auto_clr"
                            placeholder="28.0" 
                            :class="config.auto_clr ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-900'"
                            class="w-full px-3 py-2 text-sm font-bold border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                    </div>

                    <div x-show="config.show_snf">
                        <label class="block text-xs font-bold mb-1" :class="config.auto_snf ? 'text-blue-600' : 'text-slate-800'" x-text="config.auto_snf ? 'SNF % (Auto)' : 'SNF % *'"></label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="snf" 
                            x-model.number="snf" 
                            @input="onSnfChange()" 
                            :readonly="config.auto_snf"
                            :required="config.show_snf && !config.auto_snf"
                            placeholder="8.5" 
                            :class="config.auto_snf ? 'bg-slate-100 text-slate-600 cursor-not-allowed' : 'bg-white text-slate-900'"
                            class="w-full px-3 py-2 text-sm font-bold border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        >
                    </div>
                </div>

                <!-- Rate, Bonus & Deduction Adjustments -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Applied Rate (₹/L)</label>
                        <input type="number" step="0.01" name="applied_rate" x-model.number="rate" @input="calculateTotal()" class="w-full px-3 py-2 text-xs font-bold border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Bonus / Additions (₹)</label>
                        <input type="number" step="0.5" name="bonus" x-model.number="bonus" @input="calculateTotal()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Deduction / Charges (₹)</label>
                        <input type="number" step="0.5" name="deduction" x-model.number="deduction" @input="calculateTotal()" placeholder="0.00" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Remarks / Freshness Notes</label>
                    <input type="text" name="notes" placeholder="e.g. Clean chilled milk, standard temperature" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                    <a href="{{ route('collections.index') }}" class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition transform hover:scale-[1.02]">
                        Save & Print Receipt Slip
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Slip Calculation Card (1 col) -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-700 text-white p-6 rounded-2xl shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-emerald-400/40">
                    <span class="text-xs font-bold uppercase tracking-wider">Live Calculation</span>
                    <span class="text-[11px] bg-white/20 px-2 py-0.5 rounded-full font-mono font-bold">{{ $nextReceipt }}</span>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-emerald-100">Collection Mode:</span>
                        <span class="font-bold" x-text="config.collection_type"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-emerald-100">Milk Type:</span>
                        <span class="font-bold capitalize" x-text="milkType"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-emerald-100">Quantity:</span>
                        <span class="font-bold" x-text="liters + ' Ltr'"></span>
                    </div>
                    <div class="flex justify-between text-xs" x-show="config.show_fat">
                        <span class="text-emerald-100">FAT %:</span>
                        <span class="font-bold" x-text="fat + '%'"></span>
                    </div>
                    <div class="flex justify-between text-xs" x-show="config.show_clr">
                        <span class="text-emerald-100">CLR:</span>
                        <span class="font-bold" x-text="clr"></span>
                    </div>
                    <div class="flex justify-between text-xs" x-show="config.show_snf">
                        <span class="text-emerald-100">SNF %:</span>
                        <span class="font-bold" x-text="snf + '%'"></span>
                    </div>

                    <div class="py-2 border-y border-emerald-400/40 my-3">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs text-emerald-100">Calculated Rate:</span>
                            <span class="text-xl font-extrabold">₹ <span x-text="Number(rate || 0).toFixed(2)"></span> / L</span>
                        </div>
                        <div class="text-[10px] text-emerald-100/90 mt-0.5 text-right" x-text="chartName"></div>
                    </div>

                    <div class="flex justify-between text-xs">
                        <span class="text-emerald-100">Gross Value:</span>
                        <span class="font-semibold" x-text="'₹ ' + gross.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-xs" x-show="bonus > 0">
                        <span class="text-emerald-100">+ Bonus:</span>
                        <span class="font-semibold" x-text="'₹ ' + Number(bonus || 0).toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-xs" x-show="deduction > 0">
                        <span class="text-emerald-100">- Deduction:</span>
                        <span class="font-semibold" x-text="'₹ ' + Number(deduction || 0).toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-emerald-400/40">
                <span class="text-[11px] text-emerald-100 uppercase tracking-wider block">Total Net Farmer Payout</span>
                <span class="text-3xl font-black mt-1 block">₹ <span x-text="net.toFixed(2)"></span></span>
                <span class="text-[10px] text-emerald-200 mt-1 block">SMS/WhatsApp receipt slip will be automatically generated.</span>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function collectionForm() {
        return {
            config: @json($collectionConfig),
            shift: '{{ $currentShift }}',
            collectionDate: '{{ $today }}',
            farmerId: '',
            centerId: '{{ $centers->first()->id ?? "" }}',
            milkType: '{{ $collectionConfig['default_milk_type'] }}',
            liters: 10,
            fat: 4.0,
            snf: 8.5,
            clr: 28,
            bonus: 0,
            deduction: 0,
            rate: 42.50,
            chartName: 'Standard Rate Chart',
            gross: 425.00,
            net: 425.00,

            init() {
                if (this.config.auto_snf) {
                    this.calculateSnfFromClr();
                } else if (this.config.auto_clr) {
                    this.calculateClrFromSnf();
                }
                this.onQuantityChange();
                this.updateRate();
            },

            onFarmerChange() {
                const select = document.querySelector('select[name="farmer_id"]');
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.animal && this.config.allowed_milk_types.includes(selectedOption.dataset.animal)) {
                    this.milkType = selectedOption.dataset.animal;
                }
                this.updateRate();
            },

            onQuantityChange() {
                const qty = parseFloat(this.liters) || 0;
                if (this.config.per_liter_addition > 0) {
                    this.bonus = parseFloat((qty * this.config.per_liter_addition).toFixed(2));
                }
                if (this.config.per_liter_deduction > 0) {
                    this.deduction = parseFloat((qty * this.config.per_liter_deduction).toFixed(2));
                }
                this.calculateTotal();
            },

            onFatChange() {
                if (this.config.auto_snf) {
                    this.calculateSnfFromClr();
                } else if (this.config.auto_clr) {
                    this.calculateClrFromSnf();
                }
                this.updateRate();
            },

            onClrChange() {
                if (this.config.auto_snf) {
                    this.calculateSnfFromClr();
                }
                this.updateRate();
            },

            onSnfChange() {
                if (this.config.auto_clr) {
                    this.calculateClrFromSnf();
                }
                this.updateRate();
            },

            calculateSnfFromClr() {
                const clr = parseFloat(this.clr) || 0;
                const fat = parseFloat(this.fat) || 0;
                if (clr > 0 && fat > 0) {
                    this.snf = parseFloat(((clr / 4) + (0.21 * fat) + 0.36).toFixed(2));
                }
            },

            calculateClrFromSnf() {
                const snf = parseFloat(this.snf) || 0;
                const fat = parseFloat(this.fat) || 0;
                if (snf > 0 && fat > 0) {
                    this.clr = parseFloat(Math.max(0, (snf - (0.21 * fat) - 0.36) * 4).toFixed(1));
                }
            },

            updateRate() {
                fetch('{{ route("collections.calc-rate") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        fat: this.config.show_fat ? this.fat : 0,
                        snf: this.config.show_snf ? this.snf : 0,
                        clr: this.config.show_clr ? this.clr : 0,
                        milk_type: this.milkType,
                        shift: this.shift,
                        farmer_id: this.farmerId,
                        collection_center_id: this.centerId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.rate = parseFloat(data.rate) || 0;
                    this.chartName = data.chart_name || 'Rate Chart';
                    if (this.config.auto_snf && data.snf > 0) this.snf = parseFloat(data.snf);
                    if (this.config.auto_clr && data.clr > 0) this.clr = parseFloat(data.clr);
                    this.calculateTotal();
                })
                .catch(() => {
                    this.calculateTotal();
                });
            },

            calculateTotal() {
                this.gross = (parseFloat(this.liters) || 0) * (parseFloat(this.rate) || 0);
                this.net = this.gross + (parseFloat(this.bonus) || 0) - (parseFloat(this.deduction) || 0);
            }
        }
    }
</script>
@endpush
@endsection
