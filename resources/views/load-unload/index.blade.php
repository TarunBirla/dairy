@extends('layouts.app')

@section('title', $loadType === 'counter_sale' ? 'Counter Sale - Load / Unload' : 'Delivery Sale - Load / Unload')
@section('breadcrumb', 'Load / Unload')
@section('header_title', $loadType === 'counter_sale' ? 'Counter Sale (Load / Unload)' : 'Delivery Sale (Load / Unload)')

@section('header_action')
    <!-- Top Nav Toggle matching Screenshot 1, 2 & 3 -->
    <div class="inline-flex items-center p-1 bg-slate-100 rounded-2xl border border-slate-200">
        <a href="{{ route('load-unload.index', ['type' => 'counter_sale']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $loadType === 'counter_sale' ? 'bg-[#002e79] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            <i class="fa-solid fa-store text-xs"></i>
            <span>Counter Sale</span>
        </a>
        <a href="{{ route('load-unload.index', ['type' => 'delivery_sale']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $loadType === 'delivery_sale' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 hover:text-emerald-900' }}">
            <i class="fa-solid fa-truck-ramp-box text-xs"></i>
            <span>Delivery Sale</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-bold flex items-center justify-between shadow-2xs">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                {{ session('success') }}
            </span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-xs font-bold">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- CARD 1: Add Form (Matching Screenshot 2 & 3) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="bg-gradient-to-r {{ $loadType === 'counter_sale' ? 'from-[#002e79] to-[#001f52]' : 'from-emerald-700 to-emerald-900' }} px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid {{ $loadType === 'counter_sale' ? 'fa-cart-shopping' : 'fa-truck-arrow-right' }} text-amber-400 text-base"></i>
                <h3 class="text-sm font-bold tracking-wide">
                    {{ $loadType === 'counter_sale' ? 'Add Counter Sale' : 'Add Delivery Sale' }}
                </h3>
            </div>
            <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">
                Load Inventory Stock
            </span>
        </div>

        <form action="{{ route('load-unload.store') }}" method="POST" id="loadForm" class="p-6 space-y-6">
            @csrf
            <input type="hidden" name="load_type" value="{{ $loadType }}">

            <!-- Row 1: Date, Delivery Person, Shift -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                <!-- Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Date *</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <!-- Delivery Person -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Delivery Person *</label>
                    <select name="delivery_person" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="">Select Delivery Person</option>
                        @foreach($deliveryPersons as $p)
                            <option value="{{ $p->id }}" {{ old('delivery_person') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} {{ $p->phone ? "({$p->phone})" : '' }}
                            </option>
                        @endforeach
                        <!-- Common Fallback Options if no users yet -->
                        @if($deliveryPersons->isEmpty())
                            <option value="Akashh (1123547899)">Akashh (1123547899)</option>
                            <option value="Rahul Kumar (9876543211)">Rahul Kumar (9876543211)</option>
                        @endif
                    </select>
                </div>

                <!-- Shift -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Shift *</label>
                    <select name="shift" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="Morning" {{ old('shift') == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Evening" {{ old('shift') == 'Evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>
            </div>

            <!-- Row 2: Products and quantities (Dynamic Rows) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Products and quantities *</label>
                    <button type="button" onclick="addProductRow()" class="px-3.5 py-1.5 border border-blue-300 hover:bg-blue-50 text-[#002e79] text-xs font-bold rounded-xl transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                        <i class="fa-solid fa-plus text-xs"></i> <span>+ Add product</span>
                    </button>
                </div>

                <!-- Products Container -->
                <div id="productsContainer" class="space-y-3">
                    <!-- Default Row 0 -->
                    <div class="product-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50/70 p-3 rounded-2xl border border-slate-200/80">
                        <div class="sm:col-span-6">
                            <select name="products[0][name]" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                                <option value="">Select Product</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->name }}">{{ $prod->name }}</option>
                                @endforeach
                                <!-- Common Dairy Products Fallback -->
                                <option value="Bottle Milk">Bottle Milk</option>
                                <option value="Bottle milk 1ltr">Bottle milk 1ltr</option>
                                <option value="Cow Milk">Cow Milk</option>
                                <option value="Buffalo Milk">Buffalo Milk</option>
                                <option value="paneer">paneer</option>
                                <option value="Ghee">Ghee</option>
                                <option value="Curd / Dahi">Curd / Dahi</option>
                                <option value="Chhach / Buttermilk">Chhach / Buttermilk</option>
                            </select>
                        </div>
                        <div class="sm:col-span-4">
                            <input type="number" step="0.01" name="products[0][quantity]" placeholder="Quantity" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold text-[#002e79]">
                        </div>
                        <div class="sm:col-span-2 flex justify-end">
                            <button type="button" onclick="removeProductRow(this)" class="w-full px-3 py-2 border border-rose-300 text-rose-600 hover:bg-rose-50 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-trash-can text-xs"></i> <span>Remove</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Remark & Submit -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end pt-2">
                <div class="sm:col-span-8">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Remark</label>
                    <input type="text" name="remark" value="{{ old('remark') }}" placeholder="Enter remark (e.g. Counter stock or route dispatch)..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>
                <div class="sm:col-span-4 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-8 py-2.5 bg-[#002e79] hover:bg-[#002765] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Save</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- CARD 2: Records Register Table (Matching Screenshot 2 & 3) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-clipboard-list text-amber-400 text-base"></i>
                <h3 class="text-sm font-bold tracking-wide">
                    {{ $loadType === 'counter_sale' ? 'Counter Sale Records' : 'Delivery Sale Records' }}
                </h3>
            </div>
            <span class="text-xs font-mono font-bold text-slate-300">
                Total: {{ $records->total() }} Records
            </span>
        </div>

        <!-- Filter Bar matching Screenshot 2 & 3 -->
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="{{ route('load-unload.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 items-end">
                <input type="hidden" name="type" value="{{ $loadType }}">

                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Delivery person, phone, product" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                </div>

                <!-- From Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                </div>

                <!-- Shift -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Shift</label>
                    <select name="shift" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                        <option value="all" {{ request('shift') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="Morning" {{ request('shift') == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Evening" {{ request('shift') == 'Evening' ? 'selected' : '' }}>Evening</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-[#002e79] hover:bg-[#002765] text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i> Search
                    </button>
                    <a href="{{ route('load-unload.index', ['type' => $loadType]) }}" class="py-2 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1">
                        <i class="fa-solid fa-rotate text-xs"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table matching Screenshot 2 & 3 -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-[#e8f5e9] border-b border-emerald-100 text-[11px] uppercase font-bold text-slate-700">
                    <tr>
                        <th class="py-3 px-4">S.no</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Delivery Person</th>
                        <th class="py-3 px-4">Load For</th>
                        <th class="py-3 px-4">Shift</th>
                        <th class="py-3 px-4">Products / Quantity</th>
                        <th class="py-3 px-4">Remark</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($records as $index => $rec)
                        <tr class="hover:bg-blue-50/30 transition">
                            <!-- 1. S.no -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-500">
                                #{{ $records->firstItem() + $index }}
                            </td>

                            <!-- 2. Date -->
                            <td class="py-3.5 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $rec->date ? $rec->date->format('Y-m-d') : '—' }}
                            </td>

                            <!-- 3. Delivery Person (Name + Phone) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">
                                    {{ $rec->delivery_person_name ?? ($rec->deliveryPerson->name ?? '—') }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-400">
                                    {{ $rec->delivery_person_phone ?? ($rec->deliveryPerson->phone ?? '') }}
                                </div>
                            </td>

                            <!-- 4. Load For -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $rec->load_type === 'counter_sale' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $rec->load_type === 'counter_sale' ? 'Counter Sale' : 'Delivery Sale' }}
                                </span>
                            </td>

                            <!-- 5. Shift -->
                            <td class="py-3.5 px-4 font-medium text-slate-700 capitalize">
                                {{ $rec->shift }}
                            </td>

                            <!-- 6. Products / Quantity List -->
                            <td class="py-3.5 px-4 min-w-[200px]">
                                <div class="space-y-1">
                                    @foreach($rec->items as $item)
                                        <div class="flex items-center justify-between gap-4 text-xs">
                                            <span class="text-slate-700 font-medium">{{ $item->product_name }}</span>
                                            <span class="font-mono font-bold text-slate-900">{{ number_format($item->quantity, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <!-- 7. Remark -->
                            <td class="py-3.5 px-4 text-slate-600 max-w-[150px] truncate">
                                {{ $rec->remark ?? '—' }}
                            </td>

                            <!-- 8. Action (Yellow Eye & Red Delete matching Screenshot 2 & 3) -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- View Button (Yellow / Amber) -->
                                    <button type="button" onclick="openViewModal({{ $rec->id }})" class="p-2 bg-[#ffc107] hover:bg-amber-500 text-slate-900 rounded-lg transition shadow-2xs inline-flex items-center justify-center cursor-pointer" title="View Details">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- Delete Button (Red) -->
                                    <form action="{{ route('load-unload.destroy', $rec->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this load record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-[#dc3545] hover:bg-rose-700 text-white rounded-lg transition shadow-2xs inline-flex items-center justify-center cursor-pointer" title="Delete Record">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-2 block"></i>
                                No {{ $loadType === 'counter_sale' ? 'Counter Sale' : 'Delivery Sale' }} records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Interactive View Modal -->
<div id="viewModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        <div class="bg-gradient-to-r from-[#002e79] to-[#001f52] px-6 py-4 text-white flex items-center justify-between">
            <h4 class="text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-amber-400"></i>
                <span id="modalTitle">Load Details</span>
            </h4>
            <button type="button" onclick="closeViewModal()" class="text-white/80 hover:text-white text-lg font-bold">&times;</button>
        </div>

        <div class="p-6 space-y-4 text-xs">
            <!-- Details Header Grid -->
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Date & Shift</span>
                    <span id="modalDateShift" class="font-bold text-slate-800 text-xs"></span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Load Type</span>
                    <span id="modalType" class="font-bold text-emerald-700 text-xs"></span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Delivery Person</span>
                    <span id="modalPerson" class="font-bold text-slate-800 text-xs"></span>
                </div>
                <div class="col-span-2" id="modalRemarkRow">
                    <span class="text-slate-400 font-semibold block text-[10px] uppercase">Remark</span>
                    <span id="modalRemark" class="text-slate-700 text-xs italic"></span>
                </div>
            </div>

            <!-- Products List Table -->
            <div>
                <h5 class="font-bold text-slate-800 mb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-stacked text-[#002e79]"></i>
                    Loaded Products & Quantities
                </h5>
                <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Product Name</th>
                                <th class="py-2.5 px-3 text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsBody" class="divide-y divide-slate-100 bg-white">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 border-t border-slate-100 px-6 py-3.5 flex justify-end">
            <button type="button" onclick="closeViewModal()" class="px-5 py-2 bg-slate-600 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    let rowIndex = 1;

    // Available products options
    const productOptions = `
        <option value="">Select Product</option>
        @foreach($products as $prod)
            <option value="{{ $prod->name }}">{{ $prod->name }}</option>
        @endforeach
        <option value="Bottle Milk">Bottle Milk</option>
        <option value="Bottle milk 1ltr">Bottle milk 1ltr</option>
        <option value="Cow Milk">Cow Milk</option>
        <option value="Buffalo Milk">Buffalo Milk</option>
        <option value="paneer">paneer</option>
        <option value="Ghee">Ghee</option>
        <option value="Curd / Dahi">Curd / Dahi</option>
        <option value="Chhach / Buttermilk">Chhach / Buttermilk</option>
    `;

    function addProductRow() {
        const container = document.getElementById('productsContainer');
        const row = document.createElement('div');
        row.className = 'product-row grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-slate-50/70 p-3 rounded-2xl border border-slate-200/80 transition';
        row.innerHTML = `
            <div class="sm:col-span-6">
                <select name="products[${rowIndex}][name]" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                    ${productOptions}
                </select>
            </div>
            <div class="sm:col-span-4">
                <input type="number" step="0.01" name="products[${rowIndex}][quantity]" placeholder="Quantity" required class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none font-mono font-bold text-[#002e79]">
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="button" onclick="removeProductRow(this)" class="w-full px-3 py-2 border border-rose-300 text-rose-600 hover:bg-rose-50 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-trash-can text-xs"></i> <span>Remove</span>
                </button>
            </div>
        `;
        container.appendChild(row);
        rowIndex++;
    }

    function removeProductRow(btn) {
        const rows = document.querySelectorAll('.product-row');
        if (rows.length <= 1) {
            alert('At least one product row is required.');
            return;
        }
        btn.closest('.product-row').remove();
    }

    // View Modal Functionality
    function openViewModal(id) {
        fetch(`/load-unload/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalTitle').innerText = (data.load_type === 'counter_sale' ? 'Counter Sale #' : 'Delivery Sale #') + data.id;
                document.getElementById('modalDateShift').innerText = (data.date ? data.date.substring(0, 10) : '—') + ' (' + data.shift + ')';
                document.getElementById('modalType').innerText = data.load_type === 'counter_sale' ? 'Counter Sale' : 'Delivery Sale';
                
                const personName = data.delivery_person_name || (data.delivery_person ? data.delivery_person.name : '—');
                const personPhone = data.delivery_person_phone || (data.delivery_person ? data.delivery_person.phone : '');
                document.getElementById('modalPerson').innerText = personName + (personPhone ? ' - ' + personPhone : '');

                document.getElementById('modalRemark').innerText = data.remark || 'None';

                const body = document.getElementById('modalItemsBody');
                body.innerHTML = '';
                let totalQty = 0;
                if (data.items && data.items.length > 0) {
                    data.items.forEach(it => {
                        const q = parseFloat(it.quantity) || 0;
                        totalQty += q;
                        body.innerHTML += `
                            <tr>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">${it.product_name}</td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-[#002e79]">${q.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    body.innerHTML += `
                        <tr class="bg-slate-50 font-bold border-t border-slate-200">
                            <td class="py-2.5 px-3 text-slate-900">Total Quantity</td>
                            <td class="py-2.5 px-3 text-right font-mono text-emerald-700">${totalQty.toFixed(2)}</td>
                        </tr>
                    `;
                } else {
                    body.innerHTML = '<tr><td colspan="2" class="py-4 text-center text-slate-400">No items found</td></tr>';
                }

                document.getElementById('viewModal').classList.remove('hidden');
            })
            .catch(err => {
                alert('Could not load record details.');
            });
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>
@endsection
