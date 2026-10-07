@extends('layouts.app')

@section('title', 'Delivery Routes')
@section('breadcrumb', 'Routes')
@section('header_title', 'Delivery Routes & Staff Allocation')

@section('header_action')
    <div class="flex items-center gap-2">
        <button type="button" @click="openDeliveryBoyModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl shadow-xs transition">
            <i data-lucide="user-plus" class="w-3.5 h-3.5 text-emerald-400"></i>
            <span>+ Add Delivery Boy</span>
        </button>
        <button type="button" @click="routeModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>+ Create New Route</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6" x-data="routesManager()">

    <!-- Header Stats Banner -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">ACTIVE DELIVERY ROUTES</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">{{ $routes->count() }} Routes</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Configure zones, localities, and assign dedicated delivery staff.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="openDeliveryBoyModal()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i data-lucide="bike" class="w-4 h-4 text-emerald-600"></i>
                <span>Add Delivery Boy</span>
            </button>
            <button type="button" @click="routeModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                + Create New Route
            </button>
        </div>
    </div>

    <!-- Routes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($routes as $r)
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300 transition">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $r->code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                            {{ $r->status }}
                        </span>
                    </div>

                    <h4 class="text-base font-bold text-slate-900">{{ $r->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>{{ $r->area_name }}</span>
                    </p>

                    <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Delivery Boy:</span>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="bike" class="w-3.5 h-3.5 {{ $r->deliveryBoy ? 'text-emerald-600' : 'text-slate-300' }}"></i>
                                <span class="font-bold {{ $r->deliveryBoy ? 'text-slate-800' : 'text-amber-600' }}">
                                    {{ $r->deliveryBoy ? $r->deliveryBoy->name : 'Unassigned' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Subscribed Stops:</span>
                            <span class="font-black text-emerald-700">{{ $r->customers_count }} Customers</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center">
                    <a href="{{ route('delivery.board', ['route_id' => $r->id]) }}" class="text-xs text-emerald-600 font-bold hover:underline flex items-center gap-1">
                        <span>View Daily Dispatch Board</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                <i data-lucide="map" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <p>No delivery routes found. Click above to add your first route.</p>
            </div>
        @endforelse
    </div>

    <!-- 1. Create Route Modal (Layer 50) -->
    <div x-show="routeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-route-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="routeModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="routeModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="routeModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <div class="p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800" id="modal-route-title">Create Delivery Route</h3>
                        <button type="button" @click="routeModal = false" class="text-slate-400 hover:text-slate-600 p-1 text-lg leading-none">&times;</button>
                    </div>

                    <form action="{{ route('delivery.routes.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Route Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Route A - Vijay Nagar Sector" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Route Code *</label>
                                <input type="text" name="code" required value="RT-{{ sprintf('%03d', count($routes) + 1) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl font-mono bg-slate-50 text-slate-600">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-semibold text-slate-700">Assign Delivery Boy</label>
                                    <button type="button" @click="openDeliveryBoyModal()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">
                                        + Add new
                                    </button>
                                </div>
                                <select name="delivery_boy_id" x-model="selectedDeliveryBoyId" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition bg-white">
                                    <option value="">Unassigned</option>
                                    <template x-for="db in deliveryBoys" :key="db.id">
                                        <option :value="db.id" x-text="db.name" :selected="db.id == selectedDeliveryBoyId"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Localities / Areas Covered *</label>
                            <input type="text" name="area_name" required placeholder="e.g. Scheme 54, Bapat Square, Vijay Nagar" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Branch (Optional)</label>
                            <select name="branch_id" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition bg-white">
                                <option value="">Main Dairy / All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="routeModal = false" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition">Save Route</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Add Delivery Boy Modal (Layer 70 - Front Modal) -->
    <div x-show="deliveryBoyModal" x-cloak class="fixed inset-0 z-70 overflow-y-auto" aria-labelledby="modal-db-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="deliveryBoyModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" @click="deliveryBoyModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="deliveryBoyModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <div class="p-6">
                    <div class="flex items-start justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900" id="modal-db-title">Add New Delivery Boy</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Quickly register delivery staff for routes & mobile app dispatch.</p>
                        </div>
                        <button type="button" @click="deliveryBoyModal = false" class="text-slate-400 hover:text-slate-600 p-1 text-lg leading-none">&times;</button>
                    </div>

                    <div class="mt-4 space-y-3.5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" x-model="dbForm.name" x-ref="dbNameInput" placeholder="e.g. Ramesh Kumar" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile Number *</label>
                                <input type="text" x-model="dbForm.phone" placeholder="9876543210" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Login Password</label>
                                <input type="text" x-model="dbForm.password" placeholder="Default: 123456" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="email" x-model="dbForm.email" placeholder="ramesh@simpledairy.com (auto-generated if empty)" class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        </div>

                        <p x-show="dbError" x-text="dbError" class="text-rose-600 text-xs mt-1 font-medium"></p>
                    </div>

                    <div class="mt-5 flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="deliveryBoyModal = false" class="px-4 py-2 text-xs font-medium text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-xl transition">Cancel</button>
                        <button type="button" @click="saveDeliveryBoy()" :disabled="savingDb" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 rounded-xl shadow-xs transition flex items-center gap-1.5">
                            <span x-show="!savingDb">Create Delivery Boy</span>
                            <span x-show="savingDb">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function routesManager() {
    return {
        routeModal: false,
        deliveryBoyModal: false,
        selectedDeliveryBoyId: '',
        deliveryBoys: @json($deliveryBoys),
        
        dbForm: {
            name: '',
            phone: '',
            email: '',
            password: 'password123'
        },
        dbError: '',
        savingDb: false,

        openDeliveryBoyModal() {
            this.dbError = '';
            this.dbForm = { name: '', phone: '', email: '', password: 'password123' };
            this.deliveryBoyModal = true;
            this.$nextTick(() => {
                if (this.$refs.dbNameInput) this.$refs.dbNameInput.focus();
            });
        },

        async saveDeliveryBoy() {
            if (!this.dbForm.name.trim() || !this.dbForm.phone.trim()) {
                this.dbError = 'Please provide both Name and Mobile Number.';
                return;
            }
            this.dbError = '';
            this.savingDb = true;

            try {
                const response = await fetch('{{ route('delivery.boys.ajax') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.dbForm)
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    this.deliveryBoys.push(data.delivery_boy);
                    this.selectedDeliveryBoyId = data.delivery_boy.id;
                    this.deliveryBoyModal = false;
                } else {
                    this.dbError = data.message || 'Error saving delivery boy.';
                }
            } catch (err) {
                this.dbError = 'Network error. Please try again.';
            } finally {
                this.savingDb = false;
            }
        }
    }
}
</script>
@endsection
