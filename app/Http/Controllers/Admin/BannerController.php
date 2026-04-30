<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'title'       => 'nullable|string|max:200',
            'subtitle'    => 'nullable|string|max:300',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        try {
            $file     = $request->file('image');
            $imgData  = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();

            Banner::create([
                'image_data'  => $imgData,
                'mime_type'   => $mimeType,
                'title'       => $request->title,
                'subtitle'    => $request->subtitle,
                'button_text' => $request->button_text,
                'button_link' => $request->button_link,
                'sort_order'  => $request->sort_order ?? 0,
                'is_active'   => $request->has('is_active'),
            ]);

            Cache::forget('active_banners'); // ✅ Homepage cache clear
            return back()->with('success', 'Banner সফলভাবে যোগ হয়েছে।');

        } catch (\Exception $e) {
            return back()->with('error', 'Banner যোগ করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
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

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $updateData['image_data'] = base64_encode(file_get_contents($file->getRealPath()));
                $updateData['mime_type']  = $file->getMimeType();
            }

            $banner->update($updateData);
            Cache::forget('active_banners'); // ✅ Homepage cache clear
            return back()->with('success', 'Banner সফলভাবে আপডেট হয়েছে।');

        } catch (\Exception $e) {
            return back()->with('error', 'Banner আপডেট করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        Cache::forget('active_banners'); // ✅ Homepage cache clear
        return back()->with('success', 'Banner মুছে ফেলা হয়েছে।');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        Cache::forget('active_banners'); // ✅ Homepage cache clear
        return back()->with('success', 'Banner status আপডেট হয়েছে।');
    }
}