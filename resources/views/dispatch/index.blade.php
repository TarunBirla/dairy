@extends('layouts.app')

@section('title', 'Milk Dispatched')
@section('breadcrumb', 'Dispatch')
@section('header_title', 'Milk Outward & Dispatch Management')

@section('header_action')
    <a href="{{ route('dispatch.create') }}" class="px-4 py-2 bg-[#002e79] hover:bg-[#002765] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>+ Add Dispatch</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-bold flex items-center justify-between shadow-2xs">
            <span class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                {{ session('success') }}
            </span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
        </div>
    @endif

    <!-- Top Filter Bar matching Screenshot 2 -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('dispatch.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Shift</label>
                <select name="shift" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none bg-white">
                    <option value="all" {{ request('shift') == 'all' ? 'selected' : '' }}>All Shifts</option>
                    <option value="morning" {{ request('shift') == 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') == 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Drop Location</label>
                <input type="text" name="drop_location" value="{{ request('drop_location') }}" placeholder="Search Destination..." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-[#002e79] hover:bg-[#002765] text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i> Search
                </button>
                <a href="{{ route('dispatch.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-rotate text-xs"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 4 KPI Summary Cards matching Screenshot 2 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Dispatch Count -->
        <div class="bg-white p-5 rounded-2xl border border-blue-200 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Dispatch</span>
            <p class="text-3xl font-bold text-[#002e79] mt-2">{{ $totalDispatchCount }}</p>
        </div>

        <!-- 2. Total Quantity -->
        <div class="bg-white p-5 rounded-2xl border border-emerald-300 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Total Quantity</span>
            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($totalQuantity, 2) }} <span class="text-base font-semibold text-slate-500">Ltr</span></p>
        </div>

        <!-- 3. Total Amount -->
        <div class="bg-white p-5 rounded-2xl border border-amber-300 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Total Amount</span>
            <p class="text-3xl font-bold text-amber-600 mt-2"><span class="text-base font-semibold text-slate-500">₹</span> {{ number_format($totalAmount, 2) }}</p>
        </div>

        <!-- 4. Total Vehicles -->
        <div class="bg-white p-5 rounded-2xl border border-rose-300 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Total Vehicles</span>
            <p class="text-3xl font-bold text-rose-600 mt-2">{{ $totalVehicles }}</p>
        </div>
    </div>

    <!-- Main Table matching Screenshot 2 with All Columns -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-truck-droplet text-[#002e79] text-base"></i>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Dispatch Records Register</h3>
            </div>
            <a href="{{ route('dispatch.create') }}" class="px-3 py-1.5 bg-[#002e79] hover:bg-[#002765] text-white text-xs font-bold rounded-lg transition flex items-center gap-1">
                <i class="fa-solid fa-plus text-[10px]"></i> Add Dispatch
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 min-w-[1450px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-500">
                    <tr>
                        <th class="py-3 px-3">S.No</th>
                        <th class="py-3 px-3">Challan Date</th>
                        <th class="py-3 px-3">Challan No.</th>
                        <th class="py-3 px-3">From</th>
                        <th class="py-3 px-3">To</th>
                        <th class="py-3 px-3">Drop Location</th>
                        <th class="py-3 px-3">Dispatch Type</th>
                        <th class="py-3 px-3">Milk Type</th>
                        <th class="py-3 px-3">Quality</th>
                        <th class="py-3 px-3">Purchase Qty</th>
                        <th class="py-3 px-3">Quantity</th>
                        <th class="py-3 px-3">Prev. Balance</th>
                        <th class="py-3 px-3">Balance</th>
                        <th class="py-3 px-3">Loss</th>
                        <th class="py-3 px-3">FAT</th>
                        <th class="py-3 px-3">SNF</th>
                        <th class="py-3 px-3">CLR</th>
                        <th class="py-3 px-3">Can No.</th>
                        <th class="py-3 px-3">Temp (°C)</th>
                        <th class="py-3 px-3">Acidity</th>
                        <th class="py-3 px-3 font-bold text-slate-900">Amount</th>
                        <th class="py-3 px-3">Route Name</th>
                        <th class="py-3 px-3">Vehicle No.</th>
                        <th class="py-3 px-3">Vehicle In</th>
                        <th class="py-3 px-3">Vehicle Out</th>
                        <th class="py-3 px-3">New Seal</th>
                        <th class="py-3 px-3">Chamber</th>
                        <th class="py-3 px-3">Headload Kms</th>
                        <th class="py-3 px-3">Difference</th>
                        <th class="py-3 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($dispatches as $index => $d)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="py-3 px-3 text-slate-400 font-mono">{{ $dispatches->firstItem() + $index }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $d->challan_date ? $d->challan_date->format('d-m-Y') : ($d->dispatch_date ? $d->dispatch_date->format('d-m-Y') : '—') }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-cyan-100 text-cyan-800 border border-cyan-200">
                                    {{ $d->challan_number ?? $d->dispatch_number }}
                                </span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-medium text-slate-800">{{ $d->from_date ? $d->from_date->format('d-m-Y') : '—' }}</span>
                                <span class="block text-[10px] text-slate-400 capitalize">{{ $d->from_shift ?? $d->shift }}</span>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-medium text-slate-800">{{ $d->to_date ? $d->to_date->format('d-m-Y') : '—' }}</span>
                                <span class="block text-[10px] text-slate-400 capitalize">{{ $d->to_shift ?? $d->shift }}</span>
                            </td>
                            <td class="py-3 px-3 font-medium text-slate-700 whitespace-nowrap">{{ $d->drop_location ?? '—' }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $d->dispatch_type === 'Tanker' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $d->dispatch_type ?? 'Can' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">{{ $d->milk_type ?? 'Cow' }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    {{ $d->milk_quality ?? 'Good' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono">{{ number_format($d->purchase_qty ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-[#002e79] whitespace-nowrap">
                                {{ number_format($d->quantity_ltr > 0 ? $d->quantity_ltr : $d->total_milk_quantity, 2) }} Ltr
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-500">{{ number_format($d->prev_balance ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">{{ number_format($d->balance ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono text-rose-600">{{ number_format($d->loss ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono font-bold">{{ $d->fat ? number_format($d->fat, 2) : '—' }}</td>
                            <td class="py-3 px-3 font-mono font-bold">{{ $d->snf ? number_format($d->snf, 2) : '—' }}</td>
                            <td class="py-3 px-3 font-mono">{{ $d->clr ? number_format($d->clr, 2) : '—' }}</td>
                            <td class="py-3 px-3 font-mono text-slate-600">{{ $d->can_number ?? '—' }}</td>
                            <td class="py-3 px-3 font-mono">{{ $d->temperature ? number_format($d->temperature, 1) : '—' }}</td>
                            <td class="py-3 px-3 font-mono">{{ $d->acidity ? number_format($d->acidity, 2) : '—' }}</td>
                            <td class="py-3 px-3 whitespace-nowrap font-bold text-emerald-700">
                                ₹ {{ number_format($d->amount ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-3 text-slate-700 whitespace-nowrap">{{ $d->route_name ?? ($d->route->name ?? '—') }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">{{ $d->vehicle_number ?? '—' }}</td>
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $d->vehicle_in_time ?? '—' }}</td>
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">{{ $d->vehicle_out_time ?? '—' }}</td>
                            <td class="py-3 px-3 font-mono text-slate-600">{{ $d->seal_number ?? '—' }}</td>
                            <td class="py-3 px-3 font-mono text-slate-600">{{ $d->chamber_number ?? '—' }}</td>
                            <td class="py-3 px-3 font-mono">{{ number_format($d->headload_kms ?? 0, 2) }}</td>
                            <td class="py-3 px-3 font-mono">{{ number_format($d->difference ?? 0, 2) }}</td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('dispatch.edit', $d->id) }}" class="p-1.5 bg-blue-50 text-[#002e79] hover:bg-[#002e79] hover:text-white rounded-lg transition" title="Edit Dispatch">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('dispatch.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this dispatch record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg transition" title="Delete Dispatch">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="30" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-truck-ramp-box text-3xl text-slate-300 mb-2 block"></i>
                                No milk dispatches found matching your search. Click <b>"+ Add Dispatch"</b> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispatches->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $dispatches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
