<?php

namespace App\Http\Controllers;

use App\Models\RefundPolicy;
use App\Models\Setting;
use App\Models\SupportMessage;
use App\Models\WarrantyPolicy;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsappService
    ) {}

    public function privacy()
    {
        $settings = Setting::getAllPublic();
        return view('pages.privacy', compact('settings'));
    }

    public function terms()
    {
        $settings = Setting::getAllPublic();
        $warranty = WarrantyPolicy::where('is_active', true)->first();
        return view('pages.terms', compact('settings', 'warranty'));
    }

    public function refund()
    {
        $settings = Setting::getAllPublic();
        $refund = RefundPolicy::where('is_active', true)->first();
        return view('pages.refund', compact('settings', 'refund'));
    }

    public function warranty()
    {
        $settings = Setting::getAllPublic();
        $warranty = WarrantyPolicy::where('is_active', true)->first();
        return view('pages.warranty', compact('settings', 'warranty'));
    }

    public function support()
    {
        $settings = Setting::getAllPublic();
        $whatsappSupportUrl = $this->whatsappService->generateSupportUrl();
        return view('pages.support', compact('settings', 'whatsappSupportUrl'));
    }

    public function submitSupport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'order_number' => 'nullable|string|max:50',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        SupportMessage::create($validated);

        return back()->with('success', 'Thank you! Your message has been received. Our team will respond shortly.');
    }
}
