<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class InterviewController extends Controller
{
    public function index()
    {
        // A lista e a ordenação vivem no componente Livewire `InterviewFilter`.
        return view('interviews.index');
    }
}