@extends('layouts.app')

@section('title', 'Customer Groups')
@section('breadcrumb', 'Customer Groups')
@section('header_title', 'Customer Management / Groups')

@section('header_action')
    <button type="button" onclick="document.getElementById('groupModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Create New Group</span>
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header Description -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Customer Groups & Segmentation ({{ $groups->count() }})</h3>
            <p class="text-xs text-slate-500">Segment customers for targeted broadcasts, bulk SMS notifications, and special rate rules.</p>
        </div>
        <button type="button" onclick="document.getElementById('groupModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
            + Add Group
        </button>
    </div>

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($groups as $g)
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300 transition">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $g->color ?? '#10B981' }}"></div>
                            <span class="font-mono text-xs font-bold text-slate-700">{{ $g->slug }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                            {{ $g->customers_count }} Members
                        </span>
                    </div>

                    <h4 class="text-base font-bold text-slate-900">{{ $g->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1">{{ $g->description ?? 'No description provided.' }}</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('customers.index', ['group_id' => $g->id]) }}" class="text-emerald-600 font-bold hover:underline flex items-center gap-1">
                        <span>View {{ $g->customers_count }} Customers &rarr;</span>
                    </a>
                    <form action="{{ route('customers.groups.destroy', $g) }}" method="POST" onsubmit="return confirm('Delete this group?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                <i data-lucide="users" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <p>No customer groups found. Create groups like "Dudh", "Shree Nagar", "Hotels", "VIPs" to organize customers.</p>
            </div>
        @endforelse
    </div>

    <!-- Create Group Modal -->
    <div id="groupModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Create Customer Group</h3>
                <button onclick="document.getElementById('groupModal').classList.add('hidden')" class="text-slate-400">&times;</button>
            </div>
            <form action="{{ route('customers.groups.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Group Name *</label>
                    <input type="text" name="name" required placeholder="e.g. dudh, Shree nagar, Hotels, Bulk VIPs" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description (Optional)</label>
                    <textarea name="description" rows="2" placeholder="e.g. Morning delivery subscribers in Shree nagar sector" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Color</label>
                    <select name="color" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl bg-white">
                        <option value="#10B981">Emerald Green</option>
                        <option value="#3B82F6">Blue</option>
                        <option value="#8B5CF6">Purple</option>
                        <option value="#F59E0B">Amber</option>
                        <option value="#EC4899">Pink</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('groupModal').classList.add('hidden')" class="px-3.5 py-2 text-xs text-slate-600 hover:bg-slate-50 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Save Group</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
