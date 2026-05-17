<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('Categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create(['name' => $request->name]);

        return response()->json([
            'data' => [
                'id'   => $category->id,
                'name' => $category->name,
            ]
        ], 201);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if (!$category) {
            return response()->json(['error' => 'مش موجودة'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'تم الحذف'], 200);
    }
}
