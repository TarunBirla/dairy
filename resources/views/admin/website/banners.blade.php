@extends('layouts.app')

@section('title', 'Website CMS & Banners')
@section('breadcrumb', 'Website CMS')
@section('header_title', 'Super Admin / Frontend CMS & Banners')

@section('header_action')
    <button type="button" onclick="document.getElementById('bModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>+ Add Banner</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Active Banners Grid -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Homepage Hero Banners ({{ $banners->count() }})</h3>
                <p class="text-xs text-slate-500">Control active promotional banners, headlines, and call-to-actions shown on public website.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                <span>View Live Site</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($banners as $b)
                <div class="p-5 rounded-2xl border-2 {{ $b->is_active ? 'border-emerald-500 bg-emerald-50/10' : 'border-slate-200 bg-slate-50/50' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $b->is_active ? 'Active on Website' : 'Hidden' }}
                            </span>
                            <span class="text-xs font-mono text-slate-400">Order: {{ $b->sort_order }}</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase text-emerald-600">{{ $b->badge_text ?? 'PROMO' }}</span>
                        <h4 class="text-base font-bold text-slate-900 mt-1">{{ $b->title }}</h4>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $b->subtitle }}</p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">Button: {{ $b->cta_text }}</span>
                        <form action="{{ route('admin.website.banners.toggle', $b) }}" method="POST">
                            @csrf
                            <button type="submit" class="font-bold {{ $b->is_active ? 'text-amber-600' : 'text-emerald-600' }} hover:underline">
                                {{ $b->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-2 py-8 text-center text-slate-400 text-xs">
                    No custom banners created. The default responsive hero section is currently active.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Website Text & Contact Settings -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <h3 class="text-sm font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100">Contact & Brand Content</h3>
        <form action="{{ route('admin.website.settings') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Helpline Mobile</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '+91 98765 43210' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Support Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? 'support@simpledairy.com' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-xs">
                    Save Website Content
                </button>
            </div>
        </form>
    </div>

    <!-- Modal -->
    <div id="bModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-sm font-bold text-slate-800 pb-3 border-b border-slate-100">Create Hero Banner</h3>
            <form action="{{ route('admin.website.banners.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Tag</label>
                    <input type="text" name="badge_text" placeholder="e.g. 100% PURE & TESTED" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Headline Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Fresh Cow & Buffalo Milk Delivered to Your Doorstep" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-bold">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subtitle / Paragraph</label>
                    <textarea name="subtitle" rows="2" placeholder="e.g. Pure raw non-homogenized milk delivered daily morning before 7:00 AM." class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Button Text</label>
                        <input type="text" name="cta_text" value="Order Pure Milk" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Button Link</label>
                        <input type="text" name="cta_url" value="/products" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('bModal').classList.add('hidden')" class="px-3.5 py-1.5 text-xs text-slate-600">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-xl">Save Banner</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
