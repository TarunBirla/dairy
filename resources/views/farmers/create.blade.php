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
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-3.5">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="milk" class="w-4 h-4 text-emerald-600"></i>
                <span>Milk Details & Rates (RO / RF) / दूध एवं दर विवरण</span>
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

                <!-- Cow Milk Rate (Fixed / Custom) -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Cow Milk Rate (₹/L) / गाय दर
                    </label>
                    <input type="number" step="0.01" name="cow_milk_rate" value="{{ old('cow_milk_rate') }}" placeholder="e.g. 40.00 (Leave blank for Chart)"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    <span class="text-[10px] text-slate-400">Blank = Standard Chart (RO)</span>
                </div>

                <!-- Buffalo Milk Rate (Fixed / Custom) -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Buffalo Milk Rate (₹/L) / भैंस दर
                    </label>
                    <input type="number" step="0.01" name="buffalo_milk_rate" value="{{ old('buffalo_milk_rate') }}" placeholder="e.g. 60.00 (Leave blank for Chart)"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    <span class="text-[10px] text-slate-400">Blank = Standard Chart (RO)</span>
                </div>

                <!-- Rate Chart Applied -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Rate Chart Applied / लागू दर चार्ट
                    </label>
                    <select name="rate_chart_id" class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                        <option value="">Standard Quality Chart (मानक दर चार्ट)</option>
                        @foreach($rateCharts as $rc)
                            <option value="{{ $rc->id }}" {{ old('rate_chart_id') == $rc->id ? 'selected' : '' }}>
                                {{ $rc->name }} ({{ ucfirst($rc->milk_type) }})
                            </option>
                        @endforeach
                    </select>
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

        <!-- SECTION 4: FINANCIAL & SECURITY DEPOSIT (ANAMAT) / वित्तीय विवरण एवं अमानत -->
        <div class="bg-slate-50/70 rounded-2xl p-5 border border-slate-200/80 space-y-3.5">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-200 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="badge-indian-rupee" class="w-4 h-4 text-emerald-600"></i>
                <span>Financial & Anamat Deposit / वित्तीय विवरण एवं अमानत</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Anamat Deposit -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                        Anamat / अमानत (₹)
                    </label>
                    <input type="number" step="0.01" name="anamat" value="{{ old('anamat', '0.00') }}" placeholder="0.00"
                           class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition">
                    <span class="text-[10px] text-slate-400">Opening security deposit balance</span>
                </div>

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
@endsection
