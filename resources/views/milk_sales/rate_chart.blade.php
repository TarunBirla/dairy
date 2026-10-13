@extends('layouts.app')

@section('title', 'Milk Sale Ratechart')
@section('breadcrumb', 'Rate Chart')
@section('header_title', 'Milk Sales / Rate Chart')

@section('header_action')
    <div class="flex items-center gap-2">
        <a href="{{ route('rates.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl shadow-xs transition">
            <i data-lucide="sliders" class="w-4 h-4 text-emerald-600"></i>
            <span>Rate Chart Management</span>
        </a>
        <a href="{{ route('rates.create', ['category' => 'milk_sale', 'milk_type' => $milkType]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>+ Create Rate Chart</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <span class="text-xs font-semibold">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Card: Milk Selector & Rate Chart Assignment Bar matching Screenshot 2 -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            
            <!-- Left: Milk Type Toggle (Cow / Buffalo / Mix) matching Image 2 -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-700 mr-1">Milk:</span>
                
                <a href="{{ route('milk-sales.rate-chart', ['milk_type' => 'cow']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ $milkType === 'cow' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-white' }}">
                    <span>🐄 Cow</span>
                </a>

                <a href="{{ route('milk-sales.rate-chart', ['milk_type' => 'buffalo']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ $milkType === 'buffalo' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-white' }}">
                    <span>🐃 Buffalo</span>
                </a>

                <a href="{{ route('milk-sales.rate-chart', ['milk_type' => 'mixed']) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition border {{ $milkType === 'mixed' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-white' }}">
                    <span>🥛 Mixed</span>
                </a>
            </div>

            <!-- Right: Active Rate Chart Status / Selector & Setup Button matching Image 2 -->
            <div class="flex items-center gap-2.5">
                @if($chart)
                    <!-- Form to switch active chart -->
                    <form method="POST" action="{{ route('milk-sales.rate-chart.set-default') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="milk_type" value="{{ $milkType }}">
                        
                        <div class="min-w-[200px]">
                            <select name="rate_chart_id" onchange="this.form.submit()" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-xl bg-white font-bold text-slate-800 focus:border-emerald-500 outline-none">
                                @foreach($allCharts as $c)
                                    <option value="{{ $c->id }}" {{ $chart->id == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ strtoupper($c->format ?? 'fat_clr') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <a href="{{ route('rates.edit', $chart->id) }}" class="px-3.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl text-xs font-bold transition flex items-center gap-1">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Setup / Edit</span>
                    </a>
                @else
                    <!-- Rate Chart Not Found badge + Setup button matching Screenshot 2 -->
                    <div class="px-3.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span>Rate Chart Not Found</span>
                    </div>

                    <a href="{{ route('rates.create', ['category' => 'milk_sale', 'milk_type' => $milkType]) }}" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs">
                        <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                        <span>Setup</span>
                    </a>
                @endif
            </div>

        </div>

    </div>

    <!-- Main Content Area: Matrix Table or No Records Found Card matching Screenshot 2 -->
    @if(!$chart)
        <!-- No Records Found state matching Screenshot 2 -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-12 text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center">
                <i data-lucide="table" class="w-8 h-8"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-800">No Records Found.</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                No active rate chart configured for <span class="font-bold text-slate-700">{{ ucfirst($milkType) }}</span> milk sales. Click Setup to configure formulas, FAT/CLR steps, and rates.
            </p>
            <div class="pt-2">
                <a href="{{ route('rates.create', ['category' => 'milk_sale', 'milk_type' => $milkType]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>+ Setup Milk Sale Rate Chart</span>
                </a>
            </div>
        </div>
    @else
        <!-- Rate Chart Details & Live Matrix Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Info Card (4 Cols) -->
            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rate Chart Profile</span>
                        <h4 class="text-base font-extrabold text-slate-900 leading-tight">{{ $chart->name }}</h4>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600 font-medium">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Category:</span>
                        <span class="font-bold text-slate-800 uppercase text-[11px]">{{ $chart->category }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Milk Type:</span>
                        <span class="font-bold text-slate-800 capitalize">{{ $chart->milk_type }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Format:</span>
                        <span class="font-mono font-bold text-blue-700 uppercase">{{ $chart->format ?? 'fat_clr' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Type:</span>
                        <span class="font-bold text-slate-800">{{ str_replace('_', ' ', ucfirst($chart->type ?? 'rate_per_kg')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Base / Starting:</span>
                        <span class="font-mono font-bold text-emerald-800">₹{{ number_format($chart->starting_amount ?: $chart->base_rate, 2) }}</span>
                    </div>
                </div>

                <!-- Configured Steps Summary -->
                <div class="pt-3 border-t border-slate-100 space-y-3">
                    <div>
                        <span class="text-[11px] font-bold text-slate-700">FAT Slabs / Steps:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @forelse($chart->fat_steps ?? [] as $fs)
                                <span class="px-2 py-0.5 bg-blue-50 border border-blue-200 text-blue-900 rounded-lg text-[10px] font-mono font-bold">
                                    {{ $fs['from'] ?? $fs['step'] }}-{{ $fs['to'] ?? ($fs['step'] + 1) }} &rarr; ₹{{ $fs['amount'] }}
                                </span>
                            @empty
                                <span class="text-[10px] text-slate-400 italic">None configured</span>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] font-bold text-slate-700">{{ ($chart->format ?? '') === 'fat_snf' ? 'SNF' : 'CLR' }} Slabs / Steps:</span>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @forelse($chart->snf_steps ?? [] as $ss)
                                <span class="px-2 py-0.5 bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-lg text-[10px] font-mono font-bold">
                                    {{ $ss['from'] ?? $ss['step'] }}-{{ $ss['to'] ?? ($ss['step'] + 1) }} &rarr; ₹{{ $ss['amount'] }}
                                </span>
                            @empty
                                <span class="text-[10px] text-slate-400 italic">None configured</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('rates.edit', $chart->id) }}" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                        <span>Edit Full Matrix & Formulas</span>
                    </a>
                </div>
            </div>

            <!-- Right Matrix Table (8 Cols) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
                <div class="p-3.5 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="grid" class="w-4 h-4 text-emerald-600"></i>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                            {{ $chart->format ?? 'fat_clr' }} Live Matrix
                        </h4>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">Rates in ₹ / Litre</span>
                </div>

                @php
                    $fatSteps = $chart->fat_steps ?? [];
                    $secSteps = $chart->snf_steps ?? [];
                    $isFatClr = ($chart->format ?? 'fat_clr') === 'fat_clr';

                    // Derive ticks
                    $fatMin = !empty($fatSteps) ? min(array_map(fn($s) => (float)($s['from'] ?? $s['step'] ?? 2.0), $fatSteps)) : 2.0;
                    $fatMax = !empty($fatSteps) ? max(array_map(fn($s) => (float)($s['to'] ?? 5.0), $fatSteps)) : 5.0;

                    $fatRows = [];
                    for ($f = $fatMin; $f <= $fatMax + 0.001; $f += 0.2) {
                        $fatRows[] = round($f, 1);
                        if (count($fatRows) >= 20) break;
                    }

                    $secCols = [];
                    if (!empty($secSteps)) {
                        $secMin = min(array_map(fn($s) => (float)($s['from'] ?? $s['step'] ?? ($isFatClr ? 21.0 : 8.0)), $secSteps));
                        $secMax = max(array_map(fn($s) => (float)($s['to'] ?? ($isFatClr ? 26.0 : 9.5)), $secSteps));
                        $secStepInc = $isFatClr ? 1.0 : 0.2;
                        for ($s = $secMin; $s <= $secMax + 0.001; $s += $secStepInc) {
                            $secCols[] = round($s, 1);
                            if (count($secCols) >= 12) break;
                        }
                    } else {
                        $secCols = $isFatClr ? [21.0, 22.0, 23.0, 24.0, 25.0, 26.0] : [8.0, 8.2, 8.4, 8.6, 8.8, 9.0];
                    }
                @endphp

                <div class="overflow-auto max-h-[500px]">
                    <table class="w-full text-center border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 font-bold text-[11px] sticky top-0 z-20">
                                <th class="p-2 border border-slate-200 bg-slate-200 sticky left-0 z-30">
                                    FAT \ {{ $isFatClr ? 'CLR' : 'SNF' }}
                                </th>
                                @foreach($secCols as $col)
                                    <th class="p-2 border border-slate-200 text-center font-bold">
                                        {{ number_format($col, 1) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @foreach($fatRows as $f)
                                <tr>
                                    <td class="p-2 border border-slate-200 bg-slate-50 font-bold text-slate-800 sticky left-0 z-10 font-mono">
                                        {{ number_format($f, 1) }}
                                    </td>
                                    @foreach($secCols as $s)
                                        @php
                                            $calculatedRate = $chart->calculateRate($f, $s);
                                        @endphp
                                        <td class="p-2 border border-slate-200 font-bold font-mono text-slate-800 hover:bg-blue-50 transition cursor-default">
                                            ₹{{ number_format($calculatedRate, 2) }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    @endif

</div>
@endsection
