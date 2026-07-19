<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class SettingsController extends Controller
{
    /**
     * Display the settings index.
     */
    public function index()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        return view('settings.index', compact('user', 'wedding'));
    }

    /**
     * Update wedding details and conditionally update slug.
     */
    public function updateWedding(Request $request)
    {
        $request->validate([
            'bride_name' => ['required', 'string', 'max:100'],
            'groom_name' => ['required', 'string', 'max:100'],
            'wedding_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $wedding = $user->wedding;

        $bride = trim($request->bride_name);
        $groom = trim($request->groom_name);
        
        $slugChanged = false;

        // Bride හෝ Groom ගේ නම වෙනස් වුවහොත් පමණක් අලුත් Slug එකක් හදනවා
        if ($wedding->bride_name !== $bride || $wedding->groom_name !== $groom) {
            $slugBase = Str::slug($bride . '-' . $groom);
            $slug = $slugBase;
            
            // Ensure unique slug (excluding this wedding's own ID)
            while (Wedding::where('slug', $slug)->where('id', '!=', $wedding->id)->exists()) {
                $slug = $slugBase . '-' . rand(100, 999);
            }
            $wedding->slug = $slug;
            $slugChanged = true;
        }

        $wedding->update([
            'bride_name' => $bride,
            'groom_name' => $groom,
            'wedding_date' => $request->wedding_date,
            'venue' => trim($request->venue),
            'slug' => $wedding->slug
        ]);

        // User ගේ display name එකත් Wedding names වලට update කරනවා
        $user->update([
            'name' => $bride . ' & ' . $groom
        ]);

        $statusMsg = $slugChanged 
            ? 'Wedding details updated! Your invitation link has changed — please re-share the new link with guests.' 
            : 'Wedding details updated!';

        return redirect()->route('settings.index')
            ->with('status', $statusMsg)
            ->with('open_section', 'wedding');
    }

    /**
     * Change user password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])
                ->with('open_section', 'password');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.index')
            ->with('status', 'Password updated successfully!')
            ->with('open_section', 'password');
    }

    /**
     * Delete account permanently (Manually wipe disk files first).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'delete_password' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $wedding = $user->wedding;

        if (!Hash::check($request->delete_password, $user->password)) {
            return back()->withErrors(['delete_password' => 'Incorrect password. Account not deleted.'])
                ->with('open_section', 'danger');
        }

        // 1. Server disk එකේ තියෙන Couple ගේ gallery පින්තූර මකා දැමීම
        if ($wedding) {
            foreach ($wedding->galleries as $img) {
                $path = public_path($img->image_path);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            // 2. Server disk එකේ තියෙන Guest gallery පින්තූර මකා දැමීම
            foreach ($wedding->guestGalleries as $img) {
                $path = public_path($img->image_path);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
        }

        // 3. User ව delete කිරීම ( cascadeOnDelete නිසා weddings, guests, tasks auto මැකෙනවා) [2]
        $user->delete();

        // Session destroy & logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Your account and all associated data have been permanently deleted.');
    }
}