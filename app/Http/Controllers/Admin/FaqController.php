<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('display_order')->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:50',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $faq = Faq::create($validated);

        AuditLog::logAction('faq_created', 'Faq', (string) $faq->id);

        return back()->with('success', 'FAQ item added.');
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:50',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $faq->update($validated);

        AuditLog::logAction('faq_updated', 'Faq', (string) $faq->id);

        return back()->with('success', 'FAQ updated.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        AuditLog::logAction('faq_deleted', 'Faq', (string) $faq->id);

        return back()->with('success', 'FAQ deleted.');
    }
}
