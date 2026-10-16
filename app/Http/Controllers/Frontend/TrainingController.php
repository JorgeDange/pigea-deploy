<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Training;

class TrainingController extends Controller
{
    public function index()
    {
        $items = Training::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(9);

        $categories = Category::where('type', 'training')->get();

        return view('training.index', compact('items', 'categories'));
    }
}