@extends('layouts.app')

@section('title', 'Dispatch List')
@section('breadcrumb', 'Dispatch')
@section('header_title', 'Dispatch List')

@section('header_action')
    <a href="{{ route('dispatch.create') }}" class="px-4 py-2 bg-[#002e79] hover:bg-[#002765] text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
        <i class="fa-solid fa-plus text-xs"></i>
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

    <!-- Top Filter Bar matching Screenshot 4 -->
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
                    <option value="all" {{ request('shift') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="morning" {{ request('shift') == 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="evening" {{ request('shift') == 'evening' ? 'selected' : '' }}>Evening</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Drop Location</label>
                <input type="text" name="drop_location" value="{{ request('drop_location') }}" placeholder="Search Destination" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="py-2 px-4 bg-[#0d6efd] hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i> Search
                </button>
                <a href="{{ route('dispatch.index') }}" class="py-2 px-4 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-rotate text-xs"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 4 KPI Summary Cards matching Screenshot 4 -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Dispatch (Blue outline) -->
        <div class="bg-white p-5 rounded-2xl border-2 border-blue-500 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Total Dispatch</span>
            <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalDispatchCount }}</p>
        </div>

        <!-- 2. Total Quantity (Green outline) -->
        <div class="bg-white p-5 rounded-2xl border-2 border-emerald-500 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Total Quantity</span>
            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($totalQuantity, 2) }} <span class="text-base font-semibold text-slate-500">Ltr</span></p>
        </div>

        <!-- 3. Total Amount (Amber/Yellow outline) -->
        <div class="bg-white p-5 rounded-2xl border-2 border-amber-400 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Total Amount</span>
            <p class="text-3xl font-bold text-amber-600 mt-2">Rs. {{ number_format($totalAmount, 2) }}</p>
        </div>

        <!-- 4. Total Vehicles (Red outline) -->
        <div class="bg-white p-5 rounded-2xl border-2 border-rose-500 shadow-2xs flex flex-col justify-between">
            <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Total Vehicles</span>
            <p class="text-3xl font-bold text-rose-600 mt-2">{{ $totalVehicles }}</p>
        </div>
    </div>

    <!-- Main Table matching Screenshot 4 with All 31 Columns -->
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
            <table class="w-full text-left text-xs text-slate-600 min-w-[1600px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-[10px] uppercase font-bold text-slate-600">
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
                        <th class="py-3 px-3">Temp</th>
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
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($dispatches as $index => $d)
                        <tr class="hover:bg-blue-50/30 transition">
                            <!-- 1. S.No -->
                            <td class="py-3 px-3 text-slate-500 font-mono">{{ $dispatches->firstItem() + $index }}</td>
                            
                            <!-- 2. Challan Date -->
                            <td class="py-3 px-3 font-medium text-slate-800 whitespace-nowrap">
                                {{ $d->challan_date ? $d->challan_date->format('d-m-Y') : ($d->dispatch_date ? $d->dispatch_date->format('d-m-Y') : '—') }}
                            </td>

                            <!-- 3. Challan No (Cyan Badge matching Screenshot 4) -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md font-mono font-bold text-xs bg-[#00c5dc] text-white shadow-2xs">
                                    {{ $d->challan_number ?? $d->dispatch_number }}
                                </span>
                            </td>

                            <!-- 4. From (Date + Shift) -->
                            <td class="py-3 px-3 whitespace-nowrap leading-tight">
                                <span class="font-medium text-slate-800 text-[11px]">{{ $d->from_date ? $d->from_date->format('d-m-Y') : '—' }}</span>
                                <span class="block text-[11px] text-slate-500 capitalize">{{ $d->from_shift ?? $d->shift }}</span>
                            </td>

                            <!-- 5. To (Date + Shift) -->
                            <td class="py-3 px-3 whitespace-nowrap leading-tight">
                                <span class="font-medium text-slate-800 text-[11px]">{{ $d->to_date ? $d->to_date->format('d-m-Y') : '—' }}</span>
                                <span class="block text-[11px] text-slate-500 capitalize">{{ $d->to_shift ?? $d->shift }}</span>
                            </td>

                            <!-- 6. Drop Location -->
                            <td class="py-3 px-3 font-medium text-slate-700 whitespace-nowrap">{{ $d->drop_location ?? '—' }}</td>

                            <!-- 7. Dispatch Type -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $d->dispatch_type ?? 'Can' }}
                                </span>
                            </td>

                            <!-- 8. Milk Type -->
                            <td class="py-3 px-3 font-medium text-slate-800 whitespace-nowrap">{{ $d->milk_type ?? 'Cow' }}</td>

                            <!-- 9. Quality -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ $d->milk_quality ?? 'Good' }}
                                </span>
                            </td>

                            <!-- 10. Purchase Qty -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ number_format($d->purchase_qty ?? 0, 2) }}</td>

                            <!-- 11. Quantity (Ltr) -->
                            <td class="py-3 px-3 font-mono font-bold text-[#002e79] whitespace-nowrap">
                                {{ number_format($d->quantity_ltr > 0 ? $d->quantity_ltr : $d->total_milk_quantity, 2) }} Ltr
                            </td>

                            <!-- 12. Prev. Balance -->
                            <td class="py-3 px-3 font-mono text-slate-600">{{ number_format($d->prev_balance ?? 0, 2) }}</td>

                            <!-- 13. Balance -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">{{ number_format($d->balance ?? 0, 2) }}</td>

                            <!-- 14. Loss -->
                            <td class="py-3 px-3 font-mono text-rose-600">{{ number_format($d->loss ?? 0, 2) }}</td>

                            <!-- 15. FAT -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-700">{{ $d->fat ? number_format($d->fat, 2) : '—' }}</td>

                            <!-- 16. SNF -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-700">{{ $d->snf ? number_format($d->snf, 2) : '—' }}</td>

                            <!-- 17. CLR -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $d->clr ? number_format($d->clr, 2) : '—' }}</td>

                            <!-- 18. Can No -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $d->can_number ?? '—' }}</td>

                            <!-- 19. Temp -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $d->temperature ? number_format($d->temperature, 2) : '—' }}</td>

                            <!-- 20. Acidity -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $d->acidity ? number_format($d->acidity, 2) : '—' }}</td>

                            <!-- 21. Amount (Green pill badge matching Screenshot 4) -->
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md font-bold text-xs bg-[#198754] text-white whitespace-nowrap shadow-2xs">
                                    Rs. {{ number_format($d->amount ?? 0, 2) }}
                                </span>
                            </td>

                            <!-- 22. Route Name -->
                            <td class="py-3 px-3 text-slate-700 whitespace-nowrap">{{ $d->route_name ?? ($d->route->name ?? '—') }}</td>

                            <!-- 23. Vehicle No -->
                            <td class="py-3 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">{{ $d->vehicle_number ?? '—' }}</td>

                            <!-- 24. Vehicle In -->
                            <td class="py-3 px-3 text-slate-600 whitespace-nowrap">{{ $d->vehicle_in_time ?? '—' }}</td>

                            <!-- 25. Vehicle Out -->
                            <td class="py-3 px-3 text-slate-600 whitespace-nowrap">{{ $d->vehicle_out_time ?? '—' }}</td>

                            <!-- 26. New Seal -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $d->seal_number ?? '—' }}</td>

                            <!-- 27. Chamber -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $d->chamber_number ?? '—' }}</td>

                            <!-- 28. Headload Kms -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ number_format($d->headload_kms ?? 0, 2) }}</td>

                            <!-- 29. Difference -->
                            <td class="py-3 px-3 font-mono text-slate-700">{{ number_format($d->difference ?? 0, 2) }}</td>

                            <!-- 30. Status (Green Completed badge matching Screenshot 4) -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#198754] text-white tracking-wide">
                                    {{ in_array($d->status, ['completed', 'delivered']) ? 'Completed' : ucfirst($d->status ?? 'Completed') }}
                                </span>
                            </td>

                            <!-- 31. Action (Blue Edit & Red Delete matching Screenshot 4) -->
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('dispatch.edit', $d->id) }}" class="p-1.5 bg-[#0d6efd] hover:bg-blue-700 text-white rounded transition shadow-2xs inline-flex items-center justify-center" title="Edit Dispatch">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('dispatch.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this dispatch record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-[#dc3545] hover:bg-rose-700 text-white rounded transition shadow-2xs inline-flex items-center justify-center cursor-pointer" title="Delete Dispatch">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="31" class="py-12 text-center text-slate-400">
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
