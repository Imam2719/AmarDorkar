<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en'    => 'required|string|max:100',
            'name_bn'    => 'required|string|max:100',
            'icon'       => 'nullable|string|max:60',
            'color'      => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        Category::create([
            'name_en'    => $request->name_en,
            'name_bn'    => $request->name_bn,
            'icon'       => $request->icon  ?? 'fas fa-tag',
            'color'      => $request->color ?? '#1a9e4a',
            'sort_order' => $request->sort_order ?? 0,
            'status'     => true,
        ]);

        Cache::forget('active_categories'); // FIX PERF 3
        return back()->with('success', 'Category added successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_en'    => 'required|string|max:100',
            'name_bn'    => 'required|string|max:100',
            'icon'       => 'nullable|string|max:60',
            'color'      => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name_en'    => $request->name_en,
            'name_bn'    => $request->name_bn,
            'icon'       => $request->icon  ?? $category->icon,
            'color'      => $request->color ?? $category->color,
            'sort_order' => $request->sort_order ?? $category->sort_order,
        ]);

        Cache::forget('active_categories'); // FIX PERF 3
        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'এই ক্যাটাগরিতে পণ্য আছে, ডিলিট করা যাবে না।');
        }
        $category->delete();
        Cache::forget('active_categories'); // FIX PERF 3
        return back()->with('success', 'Category deleted.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['status' => !$category->status]);
        Cache::forget('active_categories'); // FIX PERF 3
        return back()->with('success', 'Status updated.');
    }
}
