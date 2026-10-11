<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('display_order')->get();
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_initial' => 'nullable|string|max:10',
            'review_text' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'verified' => 'boolean',
            'published' => 'boolean',
            'display_order' => 'integer',
        ]);

        $validated['verified'] = $request->boolean('verified', true);
        $validated['published'] = $request->boolean('published', true);

        $review = Testimonial::create($validated);

        AuditLog::logAction('testimonial_created', 'Testimonial', (string) $review->id);

        return back()->with('success', 'Customer review added successfully.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_initial' => 'nullable|string|max:10',
            'review_text' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'verified' => 'boolean',
            'published' => 'boolean',
            'display_order' => 'integer',
        ]);

        $validated['verified'] = $request->boolean('verified');
        $validated['published'] = $request->boolean('published');

        $testimonial->update($validated);

        AuditLog::logAction('testimonial_updated', 'Testimonial', (string) $testimonial->id);

        return back()->with('success', 'Review updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        AuditLog::logAction('testimonial_deleted', 'Testimonial', (string) $testimonial->id);

        return back()->with('success', 'Review removed.');
    }
}
