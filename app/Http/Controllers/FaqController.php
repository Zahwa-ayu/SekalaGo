<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::where('is_active', true)
            ->whereIn('category', ['customer', 'corporate'])
            ->orderBy('sort_order')
            ->with('translations')
            ->get()
            ->groupBy('category');

        return view('faq.index', compact('faqs'));
    }
}