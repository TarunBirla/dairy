@extends('layouts.app')

@section('title', 'Register Farmer / नया किसान पंजीकरण')
@section('breadcrumb', 'Register Farmer')
@section('header_title', 'Farmers / New Registration')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
    <div class="border-b border-slate-100 pb-4 mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Farmer Registration / किसान पंजीकरण</h3>
                <p class="text-xs text-slate-500">Capture supplier code, dual language name, vehicle, route, rates (RO/RF), banking, and anamat deposit.</p>
            </div>
        </div>
        <a href="{{ route('farmers.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium">Cancel</a>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('farmers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- SECTION 1: PERSONAL DETAILS / व्यक्तिगत जानकारी -->
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-3.5">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="user" class="w-4 h-4 text-emerald-600"></i>
                <span>Personal Details / व्यक्तिगत जानकारी</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Farmer Code -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Farmer Code / किसान कोड <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="farmer_code" value="{{ old('farmer_code', $nextCode) }}" required placeholder="e.g. 46, 58, FMR0023"
                           class="w-full px-3 py-2 text-xs font-mono font-bold bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    <span class="text-[10px] text-slate-400">Custom code or suggested number</span>
                </div>

                <!-- Farmer Image Upload -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Farmer Photo / किसान फोटो
                    </label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 bg-white border border-slate-200 rounded-xl">
                </div>

                <!-- Full Name (English) -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Name (English) / नाम (अंग्रेजी) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. SANJAY NIGWAL"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- नाम (हिंदी) -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        नाम (हिंदी) / Hindi Name
                    </label>
                    <input type="text" name="name_hi" value="{{ old('name_hi') }}" placeholder="उदा. संजय निगवाल"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- Mobile Phone -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Mobile Number / मोबाइल नंबर
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="e.g. 9753441810"
                           class="w-full px-3 py-2 text-xs font-mono bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- Village / Tehsil -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Village / गाँव
                    </label>
                    <input type="text" name="village" value="{{ old('village') }}" placeholder="e.g. Bediya / Khargone"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- Vehicle -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Vehicle / वाहन
                    </label>
                    <select name="vehicle" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">Select Vehicle</option>
                        <option value="Van" {{ old('vehicle') === 'Van' ? 'selected' : '' }}>Van</option>
                        <option value="Car" {{ old('vehicle') === 'Car' ? 'selected' : '' }}>Car</option>
                        <option value="Tanker" {{ old('vehicle') === 'Tanker' ? 'selected' : '' }}>Tanker</option>
                        <option value="Bike" {{ old('vehicle') === 'Bike' ? 'selected' : '' }}>Bike</option>
                        <option value="Tractor" {{ old('vehicle') === 'Tractor' ? 'selected' : '' }}>Tractor</option>
                        <option value="Auto" {{ old('vehicle') === 'Auto' ? 'selected' : '' }}>Auto / Loader</option>
                        <option value="Other" {{ old('vehicle') === 'Other' ? 'selected' : '' }}>Other / अन्य</option>
                    </select>
                </div>

                <!-- Assign Route -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Assign Route / मार्ग असाइन करें
                    </label>
                    <select name="route_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">Select Route</option>
                        @foreach($routes as $rt)
                            <option value="{{ $rt->id }}" {{ old('route_id') == $rt->id ? 'selected' : '' }}>
                                {{ $rt->name }} ({{ $rt->code ?? 'RT-'.$rt->id }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 2: MILK DETAILS & RATES / दूध एवं दर विवरण -->
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-3.5" x-data="{ pricingType: '{{ old('pricing_type', 'rate_chart') }}' }">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="milk" class="w-4 h-4 text-emerald-600"></i>
                <span>Milk Details & Pricing (Rate Chart / Fixed Rate) / दूध एवं दर विवरण</span>
            </div>

            <!-- Pricing Type Selector -->
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                <label class="block text-[11px] font-bold text-slate-800 mb-2">
                    Pricing Type / दर निर्धारण का प्रकार <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition"
                           :class="pricingType === 'rate_chart' ? 'bg-emerald-50/90 border-emerald-500 text-emerald-950 font-bold shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100/70'">
                        <input type="radio" name="pricing_type" value="rate_chart" x-model="pricingType" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <div>
                            <span class="text-xs block font-bold text-slate-900">📊 Rate Chart (दर चार्ट)</span>
                            <span class="text-[10px] text-slate-500 font-normal">FAT / SNF Quality Formula Chart</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition"
                           :class="pricingType === 'fixed_rate' ? 'bg-emerald-50/90 border-emerald-500 text-emerald-950 font-bold shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100/70'">
                        <input type="radio" name="pricing_type" value="fixed_rate" x-model="pricingType" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <div>
                            <span class="text-xs block font-bold text-slate-900">💵 Fixed Rate (निश्चित दर)</span>
                            <span class="text-[10px] text-slate-500 font-normal">Direct Per-Liter Rate for Cow / Buffalo</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
                <!-- Animal Classification -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Milk Type / दूध का प्रकार <span class="text-rose-500">*</span>
                    </label>
                    <select name="animal_type" required class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="cow" {{ old('animal_type') === 'cow' ? 'selected' : '' }}>Cow Milk (गाय का दूध)</option>
                        <option value="buffalo" {{ old('animal_type') === 'buffalo' ? 'selected' : '' }}>Buffalo Milk (भैंस का दूध)</option>
                        <option value="mixed" {{ old('animal_type') === 'mixed' ? 'selected' : '' }}>Mixed Milk (मिश्रित दूध)</option>
                    </select>
                </div>

                <!-- If Rate Chart Selected: Active Rate Charts Dropdown -->
                <div class="sm:col-span-2" x-show="pricingType === 'rate_chart'">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Select Rate Chart / दर चार्ट चुनें
                    </label>
                    <select name="rate_chart_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-semibold">
                        <option value="">-- Standard Quality Chart (मानक दर चार्ट) --</option>
                        @foreach($rateCharts as $rc)
                            @if($rc->status === 'active')
                                <option value="{{ $rc->id }}" {{ old('rate_chart_id') == $rc->id ? 'selected' : '' }}>
                                    {{ $rc->name }} ({{ ucfirst($rc->milk_type) }} • ₹{{ number_format((float)$rc->fixed_rate, 2) }}/L)
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Only active rate charts are available for assignment.</span>
                </div>

                <!-- If Fixed Rate Selected: Cow & Buffalo Milk Rates -->
                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="pricingType === 'fixed_rate'">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                            Cow Milk Rate (₹/L) / गाय दर <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="cow_milk_rate" value="{{ old('cow_milk_rate') }}" placeholder="e.g. 60.00"
                               class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-semibold text-slate-900">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                            Buffalo Milk Rate (₹/L) / भैंस दर <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="buffalo_milk_rate" value="{{ old('buffalo_milk_rate') }}" placeholder="e.g. 70.00"
                               class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-semibold text-slate-900">
                    </div>
                </div>

                <!-- Collection Center -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Collection Center / संकलन केंद्र
                    </label>
                    <select name="collection_center_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">Main Center</option>
                        @foreach($centers as $c)
                            <option value="{{ $c->id }}" {{ old('collection_center_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 3: BANK DETAILS / बैंक विवरण -->
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-3.5">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="landmark" class="w-4 h-4 text-emerald-600"></i>
                <span>Bank Details / बैंक विवरण</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Branch Name -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Branch Name / शाखा
                    </label>
                    <input type="text" name="branch_name" value="{{ old('branch_name') }}" placeholder="e.g. Bediya Branch"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- Bank Name -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Bank Name / बैंक का नाम
                    </label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="e.g. State Bank of India"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Account Number / खाता संख्या
                    </label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="e.g. 30891283712"
                           class="w-full px-3 py-2 text-xs font-mono bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- Confirm Account Number -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Confirm Account Number / खाता संख्या पुष्टि
                    </label>
                    <input type="text" name="confirm_account_number" value="{{ old('confirm_account_number') }}" placeholder="Re-enter Account Number"
                           class="w-full px-3 py-2 text-xs font-mono bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- IFSC Code -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        IFSC Code / आईएफएससी कोड
                    </label>
                    <input type="text" name="ifsc_code" value="{{ old('ifsc_code') }}" placeholder="e.g. SBIN0001234"
                           class="w-full px-3 py-2 text-xs font-mono uppercase bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>

                <!-- UPI ID -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        UPI ID / यूपीआई आईडी
                    </label>
                    <input type="text" name="upi_id" value="{{ old('upi_id') }}" placeholder="e.g. 9826011111@upi"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                </div>
            </div>
        </div>

        <!-- SECTION 4: EXTRA INFORMATION (DYNAMIC ADDITIONS & DEDUCTIONS) / अतिरिक्त जानकारी -->
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                    <i data-lucide="sliders" class="w-4 h-4 text-emerald-600"></i>
                    <span>Extra Information / अतिरिक्त जानकारी (Dynamic Additions & Deductions)</span>
                </div>
                <a href="{{ route('settings.center-information') }}" target="_blank" class="text-[11px] text-emerald-700 hover:underline flex items-center gap-1 font-semibold">
                    <i data-lucide="settings" class="w-3 h-3"></i>
                    <span>Center Settings</span>
                </a>
            </div>

            <p class="text-xs text-slate-500">
                The fields below are dynamically generated based on categories configured as <strong>"Farmer Wise"</strong> in <strong>Settings → Center Information</strong>.
                Categories configured for <strong>"All Farmers"</strong> are managed centrally and applied automatically.
            </p>

            @php
                $hasFarmerWiseAdditions = !empty($farmerWiseAdditions);
                $hasFarmerWiseDeductions = !empty($farmerWiseDeductions);
            @endphp

            @if($hasFarmerWiseAdditions)
                <div class="space-y-2 pt-1">
                    <h4 class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Farmer-Wise Additions / किसान-वार जोड़ (Incentive, Subsidy, etc.)</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        @foreach($farmerWiseAdditions as $add)
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    {{ $add['name'] }} (₹ {{ $add['unit'] ? '/ ' . ltrim($add['unit'], '/') : '' }})
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" 
                                           name="extra_rates[{{ $add['name'] }}]" 
                                           value="{{ old('extra_rates.' . $add['name'], '0.00') }}" 
                                           placeholder="0.00"
                                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-semibold text-slate-900">
                                </div>
                                <span class="text-[10px] text-slate-400">Applied per {{ ltrim($add['unit'] ?? 'ltr', '/') }} for this farmer</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($hasFarmerWiseDeductions)
                <div class="space-y-2 pt-2 border-t border-slate-200/60">
                    <h4 class="text-[11px] font-bold text-rose-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="minus-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                        <span>Farmer-Wise Deductions / किसान-वार कटौती (Building Fund, Grant, etc.)</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                        @foreach($farmerWiseDeductions as $ded)
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                    {{ $ded['name'] }} (₹ {{ $ded['unit'] ? '/ ' . ltrim($ded['unit'], '/') : '' }})
                                </label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0" 
                                           name="extra_rates[{{ $ded['name'] }}]" 
                                           value="{{ old('extra_rates.' . $ded['name'], '0.00') }}" 
                                           placeholder="0.00"
                                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition font-semibold text-slate-900">
                                </div>
                                <span class="text-[10px] text-slate-400">Applied as {{ $ded['unit'] ?? 'fixed' }} deduction</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!$hasFarmerWiseAdditions && !$hasFarmerWiseDeductions)
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 shrink-0"></i>
                    <span>All Addition and Deduction types are currently configured as <strong>"All Farmers"</strong> under Center Information. No farmer-specific fields are required.</span>
                </div>
            @endif

            <!-- Central "All Farmers" Notice Banner for Transparency -->
            @if(!empty($allFarmersAdditions) || !empty($allFarmersDeductions))
                <div class="p-3 bg-slate-100/80 rounded-xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-slate-700">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Center-Wide Auto Rules (All Farmers):</span>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-0.5">
                        @foreach($allFarmersAdditions as $afa)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold">
                                + {{ $afa['name'] }}: ₹{{ $afa['amount'] }}{{ $afa['unit'] ?? '/ltr' }}
                            </span>
                        @endforeach
                        @foreach($allFarmersDeductions as $afd)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded text-[10px] font-bold">
                                - {{ $afd['name'] }}: ₹{{ $afd['amount'] }}{{ $afd['unit'] ?? '/ltr' }}
                            </span>
                        @endforeach
                    </div>
                    <span class="text-[10px] text-slate-400 block">These center-wide rates apply automatically to all milk collections and cannot be overridden individually.</span>
                </div>
            @endif

            <!-- INSTALLMENT DEDUCTION PREFERENCE (Matching Reference Images 1-4) -->
            <div class="pt-3 border-t border-slate-200/80">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-white rounded-xl border border-slate-200 shadow-2xs">
                    <div>
                        <span class="text-xs font-bold text-slate-800">Installment Deduction Preference / ऋण किश्त कटौती</span>
                        <p class="text-[11px] text-slate-400">Configure loan / advance repayment deduction on period settlements</p>
                    </div>

                    <!-- Image 4 Style: Installment : [ 1000.00 ₹  ✏️ ] -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-700">Installment :</span>
                        <div onclick="openExtraInfoModal()" 
                             class="inline-flex items-center gap-2.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-300 hover:border-teal-500 rounded-lg text-xs font-mono font-bold text-slate-900 shadow-2xs cursor-pointer transition"
                             title="Click to edit Installment">
                            <span id="installmentDisplay">
                                {{ old('installment_deduction_type', 'Auto') === 'Fixed' ? number_format(old('installment', 0), 2) . ' ₹' : old('installment_deduction_type', 'Auto') }}
                            </span>
                            <span class="text-sky-500 hover:text-sky-700 transition" title="Edit Installment">
                                <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="installment" id="form_installment" value="{{ old('installment', '0.00') }}">
                <input type="hidden" name="installment_deduction_type" id="form_deduction_type" value="{{ old('installment_deduction_type', 'Auto') }}">
            </div>
        </div>

        <!-- SECTION 5: ACCOUNT STATUS, BRANCH & ADDRESS / स्थिति एवं पता -->
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-3.5">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600"></i>
                <span>Account Status & Address / स्थिति एवं पता</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Status -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Account Status / स्थिति <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (निष्क्रिय)</option>
                        <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked (अवरुद्ध)</option>
                    </select>
                </div>

                <!-- Dairy Branch -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Dairy Branch / मुख्य शाखा
                    </label>
                    <select name="branch_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">All Dairy / Main Branch</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Anamat Deposit -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Anamat / अमानत (₹)
                    </label>
                    <input type="number" step="0.01" name="anamat" value="{{ old('anamat', '0.00') }}" placeholder="0.00"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    <span class="text-[10px] text-slate-400">Opening security deposit balance</span>
                </div>
            </div>

            <!-- Address -->
            <div class="pt-1">
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                    Full Postal Address / पूरा पता
                </label>
                <textarea name="address" rows="2" placeholder="Full residential village address"
                          class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">{{ old('address') }}</textarea>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-between border-t border-slate-100">
            <a href="{{ route('farmers.index') }}" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Register Farmer / किसान पंजीकृत करें</span>
            </button>
        </div>
    </form>
</div>

<!-- Extra Information Modal (Matching Images 1, 2, 3) -->
<div id="extraInfoModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 hidden" style="z-index: 99999 !important;">
    <div class="bg-white rounded-2xl max-w-sm w-full shadow-2xl border border-slate-200 overflow-hidden" onclick="event.stopPropagation()">
        <!-- Header: #98d8c8 mint/teal -->
        <div class="px-5 py-3.5 flex items-center justify-between" style="background-color: #98d8c8;">
            <h3 class="text-sm font-bold text-slate-800 tracking-wide">Extra Information</h3>
            <button type="button" onclick="closeExtraInfoModal()" class="text-slate-600 hover:text-slate-900 font-bold text-lg leading-none cursor-pointer">
                &times;
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 space-y-4 text-xs">
            <div class="flex items-center justify-between gap-4">
                <span class="font-bold text-slate-700">Name :</span>
                <span class="font-bold text-slate-900">Installment</span>
            </div>

            <div class="flex items-center justify-between gap-4">
                <label class="font-bold text-slate-700">Deduction Type :</label>
                <select id="modalDeductionType" onchange="toggleModalAmountField()" class="w-36 px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white font-medium text-slate-800 outline-none focus:ring-1 focus:ring-teal-500">
                    <option value="Auto">Auto</option>
                    <option value="DeductionOff">DeductionOff</option>
                    <option value="Fixed">Fixed</option>
                </select>
            </div>

            <div id="modalAmountRow" class="flex items-center justify-between gap-4">
                <label class="font-bold text-slate-700">Amount :</label>
                <input type="number" step="0.01" min="0" id="modalAmountInput" placeholder="0.00" class="w-36 px-2.5 py-1.5 border border-slate-300 rounded-lg bg-white font-mono font-bold text-slate-900 outline-none focus:ring-1 focus:ring-teal-500">
            </div>
        </div>

        <!-- Footer Buttons: No (grey) and Save (teal #388e7e) -->
        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button type="button" onclick="closeExtraInfoModal()" class="px-4 py-1.5 bg-slate-300 hover:bg-slate-400 text-slate-800 font-bold text-xs rounded-lg transition cursor-pointer">
                No
            </button>
            <button type="button" onclick="saveExtraInfoModal()" class="px-5 py-1.5 text-white font-bold text-xs rounded-lg shadow-xs transition cursor-pointer" style="background-color: #388e7e;">
                Save
            </button>
        </div>
    </div>
</div>

<script>
    window.openExtraInfoModal = function() {
        const type = document.getElementById('form_deduction_type').value || 'Auto';
        const amount = document.getElementById('form_installment').value || '0.00';

        document.getElementById('modalDeductionType').value = type;
        document.getElementById('modalAmountInput').value = amount;
        window.toggleModalAmountField();
        document.getElementById('extraInfoModal').classList.remove('hidden');
    };

    window.closeExtraInfoModal = function() {
        document.getElementById('extraInfoModal').classList.add('hidden');
    };

    window.toggleModalAmountField = function() {
        const type = document.getElementById('modalDeductionType').value;
        const row = document.getElementById('modalAmountRow');
        if (type === 'Fixed') {
            row.style.display = 'flex';
        } else {
            row.style.display = 'none';
        }
    };

    window.saveExtraInfoModal = function() {
        const type = document.getElementById('modalDeductionType').value;
        const amount = parseFloat(document.getElementById('modalAmountInput').value) || 0;

        document.getElementById('form_deduction_type').value = type;
        document.getElementById('form_installment').value = (type === 'Fixed') ? amount.toFixed(2) : '0.00';

        const displayEl = document.getElementById('installmentDisplay');
        if (displayEl) {
            displayEl.textContent = (type === 'Fixed') ? amount.toFixed(2) + ' ₹' : type;
        }

        window.closeExtraInfoModal();
    };
</script>
@endsection
