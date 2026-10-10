@extends('layouts.app')

@section('title', 'Center Information')
@section('breadcrumb', 'Center Information')
@section('header_title')
<div class="flex items-center gap-2">
    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-100 text-[#002e79] font-mono">[{{ $center->code ?? 'JC4217' }}]</span>
    <span>{{ $center->name ?? \App\Models\SystemSetting::get('dairy_name', 'Shree Gopal Dudh Dairy Bediya') }}</span>
</div>
@endsection

@section('content')
<div x-data="centerInformationApp()" class="space-y-6">

    <!-- Top Breadcrumb / Title Bar -->
    <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
        <div class="flex items-center gap-1.5">
            <span>Center Information</span>
            <span>-&gt;</span>
            <span class="text-slate-800 font-bold">{{ $center->name ?? \App\Models\SystemSetting::get('dairy_name', 'Shree Gopal Dudh Dairy Bediya') }}</span>
        </div>
    </div>

    <!-- Main Settings Card with Tabs -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        
        <!-- Card Header Title -->
        <div class="px-5 pt-4 pb-2 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Settings</h3>
        </div>

        <!-- Horizontal Tab Bar (Parity with Mobile Dairy) -->
        <div class="flex items-center overflow-x-auto border-b border-slate-200 bg-[#f8fafc] px-2 scrollbar-none text-xs">
            
            <!-- Collection Settings Tab (Active) -->
            <button type="button" @click="activeTab = 'collection'"
                    :class="activeTab === 'collection' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                Collection Settings
            </button>

            <!-- SMS Settings Tab -->
            <button type="button" @click="activeTab = 'sms'"
                    :class="activeTab === 'sms' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                SMS Settings
            </button>

            <!-- Invoice Settings Tab -->
            <button type="button" @click="activeTab = 'invoice'"
                    :class="activeTab === 'invoice' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                Invoice Settings
            </button>

            <!-- Rate Chart Settings Tab -->
            <button type="button" @click="activeTab = 'rate_chart'"
                    :class="activeTab === 'rate_chart' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                Rate Chart Settings
            </button>

            <!-- Farmer App Settings Tab -->
            <button type="button" @click="activeTab = 'farmer_app'"
                    :class="activeTab === 'farmer_app' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                Farmer App Settings
            </button>

            <!-- Milk Sale Settings Tab -->
            <button type="button" @click="activeTab = 'milk_sale'"
                    :class="activeTab === 'milk_sale' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                Milk Sale Settings
            </button>

            <!-- Other Settings Tab -->
            <button type="button" @click="activeTab = 'other'"
                    :class="activeTab === 'other' ? 'bg-[#98d8c8]/40 text-[#005c53] font-bold border-b-2 border-[#005c53]' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="px-5 py-3 transition whitespace-nowrap">
                Other Settings
            </button>
        </div>

        <!-- Tab 1: Collection Settings Panel -->
        <div x-show="activeTab === 'collection'" class="p-6">
            
            <!-- 2-Column Grid of Settings with 3-dot buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. Collection Type -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Collection Type</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="collectionType"></span>
                        <button type="button" @click="openModal('collectionType')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Milk Type -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Milk Type</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="milkType"></span>
                        <button type="button" @click="openModal('milkType')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 3. Collection Shift -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Collection Shift</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="collectionShift"></span>
                        <button type="button" @click="openModal('collectionShift')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 4. Show Previous Collection -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Show Previous Collection</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="showPreviousCollection"></span>
                        <button type="button" @click="openModal('showPreviousCollection')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 5. Collection Input -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Collection Input</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="collectionInput"></span>
                        <button type="button" @click="openModal('collectionInput')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 6. Offline Collection -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Offline Collection</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="offlineCollection"></span>
                        <button type="button" @click="openModal('offlineCollection')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 7. Morning Shift Time -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Morning Shift Time</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="morningShiftTime || 'Not Available'"></span>
                        <button type="button" @click="openModal('morningShiftTime')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 8. Evening Shift Time -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Evening Shift Time</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="eveningShiftTime || 'Not Available'"></span>
                        <button type="button" @click="openModal('eveningShiftTime')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 9. Collection Print Setting -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Collection Print Setting</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="printFormat"></span>
                        <button type="button" @click="openModal('printSetting')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 10. Previous Shift Bonus / Penalty -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Previous Shift Bonus / Penalty</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-500" x-text="bonusPenaltyStatus"></span>
                        <button type="button" @click="openModal('bonusPenalty')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- Placeholder for Other Tabs (Will detail out in next steps) -->
        <div x-show="activeTab !== 'collection'" class="p-8 text-center text-slate-500 text-xs">
            <div class="max-w-md mx-auto py-8">
                <i data-lucide="sliders" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                <p class="font-bold text-slate-700 text-sm mb-1" x-text="getTabName(activeTab) + ' Tab'"></p>
                <p class="text-slate-400">Settings for this section are ready to be configured as per your reference.</p>
            </div>
        </div>

    </div>

    <!-- Center Users Table Section (Matching Bottom of Screenshot 1) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Center Users</h3>
            <button type="button" @click="showAddUserModal = true" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                <span>+ Add User</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500 font-semibold">
                        <th class="py-2.5 px-5">Name</th>
                        <th class="py-2.5 px-5">Mobile</th>
                        <th class="py-2.5 px-5">Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
                    @forelse($centerUsers as $cUser)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3 px-5 uppercase">{{ $cUser->name }}</td>
                        <td class="py-3 px-5 font-mono text-slate-600">{{ $cUser->phone ?? 'N/A' }}</td>
                        <td class="py-3 px-5">
                            @if(in_array($cUser->role, ['dairy_admin', 'super_admin']))
                                <span class="text-rose-500 font-bold">Owner</span>
                            @elseif($cUser->role === 'collection_operator')
                                <span class="text-blue-600 font-bold">Operator</span>
                            @else
                                <span class="text-slate-600 capitalize">{{ str_replace('_', ' ', $cUser->role) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-4 text-center text-slate-400">No center users configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>


    <!-- ==================== MODALS ==================== -->

    <!-- Modal 1: Collection Type -->
    <div x-show="modal === 'collectionType'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Collection Type</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-2.5 text-xs">
                <template x-for="item in collectionTypeOptions" :key="item">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700 hover:text-slate-900">
                        <input type="radio" name="collection_type_radio" :value="item" x-model="selectedCollectionType" class="text-[#005c53] focus:ring-[#005c53]">
                        <span x-text="item"></span>
                    </label>
                </template>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('collection_type', selectedCollectionType, 'collectionType')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Milk Type -->
    <div x-show="modal === 'milkType'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Milk Type</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Cow" x-model="selectedMilkTypes" class="text-[#005c53] rounded">
                    <span>Cow</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Buffalo" x-model="selectedMilkTypes" class="text-[#005c53] rounded">
                    <span>Buffalo</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Mix" x-model="selectedMilkTypes" class="text-[#005c53] rounded">
                    <span>Mix</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('milk_type', selectedMilkTypes.join(', '), 'milkType')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Collection Shift -->
    <div x-show="modal === 'collectionShift'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Collection Shift</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="shift_radio" value="Morning" x-model="selectedCollectionShift" class="text-[#005c53]">
                    <span>Morning</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="shift_radio" value="Evening" x-model="selectedCollectionShift" class="text-[#005c53]">
                    <span>Evening</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="shift_radio" value="Morning + Evening" x-model="selectedCollectionShift" class="text-[#005c53]">
                    <span>Morning + Evening</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="shift_radio" value="Auto (Morning + Evening)" x-model="selectedCollectionShift" class="text-[#005c53]">
                    <span>Auto (Morning + Evening)</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('collection_shift', selectedCollectionShift, 'collectionShift')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 4: Show Previous Collection -->
    <div x-show="modal === 'showPreviousCollection'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Show Previous Collection</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="prev_col" value="Yesterday's both shift" x-model="selectedPreviousCollection" class="text-[#005c53]">
                    <span>Yesterday's both shift</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="prev_col" value="Previous Shift" x-model="selectedPreviousCollection" class="text-[#005c53]">
                    <span>Previous Shift</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="prev_col" value="None" x-model="selectedPreviousCollection" class="text-[#005c53]">
                    <span>None</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('show_previous_collection', selectedPreviousCollection, 'showPreviousCollection')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 5: Collection Input -->
    <div x-show="modal === 'collectionInput'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Collection Input</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="input_mode" value="Manual" x-model="selectedCollectionInput" class="text-[#005c53]">
                    <span>Manual</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="input_mode" value="Automatic" x-model="selectedCollectionInput" class="text-[#005c53]">
                    <span>Automatic</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="input_mode" value="Manual + Automatic" x-model="selectedCollectionInput" class="text-[#005c53]">
                    <span>Manual + Automatic</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('collection_input', selectedCollectionInput, 'collectionInput')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 6: Offline Collection -->
    <div x-show="modal === 'offlineCollection'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Offline Collection</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="offline_col" value="On" x-model="selectedOfflineCollection" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="offline_col" value="Off" x-model="selectedOfflineCollection" class="text-[#005c53]">
                    <span>Off</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('offline_collection', selectedOfflineCollection, 'offlineCollection')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 7: Morning Shift Time (Matching Screenshot 2, Top Right) -->
    <div x-show="modal === 'morningShiftTime'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Morning Shift Time</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Start Time</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="morningStartHour" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="01">
                        <span>:</span>
                        <input type="text" x-model="morningStartMin" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="00">
                        <select x-model="morningStartAmPm" class="px-2 py-1.5 border border-slate-200 rounded font-bold">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">End Time</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="morningEndHour" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="01">
                        <span>:</span>
                        <input type="text" x-model="morningEndMin" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="00">
                        <select x-model="morningEndAmPm" class="px-2 py-1.5 border border-slate-200 rounded font-bold">
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>

                <p class="text-[11px] text-rose-500 font-semibold">Collections can only be added at the given shift timing</p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveShiftTime('morning')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 8: Evening Shift Time -->
    <div x-show="modal === 'eveningShiftTime'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Evening Shift Time</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block text-slate-600 font-semibold mb-1">Start Time</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="eveningStartHour" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="05">
                        <span>:</span>
                        <input type="text" x-model="eveningStartMin" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="00">
                        <select x-model="eveningStartAmPm" class="px-2 py-1.5 border border-slate-200 rounded font-bold">
                            <option value="PM">PM</option>
                            <option value="AM">AM</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 font-semibold mb-1">End Time</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="eveningEndHour" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="09">
                        <span>:</span>
                        <input type="text" x-model="eveningEndMin" class="w-16 px-2 py-1.5 border border-slate-200 rounded text-center font-bold" placeholder="00">
                        <select x-model="eveningEndAmPm" class="px-2 py-1.5 border border-slate-200 rounded font-bold">
                            <option value="PM">PM</option>
                            <option value="AM">AM</option>
                        </select>
                    </div>
                </div>

                <p class="text-[11px] text-rose-500 font-semibold">Collections can only be added at the given shift timing</p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveShiftTime('evening')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 9: Collection Print Setting (Matching Screenshot 2, Top Left) -->
    <div x-show="modal === 'printSetting'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Collection Print Setting</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                
                <!-- Print Language -->
                <div class="grid grid-cols-3 items-center gap-4">
                    <span class="text-slate-700 font-semibold">Print Language</span>
                    <div class="col-span-2">
                        <select x-model="printSettings.language" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs">
                            <option value="English">English</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Gujarati">Gujarati</option>
                            <option value="Marathi">Marathi</option>
                        </select>
                    </div>
                </div>

                <!-- Receipt Printing Checkboxes -->
                <div class="grid grid-cols-3 gap-4 pt-2">
                    <span class="text-slate-700 font-semibold">Receipt Printing</span>
                    <div class="col-span-2 grid grid-cols-2 gap-y-2 gap-x-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.fat" class="rounded text-[#005c53]">
                            <span>FAT</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.amount" class="rounded text-[#005c53]">
                            <span>Amount</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.snf" class="rounded text-[#005c53]">
                            <span>SNF</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.total_liter" class="rounded text-[#005c53]">
                            <span>Total Liter</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.clr" class="rounded text-[#005c53]">
                            <span>CLR</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.total_amount" class="rounded text-[#005c53]">
                            <span>Total Amount</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.liter" class="rounded text-[#005c53]">
                            <span>Liter</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.center_logo" class="rounded text-[#005c53]">
                            <span>Center Logo</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.rate" class="rounded text-[#005c53]">
                            <span>Rate</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="printSettings.number_in_language" class="rounded text-[#005c53]">
                            <span>Number in language</span>
                        </label>
                    </div>
                </div>

                <!-- Note Input -->
                <div class="grid grid-cols-3 items-center gap-4 pt-2">
                    <span class="text-slate-700 font-semibold">Note</span>
                    <div class="col-span-2">
                        <input type="text" x-model="printSettings.note" placeholder="Enter Note" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs">
                    </div>
                </div>

                <!-- Print Format Radio -->
                <div class="grid grid-cols-3 items-center gap-4 pt-2">
                    <span class="text-slate-700 font-semibold">Print Format</span>
                    <div class="col-span-2 flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-1" x-model="printSettings.format" class="text-[#005c53]">
                            <span>Format-1</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-2" x-model="printSettings.format" class="text-[#005c53]">
                            <span>Format-2</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-3" x-model="printSettings.format" class="text-[#005c53]">
                            <span>Format-3</span>
                        </label>
                    </div>
                </div>

                <!-- Preview Link -->
                <div class="text-right pt-2">
                    <button type="button" @click="showReceiptPreview = true" class="text-blue-600 hover:underline font-semibold text-xs">
                        Click here to view Print Image
                    </button>
                </div>

            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="savePrintSettings()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 10: Receipt Image Preview (Matching Screenshot 2, Bottom Left) -->
    <div x-show="showReceiptPreview" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4" style="z-index: 99999;">
        <div @click.away="showReceiptPreview = false" class="bg-white rounded-lg shadow-2xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Collection Print Preview</h4>
                <button type="button" @click="showReceiptPreview = false" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 bg-slate-50 flex justify-center">
                
                <!-- Mock Thermal Slip (Parity with screenshot) -->
                <div class="bg-white border border-slate-300 p-5 rounded shadow-sm w-64 font-mono text-[11px] leading-tight text-slate-900">
                    <div class="text-center mb-3">
                        <span class="text-cyan-700 font-extrabold text-sm tracking-wider">mobiledairy</span>
                        <h4 class="font-extrabold text-xs mt-1">SAI KRUPA</h4>
                    </div>
                    <div class="mb-2">
                        <p class="font-bold">99-Customer Name</p>
                        <p class="text-[10px] text-slate-600">01/01/2021 03:56 PM</p>
                        <div class="flex justify-between font-bold mt-1 text-[10px]">
                            <span>Morning</span>
                            <span>COW</span>
                        </div>
                    </div>
                    <div class="border-t border-b border-dashed border-slate-300 py-1.5 my-1.5 space-y-0.5">
                        <div class="flex justify-between" x-show="printSettings.fat">
                            <span>FAT</span>
                            <span class="font-bold">3.5</span>
                        </div>
                        <div class="flex justify-between" x-show="printSettings.snf">
                            <span>SNF</span>
                            <span class="font-bold">8.5</span>
                        </div>
                        <div class="flex justify-between" x-show="printSettings.clr">
                            <span>CLR</span>
                            <span class="font-bold">28.5</span>
                        </div>
                        <div class="flex justify-between" x-show="printSettings.rate">
                            <span>Rate</span>
                            <span class="font-bold">44.00</span>
                        </div>
                        <div class="flex justify-between" x-show="printSettings.liter">
                            <span>Liter</span>
                            <span class="font-bold">3.00</span>
                        </div>
                        <div class="flex justify-between" x-show="printSettings.amount">
                            <span>Amount</span>
                            <span class="font-bold">332.00</span>
                        </div>
                    </div>
                    <div class="border-b border-dashed border-slate-300 pb-1.5 mb-1.5 space-y-0.5">
                        <div class="flex justify-between" x-show="printSettings.total_liter">
                            <span>Total Liter</span>
                            <span class="font-bold">99.00</span>
                        </div>
                        <div class="flex justify-between" x-show="printSettings.total_amount">
                            <span>Total Amount</span>
                            <span class="font-bold">999.00</span>
                        </div>
                    </div>
                    <div class="text-center pt-2">
                        <p class="text-[11px] font-semibold" x-text="printSettings.note || 'Thank You :)'"></p>
                    </div>
                </div>

            </div>
            <div class="px-5 py-2.5 bg-slate-100 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showReceiptPreview = false" class="px-4 py-1.5 rounded bg-slate-300 hover:bg-slate-400 text-slate-800 text-xs font-bold">Close Preview</button>
            </div>
        </div>
    </div>

    <!-- Modal 11: Previous Shift Bonus / Penalty (Matching Screenshot 2, Bottom Right) -->
    <div x-show="modal === 'bonusPenalty'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Previous Shift Bonus / Penalty</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3.5 text-xs">
                
                <div class="text-[11px] leading-relaxed text-rose-500 font-semibold pb-2 border-b border-slate-100">
                    <p>Bonus: when previous shift collection is completed.</p>
                    <p>Penalty: when previous shift collection is pending.</p>
                    <p>Not Applicable: No bonus or penalty for this shift.</p>
                </div>

                <!-- Cow Morning -->
                <div class="flex items-center justify-between gap-2">
                    <span class="w-28 font-semibold text-slate-700">Cow Morning :</span>
                    <select x-model="bonusPenaltyData.cow_morning_type" class="px-2 py-1 border border-slate-200 rounded text-xs">
                        <option value="Bonus">Bonus</option>
                        <option value="Penalty">Penalty</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                    <div class="flex items-center gap-1">
                        <span>Rs.</span>
                        <input type="number" step="0.01" x-model="bonusPenaltyData.cow_morning_rate" class="w-16 px-2 py-1 border border-slate-200 rounded text-right font-bold" placeholder="0.00">
                        <span class="text-slate-500 text-[10px]">/Liter</span>
                    </div>
                </div>

                <!-- Buff Morning -->
                <div class="flex items-center justify-between gap-2">
                    <span class="w-28 font-semibold text-slate-700">Buff Morning :</span>
                    <select x-model="bonusPenaltyData.buff_morning_type" class="px-2 py-1 border border-slate-200 rounded text-xs">
                        <option value="Bonus">Bonus</option>
                        <option value="Penalty">Penalty</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                    <div class="flex items-center gap-1">
                        <span>Rs.</span>
                        <input type="number" step="0.01" x-model="bonusPenaltyData.buff_morning_rate" class="w-16 px-2 py-1 border border-slate-200 rounded text-right font-bold" placeholder="0.00">
                        <span class="text-slate-500 text-[10px]">/Liter</span>
                    </div>
                </div>

                <!-- Cow Evening -->
                <div class="flex items-center justify-between gap-2">
                    <span class="w-28 font-semibold text-slate-700">Cow Evening :</span>
                    <select x-model="bonusPenaltyData.cow_evening_type" class="px-2 py-1 border border-slate-200 rounded text-xs">
                        <option value="Bonus">Bonus</option>
                        <option value="Penalty">Penalty</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                    <div class="flex items-center gap-1">
                        <span>Rs.</span>
                        <input type="number" step="0.01" x-model="bonusPenaltyData.cow_evening_rate" class="w-16 px-2 py-1 border border-slate-200 rounded text-right font-bold" placeholder="0.00">
                        <span class="text-slate-500 text-[10px]">/Liter</span>
                    </div>
                </div>

                <!-- Buff Evening -->
                <div class="flex items-center justify-between gap-2">
                    <span class="w-28 font-semibold text-slate-700">Buff Evening :</span>
                    <select x-model="bonusPenaltyData.buff_evening_type" class="px-2 py-1 border border-slate-200 rounded text-xs">
                        <option value="Bonus">Bonus</option>
                        <option value="Penalty">Penalty</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                    <div class="flex items-center gap-1">
                        <span>Rs.</span>
                        <input type="number" step="0.01" x-model="bonusPenaltyData.buff_evening_rate" class="w-16 px-2 py-1 border border-slate-200 rounded text-right font-bold" placeholder="0.00">
                        <span class="text-slate-500 text-[10px]">/Liter</span>
                    </div>
                </div>

            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveBonusPenalty()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal 12: Add Center User -->
    <div x-show="showAddUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="showAddUserModal = false" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Add Center User</h4>
                <button type="button" @click="showAddUserModal = false" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <form action="{{ route('settings.center-information.add-user') }}" method="POST" class="p-5 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="name" required class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs" placeholder="e.g. Ramesh Sharma">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Mobile Number *</label>
                    <input type="text" name="mobile" required class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs" placeholder="10-digit mobile number">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">User Role *</label>
                    <select name="role" required class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs">
                        <option value="collection_operator">Operator</option>
                        <option value="collection_manager">Manager</option>
                        <option value="dairy_admin">Owner</option>
                        <option value="accountant">Accountant</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Password (Optional)</label>
                    <input type="password" name="password" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs" placeholder="Default: 123456">
                </div>
                <div class="pt-3 flex justify-end gap-2 font-bold">
                    <button type="button" @click="showAddUserModal = false" class="px-4 py-1.5 rounded bg-slate-200 text-slate-700 hover:bg-slate-300">Cancel</button>
                    <button type="submit" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Add User</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function centerInformationApp() {
    return {
        activeTab: '{{ $activeTab ?? "collection" }}',
        modal: null,
        showReceiptPreview: false,
        showAddUserModal: false,

        // Settings Values
        collectionType: '{{ $settings["collection_type"] ?? "FAT only" }}',
        milkType: '{{ $settings["milk_type"] ?? "Cow, Buffalo" }}',
        collectionShift: {!! json_encode($settings["collection_shift"] ?? "Auto (Morning + Evening)") !!},
        showPreviousCollection: {!! json_encode($settings["show_previous_collection"] ?? "Yesterday's both shift") !!},
        collectionInput: {!! json_encode($settings["collection_input"] ?? "Manual + Automatic") !!},
        offlineCollection: {!! json_encode($settings["offline_collection"] ?? "On") !!},
        morningShiftTime: {!! json_encode($settings["morning_shift_time"] ?? "Not Available") !!},
        eveningShiftTime: {!! json_encode($settings["evening_shift_time"] ?? "Not Available") !!},
        printFormat: {!! json_encode($printSetting["format"] ?? "Format-2") !!},
        bonusPenaltyStatus: {!! json_encode($settings["bonus_penalty_status"] ?? "") !!},

        // Radio & Form states
        collectionTypeOptions: [
            'FAT only',
            'CLR only',
            'FAT + SNF',
            'FAT + CLR',
            'FAT + CLR + Auto SNF',
            'FAT + SNF + Auto CLR',
            'Liter Only'
        ],
        selectedCollectionType: '{{ $settings["collection_type"] ?? "FAT only" }}',
        selectedMilkTypes: '{{ $settings["milk_type"] ?? "Cow, Buffalo" }}'.split(',').map(s => s.trim()),
        selectedCollectionShift: '{{ $settings["collection_shift"] ?? "Auto (Morning + Evening)" }}',
        selectedPreviousCollection: '{{ $settings["show_previous_collection"] ?? "Yesterday\'s both shift" }}',
        selectedCollectionInput: '{{ $settings["collection_input"] ?? "Manual + Automatic" }}',
        selectedOfflineCollection: '{{ $settings["offline_collection"] ?? "On" }}',

        // Shift Time Models
        morningStartHour: '01',
        morningStartMin: '00',
        morningStartAmPm: 'AM',
        morningEndHour: '01',
        morningEndMin: '00',
        morningEndAmPm: 'AM',

        eveningStartHour: '05',
        eveningStartMin: '00',
        eveningStartAmPm: 'PM',
        eveningEndHour: '09',
        eveningEndMin: '00',
        eveningEndAmPm: 'PM',

        // Print Settings Object
        printSettings: {
            language: '{{ $printSetting["language"] ?? "English" }}',
            fat: {{ isset($printSetting['fat']) ? ($printSetting['fat'] ? 'true' : 'false') : 'true' }},
            amount: {{ isset($printSetting['amount']) ? ($printSetting['amount'] ? 'true' : 'false') : 'false' }},
            snf: {{ isset($printSetting['snf']) ? ($printSetting['snf'] ? 'true' : 'false') : 'true' }},
            total_liter: {{ isset($printSetting['total_liter']) ? ($printSetting['total_liter'] ? 'true' : 'false') : 'false' }},
            clr: {{ isset($printSetting['clr']) ? ($printSetting['clr'] ? 'true' : 'false') : 'true' }},
            total_amount: {{ isset($printSetting['total_amount']) ? ($printSetting['total_amount'] ? 'true' : 'false') : 'false' }},
            liter: {{ isset($printSetting['liter']) ? ($printSetting['liter'] ? 'true' : 'false') : 'true' }},
            center_logo: {{ isset($printSetting['center_logo']) ? ($printSetting['center_logo'] ? 'true' : 'false') : 'false' }},
            rate: {{ isset($printSetting['rate']) ? ($printSetting['rate'] ? 'true' : 'false') : 'true' }},
            number_in_language: {{ isset($printSetting['number_in_language']) ? ($printSetting['number_in_language'] ? 'true' : 'false') : 'false' }},
            note: '{{ $printSetting["note"] ?? "" }}',
            format: '{{ $printSetting["format"] ?? "Format-2" }}'
        },

        // Bonus Penalty Object
        bonusPenaltyData: {
            cow_morning_type: '{{ $bonusPenalty["cow_morning_type"] ?? "Bonus" }}',
            cow_morning_rate: '{{ $bonusPenalty["cow_morning_rate"] ?? "0.00" }}',
            buff_morning_type: '{{ $bonusPenalty["buff_morning_type"] ?? "Bonus" }}',
            buff_morning_rate: '{{ $bonusPenalty["buff_morning_rate"] ?? "0.00" }}',
            cow_evening_type: '{{ $bonusPenalty["cow_evening_type"] ?? "Bonus" }}',
            cow_evening_rate: '{{ $bonusPenalty["cow_evening_rate"] ?? "0.00" }}',
            buff_evening_type: '{{ $bonusPenalty["buff_evening_type"] ?? "Bonus" }}',
            buff_evening_rate: '{{ $bonusPenalty["buff_evening_rate"] ?? "0.00" }}'
        },

        openModal(type) {
            this.modal = type;
        },

        getTabName(tab) {
            const map = {
                'sms': 'SMS Settings',
                'invoice': 'Invoice Settings',
                'rate_chart': 'Rate Chart Settings',
                'farmer_app': 'Farmer App Settings',
                'milk_sale': 'Milk Sale Settings',
                'other': 'Other Settings'
            };
            return map[tab] || 'Settings';
        },

        async saveSetting(key, val, modalToClose) {
            try {
                const response = await fetch('{{ route("settings.center-information.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        setting_key: key,
                        setting_value: val
                    })
                });
                const res = await response.json();
                if (res.success) {
                    if (key === 'collection_type') this.collectionType = val;
                    if (key === 'milk_type') this.milkType = val;
                    if (key === 'collection_shift') this.collectionShift = val;
                    if (key === 'show_previous_collection') this.showPreviousCollection = val;
                    if (key === 'collection_input') this.collectionInput = val;
                    if (key === 'offline_collection') this.offlineCollection = val;
                    this.modal = null;
                }
            } catch (err) {
                console.error(err);
                alert('Error saving setting');
            }
        },

        async saveShiftTime(shift) {
            let val = '';
            let key = '';
            if (shift === 'morning') {
                val = `${this.morningStartHour}:${this.morningStartMin} ${this.morningStartAmPm} - ${this.morningEndHour}:${this.morningEndMin} ${this.morningEndAmPm}`;
                key = 'morning_shift_time';
                this.morningShiftTime = val;
            } else {
                val = `${this.eveningStartHour}:${this.eveningStartMin} ${this.eveningStartAmPm} - ${this.eveningEndHour}:${this.eveningEndMin} ${this.eveningEndAmPm}`;
                key = 'evening_shift_time';
                this.eveningShiftTime = val;
            }
            await this.saveSetting(key, val, null);
            this.modal = null;
        },

        async savePrintSettings() {
            try {
                const response = await fetch('{{ route("settings.center-information.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        setting_key: 'collection_print_settings',
                        ...this.printSettings
                    })
                });
                const res = await response.json();
                if (res.success) {
                    this.printFormat = this.printSettings.format;
                    this.modal = null;
                }
            } catch (err) {
                console.error(err);
                alert('Error saving print settings');
            }
        },

        async saveBonusPenalty() {
            try {
                const response = await fetch('{{ route("settings.center-information.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        setting_key: 'bonus_penalty_settings',
                        ...this.bonusPenaltyData
                    })
                });
                const res = await response.json();
                if (res.success) {
                    this.modal = null;
                }
            } catch (err) {
                console.error(err);
                alert('Error saving bonus/penalty');
            }
        }
    }
}
</script>
@endpush
@endsection
