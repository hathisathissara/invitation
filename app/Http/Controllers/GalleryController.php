<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    /**
     * Display the couple's gallery and love story page.
     */
    public function index()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        $images = $wedding->galleries()->orderBy('id', 'desc')->get();

        return view('gallery.index', compact('wedding', 'images'));
    }

    /**
     * Update the Love Story text.
     */
    public function updateStory(Request $request)
    {
        $request->validate([
            'love_story' => ['nullable', 'string'],
        ]);

        $wedding = Auth::user()->wedding;
        $wedding->update([
            'love_story' => trim($request->love_story),
        ]);

        return redirect()->route('gallery.index')->with('status', 'Love story saved successfully!');
    }

    /**
     * Handle AJAX image upload (Compressed WebP).
     */
    public function upload(Request $request)
    {
        if ($request->hasFile('gallery_image')) {
            $file = $request->file('gallery_image');
            $imageData = 'data:image/' . $file->extension() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $preset = env('CLOUDINARY_UPLOAD_PRESET');

            $response = \Illuminate\Support\Facades\Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                'file' => $imageData,
                'upload_preset' => $preset,
                'folder' => 'lumus/gallery',
            ]);

            if ($response->successful()) {
                $dbPath = $response->json('secure_url');
                $publicId = $response->json('public_id'); // 👈 Cloudinary දෙන ID එක
                
                $wedding = Auth::user()->wedding;
                $wedding->galleries()->create([
                    'image_path' => $dbPath,
                    'public_id' => $publicId, // 👈 DB එකට සේව් කරනවා
                ]);
                return response()->json(['success' => true]);
            }
        }
        return response()->json(['success' => false, 'message' => 'Upload failed.']);
    }

    public function destroy($id)
    {
        $wedding = Auth::user()->wedding;
        $image = Gallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        if ($image->public_id) {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $apiKey = env('CLOUDINARY_API_KEY');
            $apiSecret = env('CLOUDINARY_API_SECRET');
            $timestamp = time();

            // 👈 දැන් කෙලින්ම DB එකේ තියෙන ID එක පාවිච්චි කරනවා (URL කඩන්නේ නෑ)
            $signature = sha1("public_id={$image->public_id}&timestamp={$timestamp}{$apiSecret}");

            \Illuminate\Support\Facades\Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy", [
                'public_id' => $image->public_id,
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ]);
        }

        if ($wedding->hero_image === $image->image_path) {
            $wedding->update(['hero_image' => null]);
        }
        $image->delete();

        return redirect()->route('gallery.index')->with('status', 'Photo permanently removed!');
    }

    /**
     * Set selected image as the Hero Cover Image.
     */
    public function setCover($id)
    {
        $wedding = Auth::user()->wedding;
        $image = Gallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        $wedding->update([
            'hero_image' => $image->image_path,
        ]);

        return redirect()->route('gallery.index')->with('status', 'Cover image updated successfully!');
    }
}