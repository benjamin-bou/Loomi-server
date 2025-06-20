<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Box;
use Illuminate\Support\Facades\Auth;

class BoxController extends Controller
{
    public function index()
    {
        return response()->json(Box::where('active', 1)->with(['category', 'images'])->get());
    }

    public function show($id)
    {
        // $box = Box::with(['items', 'categories'])->findOrFail($id);
        $box = Box::with(['items', 'category', 'images'])->findOrFail($id);
        return response()->json($box);
    }

    public function adminIndex()
    {
        $user = Auth::user(); // replaced auth()->user()

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(Box::with(['category', 'images'])->get());
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $box = Box::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
        ]);

        $box->update($validatedData);

        return response()->json(['message' => 'Box updated successfully', 'box' => $box]);
    }
}
