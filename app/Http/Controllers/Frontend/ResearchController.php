<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Research;

class ResearchController extends Controller
{
    public function index()
    {
        $items = Research::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(9);

        $categories = Category::where('type', 'research')->get();

        return view('research.index', compact('items', 'categories'));
    }
}