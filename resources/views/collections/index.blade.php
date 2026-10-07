@extends('layouts.app')

@section('title', 'Milk Collection History')
@section('breadcrumb', 'Collection History')
@section('header_title', 'Procurement / Milk Collections')

@section('header_action')
    <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>New Collection</span>
    </button>
@endsection

@section('content')
<div class="space-y-6" x-data="milkCollectionManager()" @open-collection-modal.window="openCreateModal()">

    <!-- Metric Summary Cards matching current theme -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Procured</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($todayTotalLiters, 1) }} <span class="text-xs font-medium text-slate-400">Liters</span></p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Total Net Payout</span>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">₹ {{ number_format($todayTotalAmount, 2) }}</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Average FAT</span>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($avgFat, 1) }}%</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400">Average SNF</span>
            <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($avgSnf, 1) }}%</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('collections.index') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
            <!-- Search Farmer -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Search Farmer</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Code, name, phone..." class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <!-- Date -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Collection Date</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <!-- Shift -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Shift</label>
                <select name="shift" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Shifts</option>
                    <option value="morning" {{ request('shift') === 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') === 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>

            <!-- Milk Type -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Milk Type</label>
                <select name="milk_type" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Milk Types</option>
                    <option value="cow" {{ request('milk_type') === 'cow' ? 'selected' : '' }}>Cow</option>
                    <option value="buffalo" {{ request('milk_type') === 'buffalo' ? 'selected' : '' }}>Buffalo</option>
                    <option value="mixed" {{ request('milk_type') === 'mixed' ? 'selected' : '' }}>Mixed</option>
                </select>
            </div>

            <!-- Center -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Center</label>
                <select name="center_id" class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All Centers</option>
                    @foreach($centers as $c)
                        <option value="{{ $c->id }}" {{ request('center_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs">Filter</button>
                <a href="{{ route('collections.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    <!-- Collection Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Collections History</h3>
                <p class="text-xs text-slate-400">Recorded milk intake with real-time FAT/SNF quality parameters</p>
            </div>
            <button type="button" @click="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Collection</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400">
                    <tr>
                        <th class="py-3 px-4">Receipt</th>
                        <th class="py-3 px-4">Date & Shift</th>
                        <th class="py-3 px-4">Farmer / Code</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-center">Qty (L)</th>
                        <th class="py-3 px-4 text-center">Fat / SNF / CLR</th>
                        <th class="py-3 px-4 text-right">Rate (₹)</th>
                        <th class="py-3 px-4 text-right">Net Amount</th>
                        <th class="py-3 px-4 text-center w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($collections as $col)
                        <tr class="hover:bg-slate-50/60 transition group">
                            <!-- Receipt -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ $col->receipt_number }}
                            </td>

                            <!-- Date & Shift -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-900 block">{{ $col->collection_date ? $col->collection_date->format('d M Y') : '—' }}</span>
                                <span class="text-[10px] capitalize px-1.5 py-0.5 rounded font-bold {{ $col->shift === 'morning' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' }}">
                                    {{ $col->shift }}
                                </span>
                            </td>

                            <!-- Farmer -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('farmers.show', $col->farmer) }}" class="font-bold text-emerald-700 hover:underline">
                                    {{ $col->farmer->name }}
                                </a>
                                <span class="block text-[11px] text-slate-400">
                                    {{ $col->farmer->farmer_code }} • {{ $col->farmer->village ?? 'Local' }}
                                </span>
                            </td>

                            <!-- Type -->
                            <td class="py-3.5 px-4 capitalize font-medium">
                                <span class="inline-flex items-center gap-1 font-semibold {{ $col->milk_type === 'cow' ? 'text-amber-700' : 'text-slate-800' }}">
                                    {{ $col->milk_type === 'cow' ? '🐄 Cow' : ($col->milk_type === 'buffalo' ? '🐃 Buffalo' : '🥛 Mixed') }}
                                </span>
                            </td>

                            <!-- Quantity -->
                            <td class="py-3.5 px-4 text-center font-extrabold text-slate-900">
                                {{ number_format($col->quantity_liters, 2) }} L
                            </td>

                            <!-- FAT / SNF / CLR -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-semibold text-slate-700">{{ number_format($col->fat, 1) }}%</span> / 
                                <span class="font-semibold text-slate-700">{{ number_format($col->snf, 1) }}%</span>
                                @if($col->clr)
                                    <span class="text-[10px] text-slate-400 block font-mono">CLR: {{ $col->clr }}</span>
                                @endif
                            </td>

                            <!-- Rate -->
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-800">
                                ₹ {{ number_format($col->applied_rate, 2) }}
                            </td>

                            <!-- Net Amount -->
                            <td class="py-3.5 px-4 text-right font-extrabold text-sm text-emerald-700">
                                ₹ {{ number_format($col->net_amount, 2) }}
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Print Slip Button -->
                                    <a href="{{ route('collections.slip', $col) }}" target="_blank" title="Print Slip" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition shadow-2xs">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    </a>

                                    <!-- Edit Collection Button (Opens Modal) -->
                                    <button type="button" 
                                            @click="openEditModal({
                                                id: {{ $col->id }},
                                                receipt_number: '{{ $col->receipt_number }}',
                                                farmer_id: {{ $col->farmer_id }},
                                                farmer_code: '{{ addslashes($col->farmer->farmer_code ?? '') }}',
                                                farmer_name: '{{ addslashes($col->farmer->name ?? '') }}',
                                                farmer_phone: '{{ addslashes($col->farmer->phone ?? '') }}',
                                                farmer_village: '{{ addslashes($col->farmer->village ?? '') }}',
                                                collection_center_id: {{ $col->collection_center_id ?? 'null' }},
                                                collection_date: '{{ $col->collection_date ? $col->collection_date->format('Y-m-d') : date('Y-m-d') }}',
                                                shift: '{{ $col->shift }}',
                                                milk_type: '{{ $col->milk_type }}',
                                                quantity_liters: {{ (float) $col->quantity_liters }},
                                                fat: {{ (float) $col->fat }},
                                                snf: {{ (float) $col->snf }},
                                                clr: {{ (float) ($col->clr ?? 28) }},
                                                bonus: {{ (float) ($col->bonus ?? 0) }},
                                                deduction: {{ (float) ($col->deduction ?? 0) }},
                                                applied_rate: {{ (float) $col->applied_rate }},
                                                notes: '{{ addslashes($col->notes ?? '') }}'
                                            })"
                                            title="Edit Collection"
                                            class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition shadow-2xs">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('collections.destroy', $col) }}" method="POST" onsubmit="return confirm('Delete collection receipt {{ $col->receipt_number }}? This will reverse the farmer ledger.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition shadow-2xs">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                                        <i data-lucide="milk" class="w-6 h-6"></i>
                                    </div>
                                    <span class="text-sm font-semibold text-slate-600">No collection entries found</span>
                                    <p class="text-xs text-slate-400 mt-0.5">Click "+ New Collection" to record milk from farmers.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $collections->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- POPUP MODAL: ADD / EDIT COLLECTION (INSPIRED BY MOBILE DAIRY)  -->
    <!-- ============================================================== -->
    <div x-show="showModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
         @keydown.escape.window="closeModal()">

        <div class="bg-white rounded-2xl max-w-4xl w-full p-5 sm:p-6 shadow-2xl border border-slate-100 relative my-6"
             @click.away="closeModal()">

            <!-- Modal Header with Quick Mode & Close -->
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="milk" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900" x-text="isEditMode ? 'Edit Milk Collection' : 'New Milk Collection Entry'"></h3>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold" x-text="form.receipt_number || '{{ $nextReceipt }}'"></span>
                        </div>
                        <p class="text-xs text-slate-400">High-speed milk procurement entry with live FAT, CLR, SNF & rate lookup</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form -->
            <form :action="formAction" method="POST" class="mt-4 space-y-4">
                @csrf
                <template x-if="isEditMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Top Selector Controls (Date, Shift, Milk Type, Center) matching Mobile Dairy reference -->
                <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
                    
                    <!-- Date Picker -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Date:</label>
                        <input type="date" name="collection_date" x-model="form.collection_date" required
                               class="text-xs font-semibold px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Shift Toggle Buttons -->
                    <div class="flex items-center gap-1.5">
                        <input type="hidden" name="shift" :value="form.shift">
                        <button type="button" 
                                @click="form.shift = 'morning'; triggerRateCalculation();"
                                :class="form.shift === 'morning' ? 'bg-amber-400 text-slate-900 font-bold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>☀️ Morning</span>
                        </button>
                        <button type="button" 
                                @click="form.shift = 'evening'; triggerRateCalculation();"
                                :class="form.shift === 'evening' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>🌙 Evening</span>
                        </button>
                    </div>

                    <!-- Milk Type Buttons (Cow vs Buffalo vs Mixed) -->
                    <div class="flex items-center gap-1.5">
                        <input type="hidden" name="milk_type" :value="form.milk_type">
                        <button type="button" 
                                @click="form.milk_type = 'cow'; triggerRateCalculation();"
                                :class="form.milk_type === 'cow' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>🐄 Cow</span>
                        </button>
                        <button type="button" 
                                @click="form.milk_type = 'buffalo'; triggerRateCalculation();"
                                :class="form.milk_type === 'buffalo' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100'"
                                class="px-3 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>🐃 Buffalo</span>
                        </button>
                    </div>

                    <!-- Collection Center -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-600">Center:</label>
                        <select name="collection_center_id" x-model="form.collection_center_id"
                                class="text-xs px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            @foreach($centers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- High-Speed Data Entry Grid (Inspired by Mobile Dairy reference image) -->
                <div class="p-4 bg-emerald-50/40 rounded-2xl border border-emerald-100/90 space-y-4">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 items-start">
                        
                        <!-- Customer / Farmer Code & Selection (4 cols) -->
                        <div class="sm:col-span-4 space-y-1">
                            <label class="block text-xs font-bold text-slate-700">
                                Customer / Farmer Code *
                            </label>

                            <!-- Direct Fast Code Input & Dropdown -->
                            <div class="space-y-1.5">
                                <div class="relative">
                                    <select name="farmer_id" 
                                            id="collection_farmer_select" 
                                            x-model="form.farmer_id" 
                                            @change="onFarmerSelect($event)" 
                                            required
                                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-semibold">
                                        <option value="">-- Select Farmer or Code --</option>
                                        @foreach($farmers as $f)
                                            <option value="{{ $f->id }}" 
                                                    data-code="{{ $f->farmer_code }}" 
                                                    data-name="{{ $f->name }}" 
                                                    data-phone="{{ $f->phone }}" 
                                                    data-village="{{ $f->village }}" 
                                                    data-animal="{{ $f->animal_type }}">
                                                {{ $f->farmer_code }} - {{ $f->name }} ({{ $f->phone ?? 'No phone' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Underneath Farmer Info preview like screenshot -->
                                <div class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs flex items-center justify-between" x-show="selectedFarmer.name">
                                    <div>
                                        <span class="font-bold text-slate-900 block" x-text="selectedFarmer.code + ' ' + selectedFarmer.name"></span>
                                        <span class="text-[10px] text-slate-400 block" x-text="(selectedFarmer.phone ? selectedFarmer.phone + ' • ' : '') + (selectedFarmer.village || 'Local')"></span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded"
                                          :class="selectedFarmer.animal === 'buffalo' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'"
                                          x-text="selectedFarmer.animal"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Liter / Quantity (2 cols) -->
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-xs font-bold text-slate-800">
                                Liter (Qty) *
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   name="quantity_liters" 
                                   x-model.number="form.quantity_liters" 
                                   @input="recalculateTotals()" 
                                   required 
                                   placeholder="e.g. 3.0"
                                   class="w-full px-3 py-2 text-sm font-black text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <span class="text-[10px] text-slate-400 block">Milk in Liters</span>
                        </div>

                        <!-- FAT % (2 cols) -->
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-xs font-bold text-slate-800">
                                FAT % *
                            </label>
                            <input type="number" 
                                   step="0.1" 
                                   name="fat" 
                                   x-model.number="form.fat" 
                                   @input="onFatOrClrChange()" 
                                   required 
                                   placeholder="4.2"
                                   class="w-full px-3 py-2 text-sm font-black text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <span class="text-[10px] text-slate-400 block">FAT reading</span>
                        </div>

                        <!-- CLR (2 cols) -->
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-xs font-bold text-slate-800">
                                CLR *
                            </label>
                            <input type="number" 
                                   step="0.5" 
                                   name="clr" 
                                   x-model.number="form.clr" 
                                   @input="onFatOrClrChange()" 
                                   required 
                                   placeholder="28"
                                   class="w-full px-3 py-2 text-sm font-black text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <span class="text-[10px] text-slate-400 block">Lactometer</span>
                        </div>

                        <!-- SNF % (2 cols - Auto Calculated from CLR & FAT) -->
                        <div class="sm:col-span-2 space-y-1">
                            <label class="block text-xs font-bold text-slate-800">
                                SNF % *
                            </label>
                            <input type="number" 
                                   step="0.01" 
                                   name="snf" 
                                   x-model.number="form.snf" 
                                   @input="triggerRateCalculation()" 
                                   required 
                                   placeholder="8.5"
                                   class="w-full px-3 py-2 text-sm font-black text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            <span class="text-[10px] text-emerald-700 font-semibold block">Auto from CLR</span>
                        </div>
                    </div>

                    <!-- Rate & Formula Feedback Row (Matching Mobile Dairy Reference: "Cow Rate Chart (54.50) = 54.50") -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3.5 pt-2 border-t border-emerald-200/60 items-center">
                        
                        <!-- Rate Input & Chart Label (6 cols) -->
                        <div class="sm:col-span-6 space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-800">
                                    Rate (₹ / Liter) *
                                </label>
                                <span class="text-[11px] font-mono font-bold text-emerald-700" x-text="rateChartFeedback"></span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs">₹</span>
                                <input type="number" 
                                       step="0.01" 
                                       name="applied_rate" 
                                       x-model.number="form.applied_rate" 
                                       @input="recalculateTotals()" 
                                       required
                                       class="w-full pl-7 pr-3 py-2 text-sm font-extrabold text-slate-900 border border-slate-300 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>

                        <!-- Total Gross Payout Display (6 cols) -->
                        <div class="sm:col-span-6 bg-white p-3 rounded-xl border border-emerald-200/90 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Net Farmer Amount</span>
                                <span class="text-xs text-slate-500" x-text="(form.quantity_liters || 0) + ' L × ₹' + (form.applied_rate || 0) + '/L'"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black text-emerald-600 block">
                                    ₹ <span x-text="netAmountFormatted"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collapsible / Optional Adjustments & Notes -->
                <div x-data="{ showAdvanced: false }" class="border border-slate-200 rounded-xl p-3 bg-slate-50/50">
                    <button type="button" @click="showAdvanced = !showAdvanced" class="flex items-center justify-between w-full text-xs font-bold text-slate-700">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Additional Adjustments & Notes (Bonus, Deductions)</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="showAdvanced ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="showAdvanced" class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Bonus (₹)</label>
                            <input type="number" step="0.5" name="bonus" x-model.number="form.bonus" @input="recalculateTotals()" placeholder="0.00"
                                   class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Deduction (₹)</label>
                            <input type="number" step="0.5" name="deduction" x-model.number="form.deduction" @input="recalculateTotals()" placeholder="0.00"
                                   class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Remarks / Quality Notes</label>
                            <input type="text" name="notes" x-model="form.notes" placeholder="e.g. Standard morning chilled sample"
                                   class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg bg-white">
                        </div>
                    </div>
                </div>

                <!-- Footer Actions matching Mobile Dairy Reference (Reset, Cancel, Save) -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100">
                    <div>
                        <button type="button" @click="resetForm()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center gap-1.5">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Reset</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Cancel
                        </button>
                        
                        <!-- Save and stay on history -->
                        <button type="submit" name="print_slip" value="0" class="px-5 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Save Collection</span>
                        </button>

                        <!-- Save & Print Receipt Slip -->
                        <button type="submit" name="print_slip" value="1" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Save & Print Slip</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function milkCollectionManager() {
    return {
        showModal: false,
        isEditMode: false,
        formAction: '{{ route("collections.store") }}',
        rateChartFeedback: 'Cow Rate Chart = ₹42.50/L',
        
        selectedFarmer: {
            id: '',
            code: '',
            name: '',
            phone: '',
            village: '',
            animal: 'cow'
        },

        form: {
            id: null,
            receipt_number: '{{ $nextReceipt }}',
            farmer_id: '',
            collection_center_id: '{{ $centers->first()->id ?? "" }}',
            collection_date: '{{ $today }}',
            shift: '{{ $currentShift }}',
            milk_type: 'cow',
            quantity_liters: 10.0,
            fat: 4.0,
            clr: 28.0,
            snf: 8.5,
            bonus: 0,
            deduction: 0,
            applied_rate: 42.50,
            notes: ''
        },

        init() {
            // Check if opened with query parameter ?open_create=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('open_create')) {
                this.openCreateModal();
            }
        },

        get netAmountFormatted() {
            const qty = parseFloat(this.form.quantity_liters) || 0;
            const rate = parseFloat(this.form.applied_rate) || 0;
            const bonus = parseFloat(this.form.bonus) || 0;
            const deduction = parseFloat(this.form.deduction) || 0;
            const total = (qty * rate) + bonus - deduction;
            return total.toFixed(2);
        },

        openCreateModal() {
            this.isEditMode = false;
            this.formAction = '{{ route("collections.store") }}';
            this.resetForm();
            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
                this.triggerRateCalculation();
            });
        },

        openEditModal(item) {
            this.isEditMode = true;
            this.formAction = `/collections/${item.id}`;
            this.form = {
                id: item.id,
                receipt_number: item.receipt_number,
                farmer_id: item.farmer_id,
                collection_center_id: item.collection_center_id || '{{ $centers->first()->id ?? "" }}',
                collection_date: item.collection_date,
                shift: item.shift,
                milk_type: item.milk_type,
                quantity_liters: item.quantity_liters,
                fat: item.fat,
                clr: item.clr,
                snf: item.snf,
                bonus: item.bonus || 0,
                deduction: item.deduction || 0,
                applied_rate: item.applied_rate,
                notes: item.notes || ''
            };

            this.selectedFarmer = {
                id: item.farmer_id,
                code: item.farmer_code,
                name: item.farmer_name,
                phone: item.farmer_phone,
                village: item.farmer_village,
                animal: item.milk_type
            };

            this.showModal = true;
            this.$nextTick(() => {
                if (window.lucide) window.lucide.createIcons();
                this.triggerRateCalculation();
            });
        },

        closeModal() {
            this.showModal = false;
        },

        resetForm() {
            this.form = {
                id: null,
                receipt_number: '{{ $nextReceipt }}',
                farmer_id: '',
                collection_center_id: '{{ $centers->first()->id ?? "" }}',
                collection_date: '{{ $today }}',
                shift: '{{ $currentShift }}',
                milk_type: 'cow',
                quantity_liters: 10.0,
                fat: 4.0,
                clr: 28.0,
                snf: 8.5,
                bonus: 0,
                deduction: 0,
                applied_rate: 42.50,
                notes: ''
            };
            this.selectedFarmer = { id: '', code: '', name: '', phone: '', village: '', animal: 'cow' };
            this.calculateSnfFromClr();
            this.triggerRateCalculation();
        },

        onFarmerSelect(event) {
            const select = event.target;
            const opt = select.selectedOptions[0];
            if (opt && opt.value) {
                this.selectedFarmer = {
                    id: opt.value,
                    code: opt.dataset.code || '',
                    name: opt.dataset.name || '',
                    phone: opt.dataset.phone || '',
                    village: opt.dataset.village || '',
                    animal: opt.dataset.animal || 'cow'
                };
                if (opt.dataset.animal && (opt.dataset.animal === 'cow' || opt.dataset.animal === 'buffalo')) {
                    this.form.milk_type = opt.dataset.animal;
                }
            } else {
                this.selectedFarmer = { id: '', code: '', name: '', phone: '', village: '', animal: 'cow' };
            }
            this.triggerRateCalculation();
        },

        onFatOrClrChange() {
            this.calculateSnfFromClr();
            this.triggerRateCalculation();
        },

        calculateSnfFromClr() {
            const clr = parseFloat(this.form.clr) || 0;
            const fat = parseFloat(this.form.fat) || 0;
            if (clr > 0 && fat > 0) {
                // Indian Dairy Formula: SNF = (CLR / 4) + (0.21 * FAT) + 0.36
                const calculatedSnf = (clr / 4) + (0.21 * fat) + 0.36;
                this.form.snf = parseFloat(calculatedSnf.toFixed(2));
            }
        },

        recalculateTotals() {
            // Evaluated reactively in netAmountFormatted
        },

        triggerRateCalculation() {
            const fat = parseFloat(this.form.fat) || 4.0;
            const snf = parseFloat(this.form.snf) || 8.5;
            const milkType = this.form.milk_type || 'cow';
            const farmerId = this.form.farmer_id || null;

            fetch('{{ route("collections.calc-rate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fat: fat,
                    snf: snf,
                    milk_type: milkType,
                    farmer_id: farmerId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.rate) {
                    this.form.applied_rate = parseFloat(data.rate);
                    this.rateChartFeedback = (data.chart_name || 'Rate Chart') + ' = ₹' + Number(data.rate).toFixed(2) + '/L';
                }
            })
            .catch(() => {
                // Fallback rate formula
                let fallbackRate = 40.0;
                if (milkType === 'buffalo') {
                    fallbackRate = (fat * 7.2) + (snf * 4.5);
                } else {
                    fallbackRate = (fat * 6.8) + (snf * 4.1);
                }
                this.form.applied_rate = parseFloat(fallbackRate.toFixed(2));
                this.rateChartFeedback = ucfirst(milkType) + ' Formula = ₹' + this.form.applied_rate + '/L';
            });
        }
    };
}
</script>
@endsection
