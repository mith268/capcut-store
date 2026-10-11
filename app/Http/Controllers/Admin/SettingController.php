<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\RefundPolicy;
use App\Models\Setting;
use App\Models\WarrantyPolicy;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $warranty = WarrantyPolicy::first();
        $refund = RefundPolicy::first();

        $razorpayKeyId = (string) Setting::get('razorpay_key_id', '');
        $razorpayHasSecret = !empty(Setting::get('razorpay_key_secret'));
        $razorpayMode = (string) Setting::get('razorpay_mode', 'test');

        // Safe masked preview of Key ID: show first 8 chars and mask remainder
        $razorpayKeyIdMasked = '';
        if (!empty($razorpayKeyId) && $razorpayKeyId !== 'rzp_test_placeholder') {
            $prefix = substr($razorpayKeyId, 0, 9);
            $razorpayKeyIdMasked = $prefix . str_repeat('•', max(4, strlen($razorpayKeyId) - 9));
        }

        return view('admin.settings.index', compact(
            'settings', 
            'warranty', 
            'refund', 
            'razorpayKeyId', 
            'razorpayHasSecret', 
            'razorpayMode', 
            'razorpayKeyIdMasked'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_logo_file' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:3072',
            'store_name' => 'nullable|string|max:150',
            'store_tagline' => 'nullable|string|max:200',
            'store_badge' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:30',
            'razorpay_mode' => 'nullable|string|in:test,live',
            'razorpay_key_id' => 'nullable|string|max:100',
            'razorpay_key_secret' => 'nullable|string|max:200',
            'razorpay_webhook_secret' => 'nullable|string|max:200',
        ]);

        $inputs = $request->except([
            '_token', 
            'store_logo_file', 
            'store_logo_data',
            'remove_store_logo',
            'warranty_content', 
            'warranty_days', 
            'refund_content', 
            'eligible_issues', 
            'excluded_issues'
        ]);

        // Handle Store Logo (Base64 data URI from standard forms or file upload)
        if ($request->filled('store_logo_data') && str_starts_with($request->input('store_logo_data'), 'data:image/')) {
            $dataUri = $request->input('store_logo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $dataUri, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                if ($ext === 'svg+xml') $ext = 'svg';
                $rawData = substr($dataUri, strpos($dataUri, ',') + 1);
                $decoded = base64_decode($rawData);
                if ($decoded !== false) {
                    $filename = 'logo_' . time() . '.' . ($ext ?: 'png');
                    $destPath = public_path('uploads/branding');
                    if (!file_exists($destPath)) {
                        mkdir($destPath, 0755, true);
                    }
                    file_put_contents($destPath . '/' . $filename, $decoded);
                    Setting::set('store_logo', '/uploads/branding/' . $filename, false, 'general');
                }
            }
        } elseif ($request->hasFile('store_logo_file') && $request->file('store_logo_file')->isValid()) {
            $file = $request->file('store_logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $destPath = public_path('uploads/branding');
            if (!file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            Setting::set('store_logo', '/uploads/branding/' . $filename, false, 'general');
        } elseif ($request->boolean('remove_store_logo')) {
            Setting::set('store_logo', '', false, 'general');
        }

        // Update standard key-values
        foreach ($inputs as $key => $val) {
            $isEncrypted = in_array($key, [
                'razorpay_key_secret',
                'razorpay_webhook_secret',
                'meta_access_token',
            ]);

            // Don't overwrite encrypted secrets with masked placeholder strings or empty values
            if ($isEncrypted && (empty($val) || preg_match('/^[•\*]+$/u', trim((string) $val)))) {
                continue;
            }

            Setting::set($key, $val, $isEncrypted);
        }

        // Flush application cache to ensure immediate synchronization across storefront
        \Illuminate\Support\Facades\Cache::flush();

        // Update Warranty Policy if provided
        if ($request->has('warranty_content')) {
            WarrantyPolicy::updateOrCreate(
                ['id' => 1],
                [
                    'warranty_days' => (int) $request->input('warranty_days', 30),
                    'policy_content' => $request->input('warranty_content'),
                    'eligible_issues' => $request->input('eligible_issues'),
                    'excluded_issues' => $request->input('excluded_issues'),
                ]
            );
        }

        // Update Refund Policy if provided
        if ($request->has('refund_content')) {
            RefundPolicy::updateOrCreate(
                ['id' => 1],
                [
                    'refund_conditions' => $request->input('refund_content'),
                    'replacement_conditions' => $request->input('replacement_conditions'),
                    'excluded_conditions' => $request->input('refund_excluded_conditions'),
                ]
            );
        }

        AuditLog::logAction('settings_updated', 'Setting', null, [
            'updated_keys' => array_keys($inputs),
        ]);

        return back()->with('success', 'Store settings updated successfully.');
    }

    /**
     * Test Razorpay connection using provided or saved credentials
     */
    public function testRazorpayConnection(Request $request, \App\Services\Payment\RazorpayPaymentService $razorpayService)
    {
        $keyId = $request->input('razorpay_key_id');
        $keySecret = $request->input('razorpay_key_secret');
        $mode = $request->input('razorpay_mode');

        if ($mode && in_array($mode, ['test', 'live'])) {
            Setting::set('razorpay_mode', $mode, false, 'payment');
        }

        // If secret was not entered or contains masked bullets, use saved encrypted secret
        if (empty($keySecret) || preg_match('/^[•\*]+$/u', trim((string) $keySecret))) {
            $keySecret = (string) Setting::get('razorpay_key_secret', '');
        }

        if (empty($keyId)) {
            $keyId = (string) Setting::get('razorpay_key_id', '');
        }

        $result = $razorpayService->testConnection($keyId, $keySecret);

        AuditLog::logAction('razorpay_connection_tested', 'Setting', null, [
            'mode' => $result['mode'] ?? 'test',
            'success' => $result['success'] ?? false,
        ]);

        return response()->json($result);
    }
}
