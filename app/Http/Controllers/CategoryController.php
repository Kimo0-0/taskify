<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $shares = \App\Models\CategoryShare::where('user_id', \Illuminate\Support\Facades\Auth::id())->get()->keyBy('category_id');
        return view('Categories', compact('categories', 'shares'));
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

    /**
     * Toggle public sharing on/off for a category folder, and update permissions.
     */
    public function toggleShare(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $userId = \Illuminate\Support\Facades\Auth::id();

        $share = \App\Models\CategoryShare::where('user_id', $userId)
            ->where('category_id', $category->id)
            ->first();

        if ($request->has('disable') && $request->disable) {
            if ($share) {
                $share->delete();
            }
            return response()->json([
                'status' => 'success',
                'shared' => false,
                'message' => 'Category sharing disabled successfully'
            ]);
        }

        if ($share && !$request->has('can_edit') && !$request->has('can_complete')) {
            $share->delete();
            return response()->json([
                'status' => 'success',
                'shared' => false,
                'message' => 'Category sharing disabled successfully'
            ]);
        }

        if (!$share) {
            $share = \App\Models\CategoryShare::create([
                'user_id' => $userId,
                'category_id' => $category->id,
                'share_token' => \Illuminate\Support\Str::random(32),
                'can_edit' => false,
                'can_complete' => false
            ]);
        }

        if ($request->has('can_edit')) {
            $share->can_edit = (bool)$request->can_edit;
        }
        if ($request->has('can_complete')) {
            $share->can_complete = (bool)$request->can_complete;
        }
        $share->save();

        return response()->json([
            'status' => 'success',
            'shared' => true,
            'share_url' => $share->share_url,
            'can_edit' => (bool)$share->can_edit,
            'can_complete' => (bool)$share->can_complete,
            'message' => 'Category share settings updated successfully'
        ]);
    }
}
