<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RateChart;
use App\Models\RateChartSlab;
use App\Models\AuditLog;

class RateChartController extends Controller
{
    public function index()
    {
        $rateCharts = RateChart::with('slabs')->get();
        return view('rates.index', compact('rateCharts'));
    }

    public function create()
    {
        return view('rates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'calculation_type' => 'required|in:fat_snf_formula,matrix,flat',
            'base_rate' => 'required|numeric|min:1',
            'min_fat' => 'required|numeric|min:1',
            'max_fat' => 'required|numeric|min:1',
            'min_snf' => 'required|numeric|min:1',
            'max_snf' => 'required|numeric|min:1',
            'fat_factor' => 'required|numeric|min:0',
            'snf_factor' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'is_default' => 'nullable|boolean',
        ]);

        $validated['is_default'] = $request->has('is_default');
        if ($validated['is_default']) {
            RateChart::where('milk_type', $validated['milk_type'])->update(['is_default' => false]);
        }

        $chart = RateChart::create($validated);
        AuditLog::log('Created Rate Chart', 'RateChart', $chart->id);

        return redirect()->route('rates.index')->with('success', "Rate chart {$chart->name} created successfully!");
    }

    public function edit(RateChart $rate)
    {
        return view('rates.edit', ['chart' => $rate]);
    }

    public function update(Request $request, RateChart $rate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'milk_type' => 'required|in:cow,buffalo,mixed',
            'calculation_type' => 'required|in:fat_snf_formula,matrix,flat',
            'base_rate' => 'required|numeric|min:1',
            'fat_factor' => 'required|numeric|min:0',
            'snf_factor' => 'required|numeric|min:0',
            'min_fat' => 'required|numeric|min:1',
            'max_fat' => 'required|numeric|min:1',
            'min_snf' => 'required|numeric|min:1',
            'max_snf' => 'required|numeric|min:1',
            'effective_date' => 'required|date',
            'status' => 'required|in:active,inactive',
        ]);

        $rate->update($validated);
        AuditLog::log('Updated Rate Chart', 'RateChart', $rate->id);

        return redirect()->route('rates.index')->with('success', 'Rate chart updated successfully.');
    }
}
