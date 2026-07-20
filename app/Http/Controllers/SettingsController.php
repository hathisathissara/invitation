<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
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
     * Update wedding details.
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

        if ($wedding->bride_name !== $bride || $wedding->groom_name !== $groom) {
            $slugBase = \Illuminate\Support\Str::slug($bride . '-' . $groom);
            $slug = $slugBase;
            
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
     * Delete account permanently (Self-Delete with Cloudinary Cleanups).
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

        // 1. Couple ගේ Gallery පින්තූර Cloudinary එකෙන් සදහටම මකා දැමීම [2]
        if ($wedding) {
            foreach ($wedding->galleries as $img) {
                $this->deleteCloudinaryFile($img->image_path, 'lumus/gallery');
            }

            // 2. Guest Shared Gallery පින්තූර Cloudinary එකෙන් සදහටම මකා දැමීම [2]
            foreach ($wedding->guestGalleries as $img) {
                $this->deleteCloudinaryFile($img->image_path, 'lumus/guest_gallery');
            }
        }

        // 3. Payment Slip එක Cloudinary එකෙන් මකා දැමීම [2]
        if (!empty($user->payment_slip)) {
            $this->deleteCloudinaryFile($user->payment_slip, 'lumus/slips');
        }

        // 4. Upgrade Slip එක Cloudinary එකෙන් මකා දැමීම (තිබේ නම්)
        if (!empty($user->upgrade_slip)) {
            $this->deleteCloudinaryFile($user->upgrade_slip, 'lumus/slips');
        }

        // 5. User ව Database එකෙන් මකා දැමීම (cascade delete) [2]
        $user->delete();

        // Session destroy & logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Your account and all associated data have been permanently deleted.');
    }

    /* =====================================================================
       💡 CLOUDINARY FILE DELETION HELPER (Zero Dependencies API) [2]
       ===================================================================== */
    private function deleteCloudinaryFile($url, $folder)
    {
        if (empty($url) || !str_starts_with($url, 'http')) return;
        
        // Extract public ID from the URL [2]
        $urlParts = explode('/', $url);
        $fileNameWithExt = end($urlParts);
        $fileName = explode('.', $fileNameWithExt)[0];
        $publicId = $folder . '/' . $fileName;

        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $timestamp = time();

        $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$apiSecret}");

        // Call Cloudinary Admin API dynamically [2]
        Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
            'public_id' => $publicId,
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
    }
}