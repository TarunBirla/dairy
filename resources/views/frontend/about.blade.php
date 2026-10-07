@extends('layouts.frontend')

@section('title', 'About Us | Gopal Dairy Enterprise')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 space-y-16">

    <!-- Header Section -->
    <div class="text-center space-y-4 max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#002e79] text-xs font-black uppercase tracking-wider">
            Our Heritage & Commitment
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight">
            Bridging Dedicated Village Farmers Directly With City Families
        </h1>
        <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
            Founded with a transparent vision: ensure dairy farmers receive accurate, automated FAT/SNF payments on time, while urban families receive pure, untampered farm milk every single dawn.
        </p>
    </div>

    <!-- Core Values & Technology -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs hover:border-[#002e79] transition text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-3xl mx-auto mb-4">
                🔬
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">100% Lab Tested Purity</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Zero synthetic preservatives, zero adulteration. Every procurement batch undergoes ultrasonic analyzer testing for FAT, SNF and water adulteration.
            </p>
        </div>

        <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs hover:border-[#002e79] transition text-center">
            <div class="w-16 h-16 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center text-3xl mx-auto mb-4">
                🤝
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Farmer Empowerment</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Empowering more than 500+ rural farmers with fair, computerized rate charts, micro-advances, cattle feed supply, and prompt bank ledger credits.
            </p>
        </div>

        <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs hover:border-[#002e79] transition text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center text-3xl mx-auto mb-4">
                ⏰
            </div>
            <h3 class="text-lg font-black text-slate-900 mb-2">Strict Cold-Chain Delivery</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Chilled instantly to 4°C at bulk milk coolers (BMC) and dispatched via route delivery boys to your door before 7:00 AM every single morning.
            </p>
        </div>
    </div>

    <!-- Banner Info Box -->
    <div class="bg-gradient-to-r from-[#002e79] to-[#001c4a] rounded-3xl p-8 sm:p-12 text-white flex flex-col md:flex-row items-center justify-between gap-8 shadow-xl">
        <div class="space-y-2 max-w-xl">
            <h3 class="text-2xl font-black">Experience Pure Milk at Your Doorstep</h3>
            <p class="text-xs sm:text-sm text-blue-200">Try our farm-fresh Cow or Buffalo milk subscription with flexible vacation pause and bottle delivery.</p>
        </div>
        <a href="{{ route('contact') }}" class="px-8 py-3.5 bg-[#c25e16] hover:bg-[#a94f10] text-white font-extrabold text-xs rounded-xl shadow-lg shrink-0 transition">
            Contact Support & Join
        </a>
    </div>

</div>
@endsection
