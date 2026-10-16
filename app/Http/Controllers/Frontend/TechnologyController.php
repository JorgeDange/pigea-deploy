<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Technology;

class TechnologyController extends Controller
{
    public function index()
    {
        $items = Technology::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(9);

        $categories = Category::where('type', 'technologies')->get();

        return view('technologies.index', compact('items', 'categories'));
    }
}