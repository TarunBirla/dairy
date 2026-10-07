@extends('layouts.app')

@section('title', 'Platform Settings')
@section('breadcrumb', 'Settings')
@section('header_title', 'Dairy Configuration & Notification Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Settings Form -->
        <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-4">Dairy Organization Profile</h3>

            <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dairy Business Name</label>
                        <input type="text" name="dairy_name" value="{{ $settings['dairy_name'] ?? 'Simple Dairy' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Owner Name</label>
                        <input type="text" name="owner_name" value="{{ $settings['owner_name'] ?? 'Narendra Malviya' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Official Mobile / Helpline</label>
                        <input type="text" name="phone" value="{{ $settings['phone'] ?? '+91 98765 43210' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Official Email</label>
                        <input type="email" name="email" value="{{ $settings['email'] ?? 'contact@simpledairy.com' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ $settings['tagline'] ?? 'Fresh, Pure & Natural Dairy Products' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Physical Address / Processing Unit</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg">{{ $settings['address'] ?? 'Plot 42, Industrial Dairy Area' }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Morning Collection Shift</label>
                        <input type="text" name="morning_shift" value="{{ $settings['morning_shift'] ?? '06:00 - 09:30' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Evening Collection Shift</label>
                        <input type="text" name="evening_shift" value="{{ $settings['evening_shift'] ?? '17:00 - 20:30' }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg font-mono">
                    </div>
                </div>

                <div class="pt-4 flex justify-end border-t border-slate-100">
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs">
                        Save Dairy Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Right 1 Col: Notification Simulator & Branches -->
        <div class="space-y-6">
            
            <!-- WhatsApp / SMS Test Simulator -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-xs font-bold text-slate-900">WhatsApp / SMS Simulator</h3>
                </div>

                <form action="{{ route('settings.test-notification') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Recipient Phone</label>
                        <input type="text" name="phone" required placeholder="9826011111" value="9826011111" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Channel</label>
                        <select name="channel" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">
                            <option value="whatsapp">WhatsApp Business API</option>
                            <option value="sms">Transactional SMS Gateway</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Message Content</label>
                        <textarea name="message" rows="2" class="w-full px-2.5 py-1.5 text-xs border border-slate-200 rounded-lg">Your milk bill of INR 1440.00 for September is generated. Pay via UPI: simpledairy@upi</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-xs">
                        Simulate & Send Notification
                    </button>
                </form>
            </div>

            <!-- Branches Mini List -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <h4 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 mb-2">Dairy Branches & Plants ({{ $branches->count() }})</h4>
                <div class="space-y-2 text-xs">
                    @foreach($branches as $b)
                        <div class="p-2 bg-slate-50 rounded-lg">
                            <p class="font-bold text-slate-900">{{ $b->name }} ({{ $b->code }})</p>
                            <p class="text-[11px] text-slate-500">{{ $b->city }} • {{ $b->phone }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
