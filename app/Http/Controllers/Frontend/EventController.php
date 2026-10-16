<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $items = Event::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(8);

        $categories = Category::where('type', 'events')->get();

        return view('events.index', compact('items', 'categories'));
    }
}