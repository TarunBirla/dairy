<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebsiteBanner;
use App\Models\WebsiteSetting;
use App\Models\Product;
use App\Models\AuditLog;

class WebsiteController extends Controller
{
    public function home()
    {
        $banners = WebsiteBanner::where('is_active', true)->orderBy('sort_order')->get();
        $products = Product::where('status', 'active')->where('in_stock', true)->take(8)->get();
        $settings = WebsiteSetting::pluck('value', 'key');

        return view('frontend.home', compact('banners', 'products', 'settings'));
    }

    public function about()
    {
        $settings = WebsiteSetting::pluck('value', 'key');
        return view('frontend.about', compact('settings'));
    }

    public function products()
    {
        $products = Product::where('status', 'active')->paginate(12);
        return view('frontend.products', compact('products'));
    }

    public function contact()
    {
        $settings = WebsiteSetting::pluck('value', 'key');
        return view('frontend.contact', compact('settings'));
    }

    // Admin Website CMS
    public function banners()
    {
        $banners = WebsiteBanner::orderBy('sort_order')->get();
        $settings = WebsiteSetting::pluck('value', 'key');
        return view('admin.website.banners', compact('banners', 'settings'));
    }

    public function storeBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'badge_text' => 'nullable|string',
            'cta_text' => 'nullable|string',
            'cta_url' => 'nullable|string',
            'bg_gradient' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        WebsiteBanner::create($validated);
        AuditLog::log('Created Website Banner', 'WebsiteBanner', 0);

        return back()->with('success', 'Banner added successfully!');
    }

    public function toggleBanner(WebsiteBanner $banner)
    {
        $banner->is_active = !$banner->is_active;
        $banner->save();
        return back()->with('success', 'Banner status updated!');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $k => $v) {
            WebsiteSetting::updateOrCreate(['key' => $k], ['value' => $v]);
        }
        AuditLog::log('Updated Website CMS Settings', 'WebsiteSetting', 0);

        return back()->with('success', 'Website settings saved!');
    }
}
