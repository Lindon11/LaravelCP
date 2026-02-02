<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::with('category')
            ->orderBy('order', 'asc')
            ->get();
        return response()->json($faqs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $faq = Faq::create($validated);
        return response()->json($faq->load('category'), 201);
    }

    public function show(Faq $faq)
    {
        return response()->json($faq->load('category'));
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $faq->update($validated);
        return response()->json($faq->load('category'));
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(null, 204);
    }
}
