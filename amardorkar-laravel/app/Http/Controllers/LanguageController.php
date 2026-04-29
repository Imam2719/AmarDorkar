<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $lang = $request->input('lang', 'bn');
        $lang = in_array($lang, ['bn', 'en']) ? $lang : 'bn';
        session(['language' => $lang]);

        // AJAX request থেকে এলে JSON return করো (page reload ছাড়া)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'lang' => $lang]);
        }

        return back();
    }

    public function theme(Request $request)
    {
        $theme = $request->input('theme', 'light');
        session(['theme' => in_array($theme, ['light', 'dark']) ? $theme : 'light']);
        return back();
    }
}
