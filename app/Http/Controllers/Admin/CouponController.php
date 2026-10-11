<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('redemptions')->latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0.01',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_customer_limit' => 'required|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['minimum_amount'] = $validated['minimum_amount'] ?? 0;

        $coupon = Coupon::create($validated);

        AuditLog::logAction('coupon_created', 'Coupon', (string) $coupon->id, ['code' => $coupon->code]);

        return back()->with('success', "Coupon '{$coupon->code}' created successfully.");
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0.01',
            'minimum_amount' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_customer_limit' => 'required|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['minimum_amount'] = $validated['minimum_amount'] ?? 0;

        $coupon->update($validated);

        AuditLog::logAction('coupon_updated', 'Coupon', (string) $coupon->id, ['code' => $coupon->code]);

        return back()->with('success', "Coupon '{$coupon->code}' updated.");
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        AuditLog::logAction('coupon_deleted', 'Coupon', null, ['code' => $code]);

        return back()->with('success', "Coupon '{$code}' deleted.");
    }
}
