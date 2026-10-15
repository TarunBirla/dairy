<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Mobile Dairy Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center">

    <div class="max-w-4xl w-full space-y-6">
        <!-- Main Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header Banner -->
            <div class="{{ $status === 'success' ? 'bg-gradient-to-r from-emerald-700 via-teal-700 to-emerald-900' : 'bg-gradient-to-r from-rose-700 via-red-700 to-rose-900' }} p-6 sm:p-8 text-white relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-3xl shadow-inner shrink-0">
                            {{ $status === 'success' ? '🚀' : '⚠️' }}
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/10 text-xs font-bold uppercase tracking-wider backdrop-blur mb-1">
                                <span>Laravel Artisan Web Migration</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                                {{ $status === 'success' ? 'Migrations Synchronized Successfully' : 'Migration Error Detected' }}
                            </h1>
                            <p class="text-white/80 text-xs sm:text-sm mt-0.5">
                                Automated schema updates for Mobile Dairy software & all cooperative modules.
                            </p>
                        </div>
                    </div>

                    <div class="self-start sm:self-center">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl {{ $status === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-400/40' : 'bg-rose-950/80 text-rose-300 border border-rose-400/40' }} text-xs font-black uppercase tracking-wider shadow-md">
                            <span class="w-2.5 h-2.5 rounded-full {{ $status === 'success' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                            <span>{{ $status === 'success' ? 'Status: Complete' : 'Status: Failed' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                <!-- Database Information Strip -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-2xl">
                        <span class="text-slate-400 uppercase font-bold text-[10px] tracking-wider block">Database Name</span>
                        <span class="text-base font-extrabold text-white mt-1 block font-mono truncate">{{ $dbName ?: 'MySQL' }}</span>
                        <span class="text-[11px] text-emerald-400 font-semibold mt-0.5 flex items-center gap-1">
                            <span>●</span> Connected & Active
                        </span>
                    </div>

                    <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-2xl">
                        <span class="text-slate-400 uppercase font-bold text-[10px] tracking-wider block">Total Database Tables</span>
                        <span class="text-base font-extrabold text-white mt-1 block font-mono">{{ $tablesCount }} Tables</span>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">All modules configured</span>
                    </div>

                    <div class="bg-slate-950/80 border border-slate-800 p-4 rounded-2xl">
                        <span class="text-slate-400 uppercase font-bold text-[10px] tracking-wider block">Application Cache</span>
                        <span class="text-base font-extrabold text-white mt-1 block font-mono">optimize:clear</span>
                        <span class="text-[11px] text-emerald-400 mt-0.5 block">Views & Routes Refreshed</span>
                    </div>
                </div>

                <!-- Error Box if any -->
                @if($errorMessage)
                    <div class="bg-rose-950/40 border border-rose-500/40 p-4 rounded-2xl text-xs text-rose-300 font-mono space-y-1">
                        <span class="font-bold text-rose-200 block uppercase tracking-wider text-[10px]">Error Details:</span>
                        <div class="whitespace-pre-wrap break-all">{{ $errorMessage }}</div>
                    </div>
                @endif

                <!-- Terminal Execution Log Window -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Artisan Migration & System Logs</span>
                        <span class="text-[10px] font-mono text-slate-500">Execution time: {{ now()->format('H:i:s') }}</span>
                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-inner">
                        <!-- Terminal Bar -->
                        <div class="bg-slate-900/90 px-4 py-2 border-b border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                                <span class="text-slate-400 text-[11px] font-mono font-semibold ml-2">artisan migrate --force</span>
                            </div>
                            <span class="text-[10px] text-slate-500 font-mono">PHP {{ phpversion() }}</span>
                        </div>

                        <!-- Terminal Body -->
                        <div class="p-4 sm:p-5 font-mono text-xs text-slate-300 space-y-2 max-h-72 overflow-y-auto leading-relaxed">
                            @forelse($logs as $log)
                                <div class="whitespace-pre-wrap {{ str_contains($log, '❌') ? 'text-rose-400 font-semibold' : (str_contains($log, '✅') || str_contains($log, '🚀') ? 'text-emerald-400' : 'text-slate-300') }}">{{ $log }}</div>
                            @empty
                                <div class="text-slate-500 italic">No logs generated.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Database Tables List (Collapsible/Preview) -->
                @if($tablesCount > 0)
                    <div class="bg-slate-950/60 border border-slate-800 p-4 rounded-2xl space-y-3" x-data="{ showAll: false }">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Detected Database Tables ({{ $tablesCount }})</h4>
                                <p class="text-[11px] text-slate-500">Live tables registered in MySQL database.</p>
                            </div>
                            <span class="text-[10px] text-emerald-400 font-bold bg-emerald-950/80 border border-emerald-500/30 px-2 py-0.5 rounded-full">
                                Mobile Dairy Tables Verified
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($tables as $tbl)
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-mono font-semibold {{ in_array($tbl, ['farmer_emergency_funds', 'farmers', 'milk_collections', 'product_sales', 'farmer_advances', 'farmer_deductions', 'farmer_invoices']) ? 'bg-emerald-900/50 text-emerald-300 border border-emerald-500/40' : 'bg-slate-800/80 text-slate-300 border border-slate-700/60' }}">
                                    {{ $tbl }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="{{ url('/migrate') }}" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                            <span>⟳ Run Migrate Again</span>
                        </a>
                        <a href="{{ route('setup.database') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs border border-slate-700 transition">
                            Database Setup Wizard
                        </a>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <a href="{{ route('mobile-dairy.index') }}" class="flex-1 sm:flex-initial px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs shadow-md transition text-center">
                            📱 Open Mobile Dairy Suite
                        </a>
                        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs border border-slate-700 transition">
                            Dashboard →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500">
            Smart Dairy Platform &copy; {{ date('Y') }} &bull; Web Migration Endpoint <code class="font-mono text-slate-400">/migrate</code>
        </div>
    </div>

</body>
</html>
