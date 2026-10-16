<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $items = Supplier::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(8);

        $categories = Category::where('type', 'suppliers')->get();

        return view('suppliers.index', compact('items', 'categories'));
    }
}