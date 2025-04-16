<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Box;

class BoxController extends Controller
{
    public function index()
    {
        return response()->json(Box::where('active', 1)->get());
    }

    public function show($id)
    {
        $box = Box::with('items')->findOrFail($id);
        return response()->json($box);
    }
}
