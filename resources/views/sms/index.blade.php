@extends('layouts.app')

@section('title', 'SMS & WhatsApp Broadcast')
@section('breadcrumb', 'SMS Broadcast')
@section('header_title', 'Communications / Send SMS & Notifications')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h3 class="text-base font-bold text-slate-900">Send SMS / WhatsApp Notification</h3>
            <p class="text-xs text-slate-500 mt-0.5">Send milk delivery alerts, billing reminders, or festival greetings to customer groups.</p>
        </div>

        <form action="{{ route('sms.send') }}" method="POST" class="space-y-5" x-data="{ 
            recipientType: 'group',
            msgText: 'Namaste! Your fresh milk delivery for tomorrow morning has been scheduled. Thank you - Simple Dairy.'
        }">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-2">Recipient Target *</label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="p-3 border rounded-2xl flex items-center gap-2.5 cursor-pointer text-xs font-bold"
                        :class="recipientType === 'group' ? 'border-emerald-500 bg-emerald-50/30 text-emerald-900' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="recipient_type" value="group" x-model="recipientType" class="text-emerald-600">
                        <span>Customer Group</span>
                    </label>

                    <label class="p-3 border rounded-2xl flex items-center gap-2.5 cursor-pointer text-xs font-bold"
                        :class="recipientType === 'all' ? 'border-emerald-500 bg-emerald-50/30 text-emerald-900' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="recipient_type" value="all" x-model="recipientType" class="text-emerald-600">
                        <span>All Active ({{ $totalCustomers }})</span>
                    </label>

                    <label class="p-3 border rounded-2xl flex items-center gap-2.5 cursor-pointer text-xs font-bold"
                        :class="recipientType === 'individual' ? 'border-emerald-500 bg-emerald-50/30 text-emerald-900' : 'border-slate-200 text-slate-600'">
                        <input type="radio" name="recipient_type" value="individual" x-model="recipientType" class="text-emerald-600">
                        <span>Custom Number</span>
                    </label>
                </div>
            </div>

            <!-- Select Group -->
            <div x-show="recipientType === 'group'">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Customer Group *</label>
                <select name="group_id" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-white">
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->customers_count }} Members)</option>
                    @endforeach
                </select>
            </div>

            <!-- Single Mobile -->
            <div x-show="recipientType === 'individual'">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Number (with country code)</label>
                <input type="text" name="phone" placeholder="+919876543210" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">SMS Template / Type *</label>
                <select name="template_type" @change="
                    if ($event.target.value === 'bill') msgText = 'Dear Customer, your monthly dairy milk bill is ₹1,450. Please pay via UPI to keep service active. Simple Dairy';
                    if ($event.target.value === 'delivery') msgText = 'Namaste! Morning milk delivery is out for dispatch with delivery boy Suresh. Simple Dairy';
                    if ($event.target.value === 'holiday') msgText = 'Dear Customer, tomorrow on festival occasion, morning milk delivery will happen at 6:30 AM. Simple Dairy';
                " class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl bg-white">
                    <option value="delivery">Daily Delivery Out For Dispatch</option>
                    <option value="bill">Monthly Bill & Dues Reminder</option>
                    <option value="holiday">Special Timing / Holiday Notice</option>
                    <option value="custom">Custom Message Broadcast</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Message Content *</label>
                <textarea name="message" x-model="msgText" rows="4" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl font-sans"></textarea>
                <span class="text-[11px] text-slate-400 mt-1 block">Supports Unicode Hindi, English and standard 160 GSM character lengths.</span>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Send SMS Broadcast Now</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
