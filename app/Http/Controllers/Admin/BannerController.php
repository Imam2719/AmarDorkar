<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:9096',
            'title'       => 'nullable|string|max:200',
            'subtitle'    => 'nullable|string|max:300',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        // FIX: Do NOT pass image_data / mime_type — columns were dropped in
        // migration 2026_04_25_000002_drop_legacy_image_data_columns.php
        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'image_path'  => $path,
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->has('is_active'),
        ]);

        return back()->with('success', 'Banner added successfully.');
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:9096',
            'title'       => 'nullable|string|max:200',
            'subtitle'    => 'nullable|string|max:300',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $updateData = [
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
            'sort_order'  => $request->sort_order ?? 0,
            'is_active'   => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            // FIX: No image_data/mime_type columns
            $updateData['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($updateData);

        return back()->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();
        return back()->with('success', 'Banner deleted.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        return back()->with('success', 'Banner status updated.');
    }
}
