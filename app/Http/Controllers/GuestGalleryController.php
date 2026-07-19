<?php

namespace App\Http\Controllers;

use App\Models\GuestGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GuestGalleryController extends Controller
{
    /**
     * Display the Guest shared photo gallery.
     */
    public function index()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        // Check if package allows guest shared gallery
        $hasGuestGallery = ($user->package === 'premium' || intval($user->has_guest_gallery) === 1);

        $guestImages = [];
        if ($hasGuestGallery) {
            $guestImages = $wedding->guestGalleries()->orderBy('id', 'desc')->get();
        }

        return view('guest_gallery.index', compact('guestImages', 'hasGuestGallery'));
    }

    /**
     * AJAX Live check for shared photos (Real-time polling)
     */
    public function liveCheck()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        $hasGuestGallery = ($user->package === 'premium' || intval($user->has_guest_gallery) === 1);

        if (!$hasGuestGallery) {
            return response()->json(['images' => []]);
        }

        // Fetch shared photos with formatted date for polling update
        $images = $wedding->guestGalleries()->orderBy('id', 'desc')->get()->map(function($img) {
            return [
                'id' => $img->id,
                'image_path' => $img->image_path,
                'guest_name' => $img->guest_name,
                'uploaded_at' => $img->created_at ? $img->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json(['images' => $images]);
    }

    /**
     * Delete shared photo from server disk and DB.
     */
    public function destroy($id)
    {
        $wedding = Auth::user()->wedding;
        $image = GuestGallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        // Server disk එකෙන් physical file එක delete කිරීම
        $physicalPath = public_path($image->image_path);
        if (File::exists($physicalPath)) {
            File::delete($physicalPath);
        }

        // DB එකෙන් delete කිරීම
        $image->delete();

        return redirect()->route('guest-gallery.index')->with('status', 'Shared photo deleted successfully!');
    }

    /**
     * WebP to JPG Dynamic Converter Download.
     */
    public function downloadJpg($id)
    {
        $wedding = Auth::user()->wedding;
        $image = GuestGallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        $physicalPath = public_path($image->image_path);

        if (!File::exists($physicalPath)) {
            abort(404, 'Shared image file not found on disk.');
        }

        // GD Library එකෙන් WebP එක කියවා ගැනීම
        $gdImage = @imagecreatefromwebp($physicalPath);
        
        if (!$gdImage) {
            // GD fail උනොත් original file එකම download වෙන්න දෙනවා (Fallback safety)
            return response()->download($physicalPath);
        }

        // Guest name එකෙන් slug එකක් හදා filenames sanitize කිරීම
        $fileName = Str::slug($image->guest_name) . '_wedding_shared_moment.jpg';

        // Laravel Stream download එක හරහා memory save වන සේ JPG එක Output කිරීම
        return response()->streamDownload(function() use ($gdImage) {
            imagejpeg($gdImage, null, 90); // 90% High quality output
            imagedestroy($gdImage);
        }, $fileName, [
            'Content-Type' => 'image/jpeg',
        ]);
    }
}
