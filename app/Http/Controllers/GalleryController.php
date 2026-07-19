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
            $newFilename = uniqid() . '.webp';
            
            // Save file directly inside public/uploads/gallery folder
            $file->move(public_path('uploads/gallery'), $newFilename);
            
            $dbPath = "uploads/gallery/" . $newFilename;
            
            $wedding = Auth::user()->wedding;
            $wedding->galleries()->create([
                'image_path' => $dbPath,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Upload failed.']);
    }

    /**
     * Delete an image from server disk and DB.
     */
    public function destroy($id)
    {
        $wedding = Auth::user()->wedding;
        $image = Gallery::where('id', $id)->where('wedding_id', $wedding->id)->firstOrFail();

        // 1. Delete file physically from public/uploads/gallery
        $physicalPath = public_path($image->image_path);
        if (File::exists($physicalPath)) {
            File::delete($physicalPath);
        }

        // 2. If this image was set as the hero cover, reset it to NULL
        if ($wedding->hero_image === $image->image_path) {
            $wedding->update(['hero_image' => null]);
        }

        // 3. Delete DB Record
        $image->delete();

        return redirect()->route('gallery.index')->with('status', 'Photo removed successfully!');
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