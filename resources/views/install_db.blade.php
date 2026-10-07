<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dairy Platform Database Auto-Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-slate-800 border border-slate-700/80 rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-700 p-6 sm:p-8 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-3xl shadow-inner">
                    🥛
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">Dairy Platform Live Setup</h1>
                    <p class="text-emerald-100 text-sm mt-0.5">Automated Database Migrations & Seed Data Wizard</p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <!-- Connection Status -->
            <div class="bg-slate-900/60 border border-slate-700 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Database Connection</span>
                    @if($dbConnected)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Connected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-500/10 text-red-400 border border-red-500/20 rounded-full text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span> Disconnected
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs sm:text-sm">
                    <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/50">
                        <span class="text-slate-400 block text-xs">Total Tables:</span>
                        <span class="text-lg font-bold text-white">{{ $tablesCount }} Tables</span>
                    </div>
                    <div class="bg-slate-800/80 p-3 rounded-xl border border-slate-700/50">
                        <span class="text-slate-400 block text-xs">Users Seeded:</span>
                        <span class="text-lg font-bold text-white">{{ $usersCount }} Users</span>
                    </div>
                </div>

                @if($dbError)
                    <div class="mt-4 p-3 bg-red-500/10 border border-red-500/30 rounded-xl text-xs text-red-300 font-mono break-all">
                        {{ $dbError }}
                    </div>
                @endif
            </div>

            <!-- Execution Logs -->
            @if(count($logs) > 0)
                <div class="space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Execution Process</h3>
                    <div class="bg-slate-950 border border-slate-700 rounded-2xl p-4 font-mono text-xs text-slate-300 space-y-2 max-h-60 overflow-y-auto">
                        @foreach($logs as $log)
                            <div class="whitespace-pre-wrap {{ str_contains($log, 'Error') ? 'text-red-400' : (str_contains($log, '✅') || str_contains($log, '🌱') ? 'text-emerald-300' : 'text-slate-300') }}">{{ $log }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Success Alert -->
            @if($status === 'success')
                <div class="p-4 bg-emerald-500/15 border border-emerald-500/30 rounded-2xl flex items-start gap-3 text-emerald-300">
                    <i class="fa-solid fa-circle-check text-xl mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-sm">Database Setup Completed Successfully!</h4>
                        <p class="text-xs text-emerald-300/80 mt-0.5">All tables, indexes, roles, permissions, demo users, products, rate charts, and frontend banners are ready.</p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                @if($status === 'success' || ($tablesCount > 0 && $usersCount > 0))
                    <a href="{{ route('login') }}" class="w-full sm:flex-1 py-3 px-6 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-center shadow-lg shadow-emerald-900/40 transition">
                        <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i> Go To Login
                    </a>
                    <a href="{{ route('home') }}" class="w-full sm:w-auto py-3 px-5 bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold rounded-xl text-center transition">
                        <i class="fa-solid fa-globe mr-2"></i> View Website
                    </a>
                    <a href="{{ route('setup.database', ['run' => 1, 'wipe' => 1]) }}" onclick="return confirm('Are you sure you want to re-run and overwrite existing tables?')" class="w-full sm:w-auto py-3 px-4 text-xs text-amber-400 hover:text-amber-300 font-medium text-center">
                        <i class="fa-solid fa-rotate mr-1"></i> Fresh Reset & Re-Seed
                    </a>
                @else
                    <a href="{{ route('setup.database', ['run' => 1]) }}" class="w-full py-3.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl text-center shadow-lg shadow-emerald-900/40 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-bolt"></i> Run 1-Click Database Setup (Migrate + Seed)
                    </a>
                @endif
            </div>

            <!-- Credentials Box -->
            <div class="border-t border-slate-700/80 pt-5 text-xs text-slate-400">
                <span class="font-bold text-slate-300 block mb-2">Default Admin Credentials:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-900/50 p-3 rounded-xl border border-slate-700/40">
                    <div><span class="text-slate-500">Super Admin:</span> <code class="text-emerald-400">superadmin@simpledairy.com</code> / <code class="text-slate-300">password</code></div>
                    <div><span class="text-slate-500">Dairy Admin:</span> <code class="text-emerald-400">admin@simpledairy.com</code> / <code class="text-slate-300">password</code></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
