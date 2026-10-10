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

        <!-- Tab 2: SMS Settings Panel (Parity with Mobile Dairy) -->
        <div x-show="activeTab === 'sms'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. SMS Type -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">SMS Type</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="smsType"></span>
                        <button type="button" @click="openModal('smsType')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Send SMS For -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Send SMS For</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900 truncate max-w-xs" x-text="sendSmsFor"></span>
                        <button type="button" @click="openModal('sendSmsFor')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 3. Farmer App Link in SMS -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Farmer App Link in SMS</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="farmerAppLinkInSms"></span>
                        <button type="button" @click="openModal('farmerAppLinkInSms')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 4. Total in Collection SMS -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Total in Collection SMS</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="totalInCollectionSms"></span>
                        <button type="button" @click="openModal('totalInCollectionSms')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 5. Center Name in SMS -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Center Name in SMS</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="centerNameInSms"></span>
                        <button type="button" @click="openModal('centerNameInSms')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tab 3: Invoice Settings Panel (Parity with Mobile Dairy) -->
        <div x-show="activeTab === 'invoice'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. Payment Period -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Payment Period</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="paymentPeriod"></span>
                        <button type="button" @click="openModal('paymentPeriod')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Payment Register Print Setting -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Payment Register Print Setting</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="paymentRegisterFormat"></span>
                        <button type="button" @click="openModal('paymentRegisterPrintSetting')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 3. Invoice Print Setting -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Invoice Print Setting</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="invoicePrintFormat"></span>
                        <button type="button" @click="openModal('invoicePrintSetting')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tab 4: Rate Chart Settings Panel (Parity with Mobile Dairy) -->
        <div x-show="activeTab === 'rate_chart'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. Rate Chart -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Rate Chart</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="rateChartStatus"></span>
                        <button type="button" @click="openModal('rateChartModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Shift Wise Rate -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Shift Wise Rate</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="'Morning ' + shiftWiseMorning + ' | Evening ' + shiftWiseEvening"></span>
                        <button type="button" @click="openModal('shiftWiseRateModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 3. Farmer Wise Rate -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Farmer Wise Rate</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="farmerWiseRate"></span>
                        <button type="button" @click="openModal('farmerWiseRateModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tab 5: Farmer App Settings Panel (Parity with Mobile Dairy) -->
        <div x-show="activeTab === 'farmer_app'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. Show Advance/Loan Interest Rate in Farmer App -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Show Advance/Loan Interest Rate In Farmer App</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="showAdvanceInterestRate"></span>
                        <button type="button" @click="openModal('showAdvanceInterestRateModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Show Rate Chart -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Show Rate Chart</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="showRateChart"></span>
                        <button type="button" @click="openModal('showRateChartModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 3. Show Collection After Invoice Save -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Show Collection After Invoice Save</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="showCollectionAfterInvoiceSave"></span>
                        <button type="button" @click="openModal('showCollectionAfterInvoiceSaveModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 4. Show Collection After Shift Finish -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Show Collection After Shift Finish</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="showCollectionAfterShiftFinish"></span>
                        <button type="button" @click="openModal('showCollectionAfterShiftFinishModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 5. Hide Collection Rate -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Hide Collection Rate</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="hideCollectionRate"></span>
                        <button type="button" @click="openModal('hideCollectionRateModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tab 6: Milk Sale Settings Panel (Parity with Mobile Dairy) -->
        <div x-show="activeTab === 'milk_sale'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. Billing Period -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Billing Period</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="milkSaleBillingPeriod"></span>
                        <button type="button" @click="openModal('milkSaleBillingPeriodModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Milk Sale Bill Print Setting -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Milk Sale Bill Print Setting</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="milkSalePrintFormat"></span>
                        <button type="button" @click="openModal('milkSalePrintModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Tab 7: Other Settings Panel (Parity with Mobile Dairy) -->
        <div x-show="activeTab === 'other'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                
                <!-- 1. Bank Details -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Bank Details</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="otherBankDetails"></span>
                        <button type="button" @click="openModal('otherBankDetailsModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 2. Weighing Scale Format -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Weighing Scale Format</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="weighingScaleFormat"></span>
                        <button type="button" @click="openModal('weighingScaleModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 3. CLR/Lacto Format -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">CLR/Lacto Format</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="clrLactoFormat"></span>
                        <button type="button" @click="openModal('clrLactoModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 4. Contact Details -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Contact Details</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900 truncate max-w-[180px]" x-text="contactDetailsSummary"></span>
                        <button type="button" @click="openModal('contactDetailsModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 5. FAT Format -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">FAT Format</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="fatFormat"></span>
                        <button type="button" @click="openModal('fatFormatModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 6. SNF Format -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">SNF Format</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="snfFormat"></span>
                        <button type="button" @click="openModal('snfFormatModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 7. Computer Login -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Computer Login</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="computerLogin"></span>
                        <button type="button" @click="openModal('computerLoginModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 8. Center Logo -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Center Logo</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="centerLogoStatus"></span>
                        <button type="button" @click="openModal('centerLogoModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- 9. Annual Bonus Print Setting -->
                <div class="flex items-center justify-between py-2 border-b border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Annual Bonus Print Setting</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-slate-900" x-text="annualBonusPrintStatus"></span>
                        <button type="button" @click="openModal('annualBonusPrintModal')" class="text-slate-400 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition">
                            <i data-lucide="more-vertical" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

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

    <!-- ==================== SMS SETTINGS MODALS ==================== -->

    <!-- Modal: SMS Type (Matching Screenshot 1) -->
    <div x-show="modal === 'smsType'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>SMS Type</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Farmer App" x-model="selectedSmsTypes" class="rounded text-[#005c53]">
                    <span>Farmer App</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="SIM Card" x-model="selectedSmsTypes" class="rounded text-[#005c53]">
                    <span>SIM Card</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Server SMS Pack" x-model="selectedSmsTypes" class="rounded text-[#005c53]">
                    <span>Server SMS Pack</span>
                </label>

                <div class="pt-2 text-[11px] text-rose-500 font-semibold space-y-1">
                    <p>• If farmer app is installed on farmer mobile, free push message will be sent.</p>
                    <p>• If not installed then sms will be sent from Mobile SIM card.</p>
                    <p>• If mobile SIM card 100 SMS limit is exceeded, SMS will be sent from V4D Server SMS Pack.</p>
                </div>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSmsType()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Send SMS For (Matching Screenshot 4) -->
    <div x-show="modal === 'sendSmsFor'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Send SMS For</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-2.5 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Collection" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Collection</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Feed" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Feed</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Invoice Payment" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Invoice Payment</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Loan" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Loan</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Loan Installment" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Loan Installment</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Milk Sale" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Milk Sale</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Milk Receive" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Milk Receive</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="checkbox" value="Milk Dispatch" x-model="selectedSendSmsFor" class="rounded text-[#005c53]">
                    <span>Milk Dispatch</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSendSmsFor()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Farmer App Link in SMS (Matching Screenshot 2) -->
    <div x-show="modal === 'farmerAppLinkInSms'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Farmer App Link In SMS</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="app_link_radio" value="On" x-model="selectedFarmerAppLinkInSms" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="app_link_radio" value="Off" x-model="selectedFarmerAppLinkInSms" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    If 'On' Farmer application install link will be included in all SMS.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('farmer_app_link_in_sms', selectedFarmerAppLinkInSms, 'farmerAppLinkInSms')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Total in Collection SMS (Matching Screenshot 5) -->
    <div x-show="modal === 'totalInCollectionSms'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Total In Collection SMS</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="total_sms_radio" value="On" x-model="selectedTotalInCollectionSms" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="total_sms_radio" value="Off" x-model="selectedTotalInCollectionSms" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    If 'On' Milk collection SMS will included the total Liter and total Amount for invoice period.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('total_in_collection_sms', selectedTotalInCollectionSms, 'totalInCollectionSms')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Center Name in SMS (Matching Screenshot 3) -->
    <div x-show="modal === 'centerNameInSms'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Center Name In SMS</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <div>
                    <label class="block text-slate-700 font-semibold mb-1">Name <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="customCenterNameInSms" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs font-bold" placeholder="e.g. SHREE GOPAL DAIRY">
                </div>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('center_name_in_sms', customCenterNameInSms, 'centerNameInSms')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>


    <!-- ==================== INVOICE SETTINGS MODALS ==================== -->

    <!-- Modal: Payment Period (Matching Screenshot 6) -->
    <div x-show="modal === 'paymentPeriod'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Payment Period</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-2.5 text-xs max-h-72 overflow-y-auto">
                <template x-for="p in paymentPeriodOptions" :key="p">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="radio" name="pay_period_radio" :value="p" x-model="selectedPaymentPeriod" class="text-[#005c53]">
                        <span x-text="p"></span>
                    </label>
                </template>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('payment_period', selectedPaymentPeriod, 'paymentPeriod')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Payment Register Print Setting (Matching Screenshot 8) -->
    <div x-show="modal === 'paymentRegisterPrintSetting'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Payment Register Print Setting</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Signature Column</span>
                    <select x-model="paymentRegisterData.signature_column" class="w-32 px-2.5 py-1 border border-slate-200 rounded text-xs">
                        <option value="On">On</option>
                        <option value="Off">Off</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Zero Amount Column</span>
                    <select x-model="paymentRegisterData.zero_amount_column" class="w-32 px-2.5 py-1 border border-slate-200 rounded text-xs">
                        <option value="Off">Off</option>
                        <option value="On">On</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Farmer Name (English)</span>
                    <select x-model="paymentRegisterData.farmer_name_english" class="w-32 px-2.5 py-1 border border-slate-200 rounded text-xs">
                        <option value="Off">Off</option>
                        <option value="On">On</option>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="font-semibold text-slate-700">Print Format</span>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-1" x-model="paymentRegisterData.format" class="text-[#005c53]">
                            <span>Format-1</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-2" x-model="paymentRegisterData.format" class="text-[#005c53]">
                            <span>Format-2</span>
                        </label>
                    </div>
                </div>

                <!-- Preview Link -->
                <div class="text-right pt-2">
                    <button type="button" @click="showPaymentRegisterPreview = true" class="text-blue-600 hover:underline font-semibold text-xs">
                        Click here to view Print Image
                    </button>
                </div>

            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="savePaymentRegisterSetting()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Invoice Print Setting (Matching Screenshot 7) -->
    <div x-show="modal === 'invoicePrintSetting'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-2xl w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Invoice Print Setting</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-3.5 text-xs max-h-[80vh] overflow-y-auto">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left Column -->
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Print Language</span>
                            <select x-model="invoicePrintData.language" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="English">English</option>
                                <option value="Hindi">Hindi</option>
                                <option value="Marathi">Marathi</option>
                                <option value="Gujarati">Gujarati</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Per Paper</span>
                            <select x-model="invoicePrintData.per_paper" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="One Bill">One Bill</option>
                                <option value="Two Bill">Two Bill</option>
                                <option value="Four Bill">Four Bill</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">FAT SNF CLR Columns</span>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" x-model="invoicePrintData.fat" class="rounded text-[#005c53]">
                                    <span>FAT</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" x-model="invoicePrintData.snf" class="rounded text-[#005c53]">
                                    <span>SNF</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" x-model="invoicePrintData.clr" class="rounded text-[#005c53]">
                                    <span>CLR</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Food Details</span>
                            <select x-model="invoicePrintData.food_details" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Advance Details</span>
                            <select x-model="invoicePrintData.advance_details" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Average in invoice</span>
                            <select x-model="invoicePrintData.average_in_invoice" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Plant Name</span>
                            <input type="text" x-model="invoicePrintData.plant_name" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs" placeholder="e.g. Gopal Dairy">
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Separate Cow/Buffalo</span>
                            <select x-model="invoicePrintData.separate_cow_buff" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Number in language</span>
                            <select x-model="invoicePrintData.number_in_language" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Rate Column</span>
                            <select x-model="invoicePrintData.rate_column" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Note</span>
                            <input type="text" x-model="invoicePrintData.note" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs" placeholder="e.g. priye dudh utpadak bandu namaste">
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Printer</span>
                            <select x-model="invoicePrintData.printer" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="Laser">Laser</option>
                                <option value="Thermal">Thermal</option>
                                <option value="Dot Matrix">Dot Matrix</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Print Format</span>
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="radio" value="Format-1" x-model="invoicePrintData.format" class="text-[#005c53]">
                                    <span>Format-1</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="radio" value="Format-2" x-model="invoicePrintData.format" class="text-[#005c53]">
                                    <span>Format-2</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="radio" value="Format-3" x-model="invoicePrintData.format" class="text-[#005c53]">
                                    <span>Format-3</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveInvoicePrintSetting()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Payment Register Print Preview (Matching Screenshot 9) -->
    <div x-show="showPaymentRegisterPreview" x-cloak class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4" style="z-index: 99999;">
        <div @click.away="showPaymentRegisterPreview = false" class="bg-white rounded-lg shadow-2xl border border-slate-200 max-w-3xl w-full overflow-hidden">
            <div class="px-5 py-3 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Payment Register Print Preview</h4>
                <button type="button" @click="showPaymentRegisterPreview = false" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 bg-slate-100 flex justify-center max-h-[75vh] overflow-y-auto">
                
                <!-- Mock Payment Register Sheet -->
                <div class="bg-white border border-slate-300 p-6 rounded shadow-sm w-full font-sans text-xs text-slate-800">
                    <div class="text-center pb-3 border-b border-slate-200">
                        <h3 class="text-sm font-black text-slate-900 tracking-wide uppercase">Mobile Dairy Dudh Sankalan Kendra</h3>
                        <p class="text-[11px] text-slate-600">Sangamner, Ahmadnagar</p>
                    </div>

                    <div class="py-2.5 flex justify-between items-center text-[11px] font-bold text-slate-700">
                        <span>Date: 16 Feb 2022 To 20 Feb 2022</span>
                        <span class="text-slate-500 font-normal">Format-1</span>
                    </div>

                    <!-- Register Table -->
                    <table class="w-full border-collapse border border-slate-300 text-[10px] text-center">
                        <thead class="bg-slate-50 font-bold">
                            <tr>
                                <th class="border border-slate-300 px-1.5 py-1">Code</th>
                                <th class="border border-slate-300 px-2 py-1 text-left">Name</th>
                                <th class="border border-slate-300 px-1.5 py-1">Liter</th>
                                <th class="border border-slate-300 px-1.5 py-1">Rate</th>
                                <th class="border border-slate-300 px-1.5 py-1">Amount</th>
                                <th class="border border-slate-300 px-1.5 py-1">Transport</th>
                                <th class="border border-slate-300 px-1.5 py-1">Other Payment</th>
                                <th class="border border-slate-300 px-1.5 py-1">Advance</th>
                                <th class="border border-slate-300 px-1.5 py-1">Grant</th>
                                <th class="border border-slate-300 px-1.5 py-1">Feed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">156</td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium">विपिन</td>
                                <td class="border border-slate-300 px-1 py-1">477.10</td>
                                <td class="border border-slate-300 px-1 py-1">28.79</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">13735.16</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1">8026.50</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1">5160.00</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">157</td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium">अनिता त्रिपुटी</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">158</td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium">सर्वेश्र्वर</td>
                                <td class="border border-slate-300 px-1 py-1">742.10</td>
                                <td class="border border-slate-300 px-1 py-1">27.79</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">20622.27</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1">18268.88</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1">1580.00</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">159</td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium">मांगीलाल प्रभाकर</td>
                                <td class="border border-slate-300 px-1 py-1">239.10</td>
                                <td class="border border-slate-300 px-1 py-1">29.08</td>
                                <td class="border border-slate-300 px-1 py-1 font-bold">6954.16</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1">6679.22</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">161</td>
                                <td class="border border-slate-300 px-2 py-1 text-left font-medium">बलिराम साहेबराव</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-100 font-bold">
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">125</td>
                                <td class="border border-slate-300 px-2 py-1 text-right">Total</td>
                                <td class="border border-slate-300 px-1 py-1 font-black">22217.80</td>
                                <td class="border border-slate-300 px-1 py-1">28.57</td>
                                <td class="border border-slate-300 px-1 py-1 font-black">634709.76</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1 font-black">231868.48</td>
                                <td class="border border-slate-300 px-1 py-1"></td>
                                <td class="border border-slate-300 px-1 py-1 font-black">164502.49</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            <div class="px-5 py-2.5 bg-slate-100 border-t border-slate-200 flex justify-end">
                <button type="button" @click="showPaymentRegisterPreview = false" class="px-4 py-1.5 rounded bg-slate-300 hover:bg-slate-400 text-slate-800 text-xs font-bold">Close Preview</button>
            </div>
        </div>
    </div>

    <!-- ==================== RATE CHART SETTINGS MODALS ==================== -->

    <!-- Modal: Rate Chart (Matching Screenshot 2) -->
    <div x-show="modal === 'rateChartModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Rate Chart</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="rate_chart_status_radio" value="On" x-model="selectedRateChartStatus" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="rate_chart_status_radio" value="Off" x-model="selectedRateChartStatus" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Set 'Off' to stop rate chart temporarily.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('rate_chart_status', selectedRateChartStatus, 'rateChartModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Farmer Wise Rate (Matching Screenshot 3) -->
    <div x-show="modal === 'farmerWiseRateModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Farmer Wise Rate</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="farmer_wise_radio" value="As per Rate Chart" x-model="selectedFarmerWiseRate" class="text-[#005c53]">
                    <span>As per Rate Chart</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="farmer_wise_radio" value="Rate Wise" x-model="selectedFarmerWiseRate" class="text-[#005c53]">
                    <span>Rate Wise</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="farmer_wise_radio" value="Fat Wise" x-model="selectedFarmerWiseRate" class="text-[#005c53]">
                    <span>Fat Wise</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Farmers milk rate can be set more or less by 'Rate Wise' or 'FAT Wise'.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('farmer_wise_rate', selectedFarmerWiseRate, 'farmerWiseRateModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Shift Wise Rate (Matching Screenshot 4) -->
    <div x-show="modal === 'shiftWiseRateModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Shift Wise Rate</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3.5 text-xs">
                <div class="flex items-center justify-between gap-4">
                    <label class="font-semibold text-slate-700">Morning:</label>
                    <input type="number" step="0.01" x-model="selectedShiftWiseMorning" class="w-32 px-3 py-1.5 border border-slate-200 rounded text-right font-bold" placeholder="0.0">
                </div>

                <div class="flex items-center justify-between gap-4">
                    <label class="font-semibold text-slate-700">Evening:</label>
                    <input type="number" step="0.01" x-model="selectedShiftWiseEvening" class="w-32 px-3 py-1.5 border border-slate-200 rounded text-right font-bold" placeholder="0.0">
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-2 cursor-pointer text-slate-700 text-[11px]">
                        <input type="checkbox" x-model="shiftWiseEveningCondition" class="rounded text-[#005c53] mt-0.5">
                        <span>I want to apply evening bonus only when there is a morning collection for the farmer on same date</span>
                    </label>
                </div>

                <p class="text-[11px] text-rose-500 font-semibold">
                    The amount will be added or subtracted according to the selected shift
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveShiftWiseRate()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>


    <!-- ==================== FARMER APP SETTINGS MODALS ==================== -->

    <!-- Modal: Show Advance/Loan Interest Rate in Farmer App (Matching Screenshot 5) -->
    <div x-show="modal === 'showAdvanceInterestRateModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Show Advance/Loan Interest Rate In Farmer App</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="adv_interest_radio" value="On" x-model="selectedShowAdvanceInterestRate" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="adv_interest_radio" value="Off" x-model="selectedShowAdvanceInterestRate" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Set 'On' to display Advance/Loan interest rate in farmer application.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('show_advance_interest_rate_farmer_app', selectedShowAdvanceInterestRate, 'showAdvanceInterestRateModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Show Rate Chart in Farmer App -->
    <div x-show="modal === 'showRateChartModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Show Rate Chart In Farmer App</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="show_rc_radio" value="On" x-model="selectedShowRateChart" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="show_rc_radio" value="Off" x-model="selectedShowRateChart" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Set 'On' to show rate chart in farmer application.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('show_rate_chart_farmer_app', selectedShowRateChart, 'showRateChartModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Show Collection After Invoice Save (Matching Screenshot 6) -->
    <div x-show="modal === 'showCollectionAfterInvoiceSaveModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Show Collection After Invoice Save</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="show_after_inv_radio" value="On" x-model="selectedShowCollectionAfterInvoiceSave" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="show_after_inv_radio" value="Off" x-model="selectedShowCollectionAfterInvoiceSave" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Set 'On' to show the collection to farmer after saving the bill.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('show_collection_after_invoice_save', selectedShowCollectionAfterInvoiceSave, 'showCollectionAfterInvoiceSaveModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Show Collection After Shift Finish -->
    <div x-show="modal === 'showCollectionAfterShiftFinishModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Show Collection After Shift Finish</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="show_after_shift_radio" value="On" x-model="selectedShowCollectionAfterShiftFinish" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="show_after_shift_radio" value="Off" x-model="selectedShowCollectionAfterShiftFinish" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Set 'On' to show collection in farmer application only after the shift collection is finished.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('show_collection_after_shift_finish', selectedShowCollectionAfterShiftFinish, 'showCollectionAfterShiftFinishModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Hide Collection Rate (Matching Screenshot 7) -->
    <div x-show="modal === 'hideCollectionRateModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Hide Collection Rate</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="hide_rate_radio" value="On" x-model="selectedHideCollectionRate" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="hide_rate_radio" value="Off" x-model="selectedHideCollectionRate" class="text-[#005c53]">
                    <span>Off</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    Set 'On' to hide collection rate in farmer application.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('hide_collection_rate_farmer_app', selectedHideCollectionRate, 'hideCollectionRateModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- ==================== MILK SALE SETTINGS MODALS ==================== -->

    <!-- Modal: Billing Period (Milk Sale) -->
    <div x-show="modal === 'milkSaleBillingPeriodModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Billing Period</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-2.5 text-xs max-h-72 overflow-y-auto">
                <template x-for="p in milkSaleBillingPeriodOptions" :key="p">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                        <input type="radio" name="milk_sale_period_radio" :value="p" x-model="selectedMilkSaleBillingPeriod" class="text-[#005c53]">
                        <span x-text="p"></span>
                    </label>
                </template>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('milk_sale_billing_period', selectedMilkSaleBillingPeriod, 'milkSaleBillingPeriodModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Milk Sale Bill Print Setting -->
    <div x-show="modal === 'milkSalePrintModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Milk Sale Bill Print Setting</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Print Language</span>
                    <select x-model="milkSalePrintData.language" class="w-40 px-2.5 py-1 border border-slate-200 rounded text-xs">
                        <option value="English">English</option>
                        <option value="Hindi">Hindi</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Number in language</span>
                    <select x-model="milkSalePrintData.number_in_language" class="w-40 px-2.5 py-1 border border-slate-200 rounded text-xs">
                        <option value="On">On</option>
                        <option value="Off">Off</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Printer</span>
                    <select x-model="milkSalePrintData.printer" class="w-40 px-2.5 py-1 border border-slate-200 rounded text-xs">
                        <option value="Laser">Laser</option>
                        <option value="Thermal">Thermal</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-semibold text-slate-700">Print Format</span>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-1" x-model="milkSalePrintData.format" class="text-[#005c53]">
                            <span>Format-1</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" value="Format-2" x-model="milkSalePrintData.format" class="text-[#005c53]">
                            <span>Format-2</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Note</label>
                    <input type="text" x-model="milkSalePrintData.note" placeholder="Enter Note" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs">
                </div>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveMilkSalePrintSetting()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>


    <!-- ==================== OTHER SETTINGS MODALS ==================== -->

    <!-- Modal: Bank Details (Other Settings) -->
    <div x-show="modal === 'otherBankDetailsModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Bank Details</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="bank_det_radio" value="On" x-model="selectedOtherBankDetails" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="bank_det_radio" value="Off" x-model="selectedOtherBankDetails" class="text-[#005c53]">
                    <span>Off</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('other_bank_details', selectedOtherBankDetails, 'otherBankDetailsModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Weighing Scale Format (Other Settings) -->
    <div x-show="modal === 'weighingScaleModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Weighing Scale Format</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="ws_format_radio" value="As per Weighing Scale" x-model="selectedWeighingScaleFormat" class="text-[#005c53]">
                    <span>As per Weighing Scale</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="ws_format_radio" value="Zero digit (00)" x-model="selectedWeighingScaleFormat" class="text-[#005c53]">
                    <span>Zero digit (00)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="ws_format_radio" value="One digit (00.0)" x-model="selectedWeighingScaleFormat" class="text-[#005c53]">
                    <span>One digit (00.0)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="ws_format_radio" value="Two digit (00.00)" x-model="selectedWeighingScaleFormat" class="text-[#005c53]">
                    <span>Two digit (00.00)</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    This option is applicable only if Auto/Online input from weighing scale is in use
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('weighing_scale_format', selectedWeighingScaleFormat, 'weighingScaleModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: CLR/Lacto Format (Other Settings) -->
    <div x-show="modal === 'clrLactoModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>CLR/Lacto Format</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="clr_lacto_radio" value="As per analyser" x-model="selectedClrLactoFormat" class="text-[#005c53]">
                    <span>As per analyser</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="clr_lacto_radio" value="One digit(00)" x-model="selectedClrLactoFormat" class="text-[#005c53]">
                    <span>One digit(00)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="clr_lacto_radio" value="Step (00.5)" x-model="selectedClrLactoFormat" class="text-[#005c53]">
                    <span>Step (00.5)</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    This option is applicable only if Auto/Online input from Milk Analyzer is in use
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('clr_lacto_format', selectedClrLactoFormat, 'clrLactoModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Contact Details (Other Settings) -->
    <div x-show="modal === 'contactDetailsModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Contact Details</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="contactDetailsData.name" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs" placeholder="Owner Name">
                    <p class="text-[10px] text-rose-500 mt-0.5">this field should not be empty.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Phone Number <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="contactDetailsData.phone" class="w-full px-3 py-1.5 border border-slate-200 rounded text-xs" placeholder="Mobile Number">
                    <p class="text-[10px] text-rose-500 mt-0.5">this field should not be empty.</p>
                </div>

                <p class="text-[11px] text-rose-500 leading-tight">
                    Contact information to be printed on invoice print. If left blank, center owner name and mobile number will be printed.
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveContactDetails()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: FAT Format (Other Settings) -->
    <div x-show="modal === 'fatFormatModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>FAT Format</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="fat_format_radio" value="Round Down" x-model="selectedFatFormat" class="text-[#005c53]">
                    <span>Round Down</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="fat_format_radio" value="Round Up" x-model="selectedFatFormat" class="text-[#005c53]">
                    <span>Round Up</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="fat_format_radio" value="Round" x-model="selectedFatFormat" class="text-[#005c53]">
                    <span>Round</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    This option is applicable only if Auto/Online input from Milk Analyzer is in use
                </p>
                <div class="text-[10px] text-slate-500 leading-tight">
                    <p>Examples:</p>
                    <p>Round Up: 5.51 -> 5.6</p>
                    <p>Round Down: 5.59 -> 5.5</p>
                    <p>Round: 5.51 -> 5.5, 5.55 -> 5.6</p>
                </div>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('fat_format', selectedFatFormat, 'fatFormatModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: SNF Format (Other Settings) -->
    <div x-show="modal === 'snfFormatModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>SNF Format</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="snf_format_radio" value="Round Down" x-model="selectedSnfFormat" class="text-[#005c53]">
                    <span>Round Down</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="snf_format_radio" value="Round Up" x-model="selectedSnfFormat" class="text-[#005c53]">
                    <span>Round Up</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="snf_format_radio" value="Round" x-model="selectedSnfFormat" class="text-[#005c53]">
                    <span>Round</span>
                </label>
                <p class="text-[11px] text-rose-500 font-semibold pt-1">
                    This option is applicable only if Auto/Online input from Milk Analyzer is in use
                </p>
                <div class="text-[10px] text-slate-500 leading-tight">
                    <p>Examples:</p>
                    <p>Round Up: 5.51 -> 5.6</p>
                    <p>Round Down: 5.59 -> 5.5</p>
                    <p>Round: 5.51 -> 5.5, 5.55 -> 5.6</p>
                </div>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('snf_format', selectedSnfFormat, 'snfFormatModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Computer Login (Other Settings) -->
    <div x-show="modal === 'computerLoginModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Computer Login</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="comp_login_radio" value="On" x-model="selectedComputerLogin" class="text-[#005c53]">
                    <span>On</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700">
                    <input type="radio" name="comp_login_radio" value="Off" x-model="selectedComputerLogin" class="text-[#005c53]">
                    <span>Off</span>
                </label>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveSetting('computer_login', selectedComputerLogin, 'computerLoginModal')" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Center Logo (Other Settings) -->
    <div x-show="modal === 'centerLogoModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-sm w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Center Logo</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4 text-xs text-center">
                <div class="w-28 h-28 mx-auto border-2 border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center bg-slate-50 text-slate-400 cursor-pointer hover:border-[#005c53]">
                    <span class="text-3xl font-light text-slate-400">+</span>
                </div>
                <p class="text-[11px] text-rose-500 font-semibold">
                    This logo will be printed on bill print
                </p>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="modal = null" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
            </div>
        </div>
    </div>

    <!-- Modal: Annual Bonus Print Setting (Other Settings) -->
    <div x-show="modal === 'annualBonusPrintModal'" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div @click.away="modal = null" class="bg-white rounded-lg shadow-xl border border-slate-200 max-w-2xl w-full overflow-hidden">
            <div class="px-5 py-3.5 bg-[#52b79a] text-white flex items-center justify-between font-bold text-sm">
                <h4>Annual Bonus Print Setting</h4>
                <button type="button" @click="modal = null" class="text-white hover:text-slate-200 text-lg leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-3.5 text-xs max-h-[75vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Print Language</span>
                            <select x-model="annualBonusData.language" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="English">English</option>
                                <option value="Hindi">Hindi</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Per Paper</span>
                            <select x-model="annualBonusData.per_paper" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="One Bill">One Bill</option>
                                <option value="Two Bill">Two Bill</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Advance Details</span>
                            <select x-model="annualBonusData.advance_details" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Previous Date</span>
                            <select x-model="annualBonusData.previous_date" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Previous Upcoming Balance</span>
                            <select x-model="annualBonusData.previous_upcoming_balance" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Amount Column</span>
                            <select x-model="annualBonusData.amount_column" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Return Details</span>
                            <select x-model="annualBonusData.return_details" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="On">On</option>
                                <option value="Off">Off</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Details Order By</span>
                            <select x-model="annualBonusData.order_by" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs">
                                <option value="Order By Date - Descending">Order By Date - Descending</option>
                                <option value="Order By Date - Ascending">Order By Date - Ascending</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 items-center gap-2">
                            <span class="font-semibold text-slate-700">Bill Note</span>
                            <input type="text" x-model="annualBonusData.bill_note" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs" placeholder="Enter Greeting">
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end gap-2 text-xs font-bold">
                <button type="button" @click="modal = null" class="px-4 py-1.5 rounded bg-slate-200 hover:bg-slate-300 text-slate-700">Cancel</button>
                <button type="button" @click="saveAnnualBonusSetting()" class="px-5 py-1.5 rounded bg-[#005c53] hover:bg-[#004740] text-white">Save</button>
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

        // SMS Settings Values
        smsType: {!! json_encode($settings["sms_type"] ?? "Farmer App , SIM Card , Server SMS Pack") !!},
        sendSmsFor: {!! json_encode($settings["send_sms_for"] ?? "Collection , Feed , Invoice Payment , Loan , Loan Installment , Milk Sale , Milk Receive , Milk Dispatch") !!},
        farmerAppLinkInSms: {!! json_encode($settings["farmer_app_link_in_sms"] ?? "On") !!},
        totalInCollectionSms: {!! json_encode($settings["total_in_collection_sms"] ?? "On") !!},
        centerNameInSms: {!! json_encode($settings["center_name_in_sms"] ?? "SHRE GOPAL DAIR") !!},
        customCenterNameInSms: {!! json_encode($settings["center_name_in_sms"] ?? "SHRE GOPAL DAIR") !!},

        selectedSmsTypes: ({!! json_encode($settings["sms_type"] ?? "Farmer App , SIM Card , Server SMS Pack") !!}).split(',').map(s => s.trim()),
        selectedSendSmsFor: ({!! json_encode($settings["send_sms_for"] ?? "Collection , Feed , Invoice Payment , Loan , Loan Installment , Milk Sale , Milk Receive , Milk Dispatch") !!}).split(',').map(s => s.trim()),
        selectedFarmerAppLinkInSms: {!! json_encode($settings["farmer_app_link_in_sms"] ?? "On") !!},
        selectedTotalInCollectionSms: {!! json_encode($settings["total_in_collection_sms"] ?? "On") !!},

        // Invoice Settings Values
        paymentPeriod: {!! json_encode($settings["payment_period"] ?? "01-10, 11-20, 21-ME") !!},
        selectedPaymentPeriod: {!! json_encode($settings["payment_period"] ?? "01-10, 11-20, 21-ME") !!},
        paymentPeriodOptions: [
            '01-10, 11-20, 21-ME',
            '01-15, 16-ME',
            '01-ME',
            '01-05, 06-10, 11-15, 16-20, 21-25, 26-ME',
            '05-15, 16-25, 26-05',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ],

        paymentRegisterFormat: {!! json_encode($paymentRegisterSetting["format"] ?? "Format-1") !!},
        showPaymentRegisterPreview: false,
        paymentRegisterData: {
            signature_column: '{{ $paymentRegisterSetting["signature_column"] ?? "On" }}',
            zero_amount_column: '{{ $paymentRegisterSetting["zero_amount_column"] ?? "Off" }}',
            farmer_name_english: '{{ $paymentRegisterSetting["farmer_name_english"] ?? "Off" }}',
            format: '{{ $paymentRegisterSetting["format"] ?? "Format-1" }}'
        },

        invoicePrintFormat: {!! json_encode($invoicePrintSetting["printer"] ?? "Laser") !!} + ' ' + {!! json_encode($invoicePrintSetting["format"] ?? "Format-1") !!},
        invoicePrintData: {
            language: '{{ $invoicePrintSetting["language"] ?? "English" }}',
            per_paper: '{{ $invoicePrintSetting["per_paper"] ?? "One Bill" }}',
            fat: {{ isset($invoicePrintSetting['fat']) ? ($invoicePrintSetting['fat'] ? 'true' : 'false') : 'true' }},
            snf: {{ isset($invoicePrintSetting['snf']) ? ($invoicePrintSetting['snf'] ? 'true' : 'false') : 'true' }},
            clr: {{ isset($invoicePrintSetting['clr']) ? ($invoicePrintSetting['clr'] ? 'true' : 'false') : 'true' }},
            food_details: '{{ $invoicePrintSetting["food_details"] ?? "On" }}',
            advance_details: '{{ $invoicePrintSetting["advance_details"] ?? "On" }}',
            average_in_invoice: '{{ $invoicePrintSetting["average_in_invoice"] ?? "On" }}',
            plant_name: '{{ $invoicePrintSetting["plant_name"] ?? "Gopal Dairy" }}',
            separate_cow_buff: '{{ $invoicePrintSetting["separate_cow_buff"] ?? "On" }}',
            number_in_language: '{{ $invoicePrintSetting["number_in_language"] ?? "On" }}',
            printer: '{{ $invoicePrintSetting["printer"] ?? "Laser" }}',
            format: '{{ $invoicePrintSetting["format"] ?? "Format-1" }}'
        },

        // Rate Chart Settings Values
        rateChartStatus: {!! json_encode($settings["rate_chart_status"] ?? "On") !!},
        selectedRateChartStatus: {!! json_encode($settings["rate_chart_status"] ?? "On") !!},
        shiftWiseMorning: {!! json_encode($settings["shift_wise_morning"] ?? "0.00") !!},
        shiftWiseEvening: {!! json_encode($settings["shift_wise_evening"] ?? "0.00") !!},
        selectedShiftWiseMorning: {!! json_encode($settings["shift_wise_morning"] ?? "0.00") !!},
        selectedShiftWiseEvening: {!! json_encode($settings["shift_wise_evening"] ?? "0.00") !!},
        shiftWiseEveningCondition: {{ isset($settings['shift_wise_evening_condition']) && $settings['shift_wise_evening_condition'] == '1' ? 'true' : 'true' }},
        farmerWiseRate: {!! json_encode($settings["farmer_wise_rate"] ?? "As per Rate Chart") !!},
        selectedFarmerWiseRate: {!! json_encode($settings["farmer_wise_rate"] ?? "As per Rate Chart") !!},

        // Farmer App Settings Values
        showAdvanceInterestRate: {!! json_encode($settings["show_advance_interest_rate_farmer_app"] ?? "Off") !!},
        selectedShowAdvanceInterestRate: {!! json_encode($settings["show_advance_interest_rate_farmer_app"] ?? "Off") !!},
        showRateChart: {!! json_encode($settings["show_rate_chart_farmer_app"] ?? "Off") !!},
        selectedShowRateChart: {!! json_encode($settings["show_rate_chart_farmer_app"] ?? "Off") !!},
        showCollectionAfterInvoiceSave: {!! json_encode($settings["show_collection_after_invoice_save"] ?? "Off") !!},
        selectedShowCollectionAfterInvoiceSave: {!! json_encode($settings["show_collection_after_invoice_save"] ?? "Off") !!},
        showCollectionAfterShiftFinish: {!! json_encode($settings["show_collection_after_shift_finish"] ?? "Off") !!},
        selectedShowCollectionAfterShiftFinish: {!! json_encode($settings["show_collection_after_shift_finish"] ?? "Off") !!},
        hideCollectionRate: {!! json_encode($settings["hide_collection_rate_farmer_app"] ?? "On") !!},
        selectedHideCollectionRate: {!! json_encode($settings["hide_collection_rate_farmer_app"] ?? "On") !!},

        // Milk Sale Settings Values
        milkSaleBillingPeriod: {!! json_encode($settings["milk_sale_billing_period"] ?? "01-10, 11-20, 21-ME") !!},
        selectedMilkSaleBillingPeriod: {!! json_encode($settings["milk_sale_billing_period"] ?? "01-10, 11-20, 21-ME") !!},
        milkSaleBillingPeriodOptions: [
            '01-10, 11-20, 21-ME',
            '01-15, 16-ME',
            '01-ME',
            '01-05, 06-10, 11-15, 16-20, 21-25, 26-ME',
            '05-15, 16-25, 26-05',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ],
        milkSalePrintFormat: {!! json_encode($settings["milk_sale_print_format"] ?? "Not Available") !!},
        milkSalePrintData: {
            language: '{{ $settings["milk_sale_print_language"] ?? "English" }}',
            number_in_language: '{{ $settings["milk_sale_print_number_in_lang"] ?? "On" }}',
            printer: '{{ $settings["milk_sale_print_printer"] ?? "Laser" }}',
            format: '{{ $settings["milk_sale_print_format_type"] ?? "Format-1" }}',
            note: '{{ $settings["milk_sale_print_note"] ?? "" }}'
        },

        // Other Settings Values
        otherBankDetails: {!! json_encode($settings["other_bank_details"] ?? "On") !!},
        selectedOtherBankDetails: {!! json_encode($settings["other_bank_details"] ?? "On") !!},
        weighingScaleFormat: {!! json_encode($settings["weighing_scale_format"] ?? "One digit (00.0)") !!},
        selectedWeighingScaleFormat: {!! json_encode($settings["weighing_scale_format"] ?? "One digit (00.0)") !!},
        clrLactoFormat: {!! json_encode($settings["clr_lacto_format"] ?? "One digit(00)") !!},
        selectedClrLactoFormat: {!! json_encode($settings["clr_lacto_format"] ?? "One digit(00)") !!},
        contactDetailsSummary: {!! json_encode($settings["contact_details_summary"] ?? "Owner Name: Mobile") !!},
        contactDetailsData: {
            name: '{{ $settings["contact_details_name"] ?? ($center->operator->name ?? "Owner Name") }}',
            phone: '{{ $settings["contact_details_phone"] ?? ($center->operator->phone ?? "9876543210") }}'
        },
        fatFormat: {!! json_encode($settings["fat_format"] ?? "Round Down") !!},
        selectedFatFormat: {!! json_encode($settings["fat_format"] ?? "Round Down") !!},
        snfFormat: {!! json_encode($settings["snf_format"] ?? "Round Down") !!},
        selectedSnfFormat: {!! json_encode($settings["snf_format"] ?? "Round Down") !!},
        computerLogin: {!! json_encode($settings["computer_login"] ?? "On") !!},
        selectedComputerLogin: {!! json_encode($settings["computer_login"] ?? "On") !!},
        centerLogoStatus: {!! json_encode($settings["center_logo_status"] ?? "Center Logo") !!},
        annualBonusPrintStatus: {!! json_encode($settings["annual_bonus_print_status"] ?? "Not Available") !!},
        annualBonusData: {
            language: '{{ $settings["annual_bonus_language"] ?? "English" }}',
            per_paper: '{{ $settings["annual_bonus_per_paper"] ?? "One Bill" }}',
            advance_details: '{{ $settings["annual_bonus_adv_details"] ?? "On" }}',
            previous_date: '{{ $settings["annual_bonus_prev_date"] ?? "On" }}',
            previous_upcoming_balance: '{{ $settings["annual_bonus_prev_upcoming"] ?? "On" }}',
            amount_column: '{{ $settings["annual_bonus_amt_col"] ?? "On" }}',
            return_details: '{{ $settings["annual_bonus_return_det"] ?? "On" }}',
            order_by: '{{ $settings["annual_bonus_order_by"] ?? "Order By Date - Descending" }}',
            bill_note: '{{ $settings["annual_bonus_note"] ?? "" }}'
        },

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

        async postData(payload) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const response = await fetch('{{ route("settings.center-information.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });

            const text = await response.text();
            let res;
            try {
                res = JSON.parse(text);
            } catch (e) {
                console.error("Non-JSON response:", text);
                throw new Error("Server returned invalid response format");
            }

            if (!response.ok || !res.success) {
                throw new Error(res.message || ('Server error (' + response.status + ')'));
            }
            return res;
        },

        async saveSetting(key, val, modalToClose) {
            try {
                await this.postData({
                    setting_key: key,
                    setting_value: val
                });

                if (key === 'collection_type') this.collectionType = val;
                if (key === 'milk_type') this.milkType = val;
                if (key === 'collection_shift') this.collectionShift = val;
                if (key === 'show_previous_collection') this.showPreviousCollection = val;
                if (key === 'collection_input') this.collectionInput = val;
                if (key === 'offline_collection') this.offlineCollection = val;
                if (key === 'farmer_app_link_in_sms') this.farmerAppLinkInSms = val;
                if (key === 'total_in_collection_sms') this.totalInCollectionSms = val;
                if (key === 'center_name_in_sms') this.centerNameInSms = val;
                if (key === 'payment_period') this.paymentPeriod = val;
                if (key === 'rate_chart_status') this.rateChartStatus = val;
                if (key === 'farmer_wise_rate') this.farmerWiseRate = val;
                if (key === 'show_advance_interest_rate_farmer_app') this.showAdvanceInterestRate = val;
                if (key === 'show_rate_chart_farmer_app') this.showRateChart = val;
                if (key === 'show_collection_after_invoice_save') this.showCollectionAfterInvoiceSave = val;
                if (key === 'show_collection_after_shift_finish') this.showCollectionAfterShiftFinish = val;
                if (key === 'hide_collection_rate_farmer_app') this.hideCollectionRate = val;
                if (key === 'milk_sale_billing_period') this.milkSaleBillingPeriod = val;
                if (key === 'other_bank_details') this.otherBankDetails = val;
                if (key === 'weighing_scale_format') this.weighingScaleFormat = val;
                if (key === 'clr_lacto_format') this.clrLactoFormat = val;
                if (key === 'fat_format') this.fatFormat = val;
                if (key === 'snf_format') this.snfFormat = val;
                if (key === 'computer_login') this.computerLogin = val;
                if (key === 'center_logo') this.centerLogo = val;

                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving setting: ' + err.message);
            }
        },

        async saveShiftTime(shift) {
            let val = '';
            let key = '';
            if (shift === 'morning') {
                val = `${this.morningStartHour}:${this.morningStartMin} ${this.morningStartAmPm} - ${this.morningEndHour}:${this.morningEndMin} ${this.morningEndAmPm}`;
                key = 'morning_shift_time';
            } else {
                val = `${this.eveningStartHour}:${this.eveningStartMin} ${this.eveningStartAmPm} - ${this.eveningEndHour}:${this.eveningEndMin} ${this.eveningEndAmPm}`;
                key = 'evening_shift_time';
            }
            try {
                await this.postData({ setting_key: key, setting_value: val });
                if (shift === 'morning') this.morningShiftTime = val;
                else this.eveningShiftTime = val;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving shift time: ' + err.message);
            }
        },

        async savePrintSettings() {
            try {
                await this.postData({
                    setting_key: 'collection_print_settings',
                    ...this.printSettings
                });
                this.printFormat = this.printSettings.format;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving print settings: ' + err.message);
            }
        },

        async saveBonusPenalty() {
            try {
                await this.postData({
                    setting_key: 'bonus_penalty_settings',
                    ...this.bonusPenaltyData
                });
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving bonus/penalty: ' + err.message);
            }
        },

        async saveSmsType() {
            const val = this.selectedSmsTypes.join(' , ');
            try {
                await this.postData({ setting_key: 'sms_type', setting_value: val });
                this.smsType = val;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving SMS type: ' + err.message);
            }
        },

        async saveSendSmsFor() {
            const val = this.selectedSendSmsFor.join(' , ');
            try {
                await this.postData({ setting_key: 'send_sms_for', setting_value: val });
                this.sendSmsFor = val;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving Send SMS For: ' + err.message);
            }
        },

        async savePaymentRegisterSetting() {
            try {
                await this.postData({
                    setting_key: 'payment_register_print_settings',
                    ...this.paymentRegisterData
                });
                this.paymentRegisterFormat = this.paymentRegisterData.format;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving payment register settings: ' + err.message);
            }
        },

        async saveInvoicePrintSetting() {
            try {
                await this.postData({
                    setting_key: 'invoice_print_settings',
                    ...this.invoicePrintData
                });
                this.invoicePrintFormat = (this.invoicePrintData.printer || 'Laser') + ' ' + (this.invoicePrintData.format || 'Format-1');
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving invoice print settings: ' + err.message);
            }
        },

        async saveShiftWiseRate() {
            try {
                await this.postData({
                    shift_wise_morning: this.selectedShiftWiseMorning,
                    shift_wise_evening: this.selectedShiftWiseEvening,
                    shift_wise_evening_condition: this.shiftWiseEveningCondition ? '1' : '0'
                });
                this.shiftWiseMorning = this.selectedShiftWiseMorning;
                this.shiftWiseEvening = this.selectedShiftWiseEvening;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving shift wise rate: ' + err.message);
            }
        },

        async saveMilkSalePrintSetting() {
            try {
                await this.postData({
                    milk_sale_print_language: this.milkSalePrintData.language,
                    milk_sale_print_number_in_lang: this.milkSalePrintData.number_in_language,
                    milk_sale_print_printer: this.milkSalePrintData.printer,
                    milk_sale_print_format_type: this.milkSalePrintData.format,
                    milk_sale_print_note: this.milkSalePrintData.note,
                    milk_sale_print_format: (this.milkSalePrintData.printer || 'Laser') + ' ' + (this.milkSalePrintData.format || 'Format-1')
                });
                this.milkSalePrintFormat = (this.milkSalePrintData.printer || 'Laser') + ' ' + (this.milkSalePrintData.format || 'Format-1');
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving milk sale print settings: ' + err.message);
            }
        },

        async saveContactDetails() {
            if (!this.contactDetailsData.name || !this.contactDetailsData.phone) {
                alert('Name and Mobile Number cannot be empty');
                return;
            }
            try {
                const summary = this.contactDetailsData.name + ': ' + this.contactDetailsData.phone;
                await this.postData({
                    contact_details_name: this.contactDetailsData.name,
                    contact_details_phone: this.contactDetailsData.phone,
                    contact_details_summary: summary
                });
                this.contactDetailsSummary = summary;
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving contact details: ' + err.message);
            }
        },

        async saveAnnualBonusSetting() {
            try {
                await this.postData({
                    annual_bonus_language: this.annualBonusData.language,
                    annual_bonus_per_paper: this.annualBonusData.per_paper,
                    annual_bonus_adv_details: this.annualBonusData.advance_details,
                    annual_bonus_prev_date: this.annualBonusData.previous_date,
                    annual_bonus_prev_upcoming: this.annualBonusData.previous_upcoming_balance,
                    annual_bonus_amt_col: this.annualBonusData.amount_column,
                    annual_bonus_return_det: this.annualBonusData.return_details,
                    annual_bonus_order_by: this.annualBonusData.order_by,
                    annual_bonus_note: this.annualBonusData.bill_note,
                    annual_bonus_print_status: 'Configured'
                });
                this.annualBonusPrintStatus = 'Configured';
                this.modal = null;
            } catch (err) {
                console.error(err);
                alert('Error saving annual bonus print settings: ' + err.message);
            }
        }
    }
}
</script>
@endpush
@endsection
