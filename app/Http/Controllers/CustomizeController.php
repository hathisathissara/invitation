<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomizeController extends Controller
{
    /**
     * Display the customization index.
     */
    public function index()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        // Templates list matching layout config
        $allTemplates = [
            'premium_gold'     => ['label' => 'Premium Gold',     'sub' => 'Dark Theme',     'primary' => '#8a6520', 'accent' => '#c9a05a'],
            'minimal_light'    => ['label' => 'Minimal Light',    'sub' => 'Clean Theme',    'primary' => '#8f6f42', 'accent' => '#b8935a'],
            'terracotta_bloom' => ['label' => 'Terracotta Bloom', 'sub' => 'Warm Theme',     'primary' => '#8f4526', 'accent' => '#c1633d'],
            'plum_parchment'   => ['label' => 'Plum Parchment',   'sub' => 'Elegant Theme',  'primary' => '#4a2c3b', 'accent' => '#8a9a7e'],
            'floral_garden'    => ['label' => 'Floral Garden',    'sub' => 'Floral Theme',   'primary' => '#a15873', 'accent' => '#8fac7a'],
            'beach_tropical'   => ['label' => 'Beach Tropical',   'sub' => 'Tropical Theme', 'primary' => '#9c7e3f', 'accent' => '#c9a961'],
            'rustic_boho'      => ['label' => 'Rustic Boho',      'sub' => 'Boho Theme',     'primary' => '#9c6b4a', 'accent' => '#c98f6b'],
            'royal_classic'    => ['label' => 'Royal Classic',    'sub' => 'Royal Theme',    'primary' => '#1c2340', 'accent' => '#c6a15b'],
            'indian_royal'     => ['label' => 'Indian Royal',     'sub' => 'Indian Theme',   'primary' => '#6e1626', 'accent' => '#d4af37'],
        ];

        $languages = [
            'en' => ['label' => 'English',  'native' => 'English'],
            'si' => ['label' => 'Sinhala',  'native' => 'සිංහල'],
            'ta' => ['label' => 'Tamil',    'native' => 'தமிழ்'],
        ];

        // Read music list dynamically from config/music.php!
        $musicLibrary = config('music');

        return view('customize', compact('wedding', 'allTemplates', 'languages', 'musicLibrary'));
    }

    /**
     * Save chosen design template.
     */
    public function updateDesign(Request $request)
    {
        $request->validate([
            'template_name' => ['required', 'string'],
        ]);

        $wedding = Auth::user()->wedding;
        $wedding->update([
            'template_name' => $request->template_name,
        ]);

        return redirect()->route('customize.index')
            ->with('status', 'Design template updated successfully!')
            ->with('open_section', 'acc-design');
    }

    /**
     * Save chosen interface language.
     */
    public function updateLanguage(Request $request)
    {
        $request->validate([
            'invite_language' => ['required', 'string', 'in:en,si,ta'],
        ]);

        $wedding = Auth::user()->wedding;
        $wedding->update([
            'invite_language' => $request->invite_language,
        ]);

        return redirect()->route('customize.index')
            ->with('status', 'Invitation language updated successfully!')
            ->with('open_section', 'acc-language');
    }

    /**
     * Save background music configurations.
     */
    public function updateMusic(Request $request)
    {
        $wedding = Auth::user()->wedding;

        $musicEnabled = $request->has('music_enabled') && $request->music_enabled == '1';
        $track = $request->music_track ?? '';

        if (!$musicEnabled) {
            $wedding->update(['music_track' => null]);
            return redirect()->route('customize.index')
                ->with('status', 'Background music turned off.')
                ->with('open_section', 'acc-music');
        }

        // Validate track exists in config
        if (array_key_exists($track, config('music'))) {
            $wedding->update(['music_track' => $track]);
            return redirect()->route('customize.index')
                ->with('status', 'Background music updated successfully!')
                ->with('open_section', 'acc-music');
        }

        return back()->withErrors(['music' => 'Please choose a valid music track.']);
    }
}