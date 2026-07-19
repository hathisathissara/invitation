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
        $wedding = \Illuminate\Support\Facades\Auth::user()->wedding;
        $image = \App\Models\GuestGallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        // 1. Cloudinary URL එකෙන් Public ID එක හොයා ගැනීම
        // උදාහරණයක් ලෙස: https://res.cloudinary.com/demo/image/upload/v12345/lumus/guest_gallery/guest_123.webp
        $urlParts = explode('/', $image->image_path);
        $fileNameWithExt = end($urlParts); // guest_123.webp
        $fileName = explode('.', $fileNameWithExt)[0]; // guest_123
        
        // Folder නමත් එක්ක Public ID එක හදාගන්නවා
        $publicId = 'lumus/guest_gallery/' . $fileName; 

        // 2. Cloudinary Admin API එකට යැවීමට අදාල දත්ත සැකසීම
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $timestamp = time();

        // Cloudinary Security Signature එක හැදීම (SHA-1)
        $signatureString = "public_id={$publicId}&timestamp={$timestamp}{$apiSecret}";
        $signature = sha1($signatureString);

        // 3. API රික්වෙස්ට් එක යැවීම
        $response = Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
            'public_id' => $publicId,
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        // 4. DB එකෙන් මකා දැමීම (Cloud එකෙන් මැකුණත් නැතත් අපි අපේ DB එක පිරිසිදු කරනවා)
        $image->delete();

        return redirect()->route('guest-gallery.index')->with('status', 'Shared photo permanently deleted!');
    }

    /**
     * WebP to JPG Dynamic Converter Download.
     */
     public function downloadJpg($id)
    {
        $wedding = Auth::user()->wedding;
        $image = GuestGallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        $cloudUrl = $image->image_path; // Cloudinary URL එක

        // GD Library එකෙන් Cloudinary WebP එක කියවා ගැනීම
        $gdImage = @imagecreatefromwebp($cloudUrl);
        
        if (!$gdImage) {
            // GD fail උනොත් (සමහරවිට සර්වර් එකේ allow_url_fopen off නම්), 
            // කෙලින්ම Cloudinary ලින්ක් එකට redirect කරනවා
            return redirect($cloudUrl);
        }

        // Guest name එකෙන් slug එකක් හදා filenames sanitize කිරීම
        $fileName = \Illuminate\Support\Str::slug($image->guest_name) . '_wedding_shared_moment.jpg';

        // Laravel Stream download එක හරහා memory save වන සේ JPG එක Output කිරීම
        return response()->streamDownload(function() use ($gdImage) {
            imagejpeg($gdImage, null, 90); // 90% High quality output
            imagedestroy($gdImage);
        }, $fileName, [
            'Content-Type' => 'image/jpeg',
        ]);
    }
}
