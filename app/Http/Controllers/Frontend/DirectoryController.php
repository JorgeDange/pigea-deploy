<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Directory;

class DirectoryController extends Controller
{
    public function index()
    {
        $items = Directory::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(8);

        $categories = Category::where('type', 'directory')->get();

        return view('directory.index', compact('items', 'categories'));
    }
}