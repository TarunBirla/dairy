<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | {{ \App\Models\SystemSetting::get('dairy_name', 'Gopal Dairy') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ["'Plus Jakarta Sans'", "sans-serif"],
                    },
                    fontWeight: {
                        extrabold: '700',
                        black: '700',
                    },
                    colors: {
                        brand: {
                            50:  '#eef4ff',
                            100: '#dbe8ff',
                            200: '#b8d0ff',
                            500: '#002e79',
                            600: '#002765',
                            700: '#001f52',
                            800: '#00183f',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }
        .font-black, .font-extrabold {
            font-weight: 700 !important;
        }
    </style>
</head>
<body class="bg-[#F4F6FC] text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-4xl w-full grid grid-cols-1 md:grid-cols-2 bg-white rounded-3xl shadow-2xl border border-slate-200/80 overflow-hidden">
        
        <!-- Left Side: Login Form -->
        <div class="p-8 sm:p-10 flex flex-col justify-between">
            <div>
                <!-- Brand Logo using public/logo.PNG -->
                <div class="mb-6">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('logo.PNG') }}" alt="Gopal Dairy" class="h-12 w-auto object-contain">
                    </a>
                </div>

                <div class="mb-6">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Portal Sign In</h2>
                    <p class="text-xs text-slate-500 mt-1">Access dairy operations, collection slips & delivery routes.</p>
                </div>

                @if($errors->any())
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3 rounded-xl">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email or Mobile Number</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="login" 
                                required 
                                value="{{ old('login', 'admin@simpledairy.com') }}"
                                placeholder="e.g. admin@simpledairy.com or 9876543210" 
                                class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#002e79]/20 focus:border-[#002e79] focus:outline-none transition"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Password</label>
                            <span class="text-[11px] text-[#002e79] font-medium">Default: password</span>
                        </div>
                        <input 
                            type="password" 
                            name="password" 
                            required 
                            value="password"
                            placeholder="••••••••" 
                            class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#002e79]/20 focus:border-[#002e79] focus:outline-none transition"
                        >
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded text-[#002e79] focus:ring-[#002e79] border-slate-300">
                            <span>Keep me logged in</span>
                        </label>
                        <a href="{{ route('home') }}" class="text-[#002e79] hover:underline font-semibold text-xs">Back to Website</a>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-3 bg-[#002e79] hover:bg-[#00235b] text-white font-extrabold text-xs rounded-xl shadow-lg shadow-blue-900/20 transition transform hover:scale-[1.01]"
                    >
                        Sign In to Portal
                    </button>
                </form>
            </div>

            <div class="pt-6 border-t border-slate-100 text-[11px] text-slate-400 text-center">
                &copy; {{ date('Y') }} Gopal Dairy Management &bull; Secure Enterprise Portal
            </div>
        </div>

        <!-- Right Side: 1-Click Role Switcher Demo Bar -->
        <div class="bg-gradient-to-br from-blue-50/70 via-slate-50 to-orange-50/40 p-8 sm:p-10 border-t md:border-t-0 md:border-l border-slate-200 flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-[#002e79]">1-Click Demo Login</span>
                    <span class="text-xs text-slate-400">&bull; Select Role</span>
                </div>
                <h3 class="text-base font-black text-slate-900 mb-1">Testing Quick Roles</h3>
                <p class="text-xs text-slate-500 mb-4">Click below to enter the dashboard instantly under any role:</p>

                <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                    @foreach($demoUsers as $u)
                        <a 
                            href="{{ route('login.demo', $u->id) }}" 
                            class="block p-3 bg-white rounded-xl border border-slate-200 hover:border-[#002e79] hover:shadow-xs transition group"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#002e79] font-black flex items-center justify-center text-xs group-hover:bg-[#002e79] group-hover:text-white transition">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800 group-hover:text-[#002e79] transition leading-tight">{{ $u->name }}</p>
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

            <div class="mt-4 p-3 bg-white rounded-xl border border-slate-200 text-[11px] text-slate-600">
                <span class="font-bold text-[#002e79]">Admin Note:</span> All 7 roles are fully configured with custom permissions and navigation modules.
            </div>
        </div>

    </div>

</body>
</html>
