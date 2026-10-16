<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Legislation;

class LegislationController extends Controller
{
    public function index()
    {
        $items = Legislation::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(8);

        $categories = Category::where('type', 'legislation')->get();

        return view('legislation.index', compact('items', 'categories'));
    }
}