@extends('layouts.frontend')

@section('title', 'Contact Support & Inquiries | Gopal Dairy')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 space-y-12">

    <!-- Header -->
    <div class="text-center space-y-3 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-[#002e79] text-xs font-black uppercase tracking-wider">
            Quick Assistance
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">We Are Here To Assist You</h1>
        <p class="text-xs sm:text-sm text-slate-500">Need milk delivery inquiries, bottle pickup, farmer enrollment, or partnership?</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Contact Information Cards -->
        <div class="lg:col-span-5 space-y-4">
            <div class="p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center shrink-0 text-xl">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer Helpline</h4>
                    <p class="text-base font-black text-slate-900 mt-0.5">+91 98765 43210</p>
                    <span class="text-xs text-slate-500">Mon-Sun: 5:00 AM - 8:30 PM (Direct Call)</span>
                </div>
            </div>

            <div class="p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 text-[#c25e16] flex items-center justify-center shrink-0 text-xl">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Email Support</h4>
                    <p class="text-base font-black text-slate-900 mt-0.5">info@gopaldairy.com</p>
                    <span class="text-xs text-slate-500">Prompt responses within 2 business hours</span>
                </div>
            </div>

            <div class="p-6 bg-white rounded-3xl border border-slate-200/90 shadow-xs flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#002e79] flex items-center justify-center shrink-0 text-xl">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dairy Processing Plant</h4>
                    <p class="text-sm font-bold text-slate-900 mt-0.5">Plot 42, Dairy Processing Zone, Industrial Area, Indore, MP</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="lg:col-span-7 bg-white p-8 sm:p-10 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-lg font-black text-slate-900 mb-2">Send Us an Inquiry</h3>
            <p class="text-xs text-slate-500 mb-6">Our representative will get back to you with delivery routes & rates.</p>

            <form action="#" method="POST" onsubmit="alert('Thank you! Your inquiry has been received. Our team will contact you shortly.'); return false;" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Your Name *</label>
                        <input type="text" required placeholder="e.g. Narendra Malviya" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone *</label>
                        <input type="text" required placeholder="9876543210" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Locality / Address</label>
                    <input type="text" placeholder="e.g. Flat 102, Scheme 54, Vijay Nagar" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Message / Requirements *</label>
                    <textarea rows="4" required placeholder="I need daily 2 liters cow milk in glass bottles delivered before 6:30 AM..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:border-[#002e79] focus:outline-none"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#c25e16] hover:bg-[#a94f10] text-white font-extrabold text-xs rounded-xl shadow-md shadow-orange-900/20 transition">
                    Submit Message
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
