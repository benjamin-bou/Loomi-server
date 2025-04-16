<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Box;
use Illuminate\Support\Facades\Auth; // added import

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

    public function adminIndex()
    {
        $user = Auth::user(); // replaced auth()->user()

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(Box::all());
    }
}
