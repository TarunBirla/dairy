<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ \App\Models\SystemSetting::get('dairy_name', 'Simple Dairy') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
        
        <!-- Left Side: Login Form -->
        <div class="p-8 sm:p-10 flex flex-col justify-between">
            <div>
                <!-- Brand Logo & Title -->
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white font-bold flex items-center justify-center text-lg shadow-sm">
                        SD
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ \App\Models\SystemSetting::get('dairy_name', 'Simple Dairy') }}
                        </h1>
                        <p class="text-xs text-slate-400">Dairy Management & Delivery Platform</p>
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Welcome Back</h2>
                    <p class="text-xs text-slate-500 mt-1">Sign in with your registered email or mobile number.</p>
                </div>

                @if($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3 rounded-xl">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email or Mobile Number</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="login" 
                                required 
                                value="{{ old('login', 'admin@simpledairy.com') }}"
                                placeholder="e.g. admin@simpledairy.com or 9876543210" 
                                class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Password</label>
                            <span class="text-[11px] text-emerald-600">Default: password</span>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            value="password"
                            placeholder="••••••••" 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 focus:outline-none transition"
                        >
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <span>Remember session</span>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition transform hover:scale-[1.01]"
                    >
                        Sign In to DairyMaster
                    </button>
                </form>
            </div>

            <div class="pt-6 border-t border-slate-100 text-[11px] text-slate-400 text-center">
                &copy; {{ date('Y') }} Simple Dairy Platform • High Security Operations
            </div>
        </div>

        <!-- Right Side: 1-Click Role Switcher Demo Bar -->
        <div class="bg-gradient-to-br from-emerald-50 via-teal-50 to-slate-50 p-8 sm:p-10 border-t md:border-t-0 md:border-l border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">1-Click Fast Login</span>
                    <span class="text-xs text-slate-400">• Testing All Roles</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Select Any User Role</h3>
                <p class="text-xs text-slate-500 mb-4">Click below to test the platform as different stakeholders:</p>

                <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                    @foreach($demoUsers as $u)
                        <a 
                            href="{{ route('login.demo', $u->id) }}" 
                            class="block p-3 bg-white rounded-xl border border-slate-200 hover:border-emerald-400 hover:shadow-xs transition group"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-bold flex items-center justify-center text-xs group-hover:bg-emerald-600 group-hover:text-white transition">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800 group-hover:text-emerald-700 transition leading-tight">{{ $u->name }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $u->email }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider capitalize {{ $u->role_badge_class }}">
                                    {{ str_replace('_', ' ', $u->role) }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 p-3 bg-white/80 rounded-xl border border-slate-200/80 text-[11px] text-slate-600">
                <span class="font-bold text-emerald-700">Tip:</span> You can also switch roles anytime directly from the top navigation bar inside the dashboard!
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
