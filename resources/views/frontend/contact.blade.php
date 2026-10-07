@extends('layouts.frontend')

@section('title', 'Contact Us & Customer Support')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 space-y-10">

    <div class="text-center space-y-2">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Get in Touch</h1>
        <p class="text-xs text-slate-500">Need milk delivery inquiries, bottle pickup, or partnership?</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Contact Info Cards -->
        <div class="space-y-4">
            <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="phone" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase">Customer Helpline</h4>
                    <p class="text-base font-black text-slate-900 mt-0.5">+91 98765 43210</p>
                    <span class="text-[11px] text-slate-500">Mon-Sun: 5:00 AM - 8:00 PM</span>
                </div>
            </div>

            <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="mail" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase">Email Support</h4>
                    <p class="text-base font-black text-slate-900 mt-0.5">support@simpledairy.com</p>
                    <span class="text-[11px] text-slate-500">Fast reply within 2 hours</span>
                </div>
            </div>

            <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-xs flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase">Dairy Plant & Head Office</h4>
                    <p class="text-sm font-bold text-slate-900 mt-0.5">Dairy Processing Complex, Industrial Area, Indore, MP</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs">
            <h3 class="text-base font-bold text-slate-900 mb-4">Send Us a Message</h3>
            <form action="#" method="POST" onsubmit="alert('Thank you! Our support team will call you shortly.'); return false;" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Your Name *</label>
                    <input type="text" required placeholder="e.g. Anand Sharma" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Phone *</label>
                    <input type="text" required placeholder="9876543210" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Message / Inquiry *</label>
                    <textarea rows="3" required placeholder="I want to start a daily morning 2-liter buffalo milk subscription..." class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    Submit Inquiry
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
