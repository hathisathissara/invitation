<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use App\Models\Guest;
use App\Models\Event;
use App\Models\GuestGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class InvitationController extends Controller
{
    /**
     * Display the 3D Wax Seal Envelope gateway (invite.php).
     */
    public function invite($slug, Request $request)
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();
        
        App::setLocale($wedding->invite_language ?? 'en');

        $user = $wedding->user;
        $isOwner = (auth()->check() && auth()->id() === $user->id);
        $isAdmin = (auth()->check() && auth()->user()->role === 'admin');

        // Coming Soon page
        if ($user->status !== 'active' && !$isOwner && !$isAdmin) {
            $themeColors = $this->getThemeColors($wedding->template_name ?? 'premium_gold');
            return view('invitation.coming_soon', compact('wedding', 'themeColors'));
        }

        // Preview Mode
        if ($request->has('preview') && ($isOwner || $isAdmin)) {
            Session::put('guest_id', 0);
            Session::put('guest_name', 'Preview (Admin/Owner)');
            Session::put('invite_wedding_id', $wedding->id);
            return redirect()->route('invitation.view', $slug);
        }

        $justVerified = false;
        $verifiedViaForm = false;

        // Auto-login via Personalized Token Link [t]
        // NOTE: This runs on every GET to the page — including WhatsApp/Facebook/
        // Telegram/Google link-preview bots fetching the URL to build the share
        // card. So we ONLY set up the guest session here. 'is_opened' is marked
        // separately, via markOpened(), fired by JS only when the guest actually
        // taps the wax seal to open the envelope. See invite.blade.php.
        if ($request->has('t')) {
            $guest = Guest::where('wedding_id', $wedding->id)->where('invite_token', $request->t)->first();
            if ($guest) {
                Session::put('guest_id', $guest->id);
                Session::put('guest_name', $guest->name);
                Session::put('invite_wedding_id', $wedding->id);
                $justVerified = true;
                $verifiedViaForm = false;
            }
        }

        // Support Legacy WhatsApp [wa] links
        if ($request->has('wa') && !$justVerified) {
            $normalizedWa = $this->normalizeWhatsappNumber($request->wa);
            $guests = Guest::where('wedding_id', $wedding->id)->get();
            foreach ($guests as $g) {
                if ($this->normalizeWhatsappNumber($g->whatsapp_number) === $normalizedWa) {
                    Session::put('guest_id', $g->id);
                    Session::put('guest_name', $g->name);
                    Session::put('invite_wedding_id', $wedding->id);
                    $justVerified = true;
                    $verifiedViaForm = false;
                    break;
                }
            }
        }

        // If already verified via session
        if (Session::get('invite_wedding_id') === $wedding->id && Session::has('guest_id')) {
            $justVerified = true;
        }

        $themeColors = $this->getThemeColors($wedding->template_name ?? 'premium_gold');

        return view('invitation.invite', compact('wedding', 'themeColors', 'isOwner', 'justVerified', 'verifiedViaForm'));
    }

    /**
     * Mark the current session guest's invitation as "opened".
     *
     * IMPORTANT: This is only ever called via an AJAX request fired by the
     * front-end when the guest physically taps/clicks the wax seal to open
     * the envelope (see waxSeal click handler in invite.blade.php).
     *
     * It is deliberately NOT called from the invite() page-load route,
     * because that route is also hit by WhatsApp/Facebook/Telegram/Google
     * link-preview bots when generating the share card — and those bots
     * never execute JavaScript, so this endpoint is never reached by them.
     * That's what keeps the dashboard's "Opened" status accurate.
     */
    public function markOpened($slug, Request $request)
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();

        $guestId = Session::get('guest_id', 0);
        if (Session::get('invite_wedding_id') !== $wedding->id || $guestId <= 0) {
            return response()->json(['success' => false], 200);
        }

        $guest = Guest::where('id', $guestId)->where('wedding_id', $wedding->id)->first();
        if ($guest && !$guest->is_opened) {
            $guest->update(['is_opened' => true, 'opened_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle Manual Phone Number submission (Envelope gateway).
     */
    public function verifyPhone($slug, Request $request)
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();
        App::setLocale($wedding->invite_language ?? 'en');
        $normalizedInput = $this->normalizeWhatsappNumber($request->whatsapp_number);

        $guests = Guest::where('wedding_id', $wedding->id)->get();
        $matchedGuest = null;

        foreach ($guests as $g) {
            if ($this->normalizeWhatsappNumber($g->whatsapp_number) === $normalizedInput) {
                $matchedGuest = $g;
                break;
            }
        }

        if ($matchedGuest) {
            if (!$matchedGuest->is_opened) {
                $matchedGuest->update(['is_opened' => true, 'opened_at' => now()]);
            }

            Session::put('guest_id', $matchedGuest->id);
            Session::put('guest_name', $matchedGuest->name);
            Session::put('invite_wedding_id', $wedding->id);

            return view('invitation.invite', [
                'wedding' => $wedding,
                'themeColors' => $this->getThemeColors($wedding->template_name ?? 'premium_gold'),
                'isOwner' => (auth()->check() && auth()->id() === $wedding->user_id),
                'justVerified' => true,
                'verifiedViaForm' => true // form එකෙන් ආ නිසා direct unseal වේ
            ]);
        }

        return back()->withInput()->with('error', 'Sorry, this number is not on the guest list.');
    }

    /**
     * Display the actual customized wedding invitation template (view_invitation.php).
     */
    public function viewInvitation($slug, Request $request)
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();
        $user = $wedding->user;
        App::setLocale($wedding->invite_language ?? 'en'); 
        $isOwner = (auth()->check() && auth()->id() === $user->id);
        $isAdmin = (auth()->check() && auth()->user()->role === 'admin');

        // Security check
        if ($user->status !== 'active' && !$isOwner && !$isAdmin) {
            abort(403, 'This invitation is currently pending activation.');
        }

        // Verify guest is logged in (via envelope verification)
        if (Session::get('invite_wedding_id') !== $wedding->id && !$isOwner && !$isAdmin) {
            return redirect()->route('invitation.invite', $slug);
        }

        $guestId = Session::get('guest_id', 0);
        $guestName = Session::get('guest_name', 'Preview Guest');

        $hasGuestGallery = ($user->package === 'premium' || intval($user->has_guest_gallery) === 1);

        // Fetch data
        $events = $wedding->events()->orderBy('event_date_time', 'asc')->get();
        $galleryImages = $wedding->galleries()->orderBy('id', 'asc')->get();
        
        $guestImages = [];
        if ($hasGuestGallery) {
            $guestImages = $wedding->guestGalleries()->orderBy('id', 'desc')->get();
        }

        $currentGuest = null;
        if ($guestId > 0) {
            $currentGuest = Guest::where('id', $guestId)->first();
        }

        // Google Calendar & ICS generation
        $googleCalLink = '';
        $icsLink = route('calendar.download', $wedding->id); // calendar route එක

        if ($events->count() > 0) {
            $ev = $events->first();
            $start = date('Ymd\THis', strtotime($ev->event_date_time));
            $end = date('Ymd\THis', strtotime($ev->event_date_time) + 7200); // 2 hours duration
            $title = urlencode($wedding->bride_name . ' & ' . $wedding->groom_name . ' Wedding');
            $loc = urlencode($ev->location_name);
            $googleCalLink = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$start}/{$end}&location={$loc}";
        }

        $themeColors = $this->getThemeColors($wedding->template_name ?? 'premium_gold');

        // Resolve dynamic blade template (e.g. resources/views/templates/premium_gold.blade.php)
        $templateView = "templates.{$wedding->template_name}";
        if (!view()->exists($templateView)) {
            $templateView = 'templates.premium_gold'; // fallback
        }

        return view($templateView, compact(
            'wedding', 'events', 'galleryImages', 'guestImages', 'hasGuestGallery',
            'currentGuest', 'guestName', 'googleCalLink', 'icsLink', 'themeColors'
        ));
    }

    /**
     * Submit RSVP (via AJAX / Form post).
     */
    public function submitRsvp($slug, Request $request)
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();
        App::setLocale($wedding->invite_language ?? 'en');
        $guestId = Session::get('guest_id', 0);

        if ($guestId == 0) {
            return back()->with('status', 'This is a preview — RSVP is disabled.');
        }

        $request->validate([
            'rsvp_status' => ['required', 'string', 'in:accepted,rejected'],
            'guest_note' => ['nullable', 'string', 'max:500'],
        ]);

        $guest = Guest::where('id', $guestId)->where('wedding_id', $wedding->id)->firstOrFail();
        $guest->update([
            'rsvp_status' => $request->rsvp_status,
            'guest_note' => trim($request->guest_note),
        ]);

        return back()->with('status', 'Thank you! Your RSVP has been recorded.');
    }

    /**
     * Handle AJAX Guest shared photo upload.
     */
   /**
     * Handle AJAX Guest shared photo upload via Cloudinary API.
     */
    public function uploadPhoto($slug, Request $request)
    {
        $wedding = Wedding::where('slug', $slug)->firstOrFail();
        $guestName = Session::get('guest_name', 'Anonymous Guest');
        $user = $wedding->user;

        $hasGuestGallery = ($user->package === 'premium' || intval($user->has_guest_gallery) === 1);

        if (!$hasGuestGallery) {
            return response()->json(['success' => false, 'message' => 'Guest Gallery is not unlocked for this wedding.']);
        }

        if ($request->hasFile('guest_image')) {
            $file = $request->file('guest_image');
            
            // 💡 වඩාත්ම සුරක්ෂිත සහ නිවැරදි Mime Type encoder එක
            $mimeType = $file->getClientMimeType();
            $imageData = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $preset = env('CLOUDINARY_UPLOAD_PRESET');

            // Cloudinary slips folder එකට upload කිරීම [4]
            $response = \Illuminate\Support\Facades\Http::asForm()->post("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload", [
                'file' => $imageData,
                'upload_preset' => $preset,
                'folder' => 'lumus/guest_gallery',
            ]);

            if ($response->successful()) {
                $dbPath = $response->json('secure_url'); // Cloud URL
                $publicId = $response->json('public_id'); // Cloud Public ID

                $wedding->guestGalleries()->create([
                    'guest_name' => $guestName,
                    'image_path' => $dbPath,
                    'public_id' => $publicId, // 👈 DB එකට ID එකත් එකවර සේව් කරයි
                ]);

                return response()->json(['success' => true, 'message' => 'Moment shared successfully!']);
            }
        }

        return response()->json(['success' => false, 'message' => 'Failed to save image to cloud.']);
    }
    /* ================= DYNAMIC CONTROLLER COLOR HELPERS ================= */

    private function getThemeColors($themeName)
    {
        $theme_palettes = [
            'premium_gold'     => ['primary' => '#8a6520', 'accent' => '#c9a05a',  'accent_light' => '#e8d5a3', 'paper' => '#fdfaf5', 'paper2' => '#f9f5ee', 'ink' => '#241b10'],
            'minimal_light'    => ['primary' => '#8f6f42', 'accent' => '#b8935a',  'accent_light' => '#ded0b8', 'paper' => '#faf9f6', 'paper2' => '#ffffff', 'ink' => '#111111'],
            'terracotta_bloom' => ['primary' => '#8f4526', 'accent' => '#c1633d',  'accent_light' => '#e3a880', 'paper' => '#faf5ec', 'paper2' => '#f4ece0', 'ink' => '#362b21'],
            'plum_parchment'   => ['primary' => '#4a2c3b', 'accent' => '#8a9a7e',  'accent_light' => '#a97e93', 'paper' => '#f8f2e9', 'paper2' => '#f0e6d6', 'ink' => '#2e2a28'],
            'floral_garden'    => ['primary' => '#a15873', 'accent' => '#8fac7a',  'accent_light' => '#e7b9cb', 'paper' => '#fffaf7', 'paper2' => '#fbeef1', 'ink' => '#3c2f34'],
            'beach_tropical'   => ['primary' => '#9c7e3f', 'accent' => '#c9a961',  'accent_light' => '#e8d9ac', 'paper' => '#f8f4ea', 'paper2' => '#f1e9d6', 'ink' => '#171a26'],
            'rustic_boho'      => ['primary' => '#9c6b4a', 'accent' => '#c98f6b',  'accent_light' => '#e7c9a0', 'paper' => '#faf9f6', 'paper2' => '#f1eee6', 'ink' => '#111111'],
            'royal_classic'    => ['primary' => '#1c2340', 'accent' => '#c6a15b',  'accent_light' => '#ddc48b', 'paper' => '#faf7f0', 'paper2' => '#f1e9d8', 'ink' => '#10142a'],
            'indian_royal'     => ['primary' => '#6e1626', 'accent' => '#d4af37',  'accent_light' => '#edc873', 'paper' => '#fff8ec', 'paper2' => '#fbecc9', 'ink' => '#2e0a10'],
        ];

        $pal = $theme_palettes[$themeName] ?? $theme_palettes['premium_gold'];

        return [
            'c_env_light' => $this->tc_lighten($pal['primary'], 0.28),
            'c_env_mid'   => $this->tc_darken($pal['primary'], 0.05),
            'c_env_dark'  => $this->tc_darken($pal['primary'], 0.55),
            'c_accent'    => $pal['accent'],
            'c_accent_light' => $pal['accent_light'],
            'c_accent_rgb' => $this->tc_rgbstr($pal['accent']),
            'c_accent_light_rgb' => $this->tc_rgbstr($pal['accent_light']),
            'c_paper'     => $pal['paper'],
            'c_paper2'    => $pal['paper2'],
            'c_ink'       => $pal['ink'],
            'c_ink_rgb'   => $this->tc_rgbstr($pal['ink']),
            'c_paper_accent_strong' => $this->tc_darken($pal['accent'], 0.55),
            'c_paper_accent_mid'    => $this->tc_darken($pal['accent'], 0.35),
            'c_paper_accent_rgb'    => $this->tc_rgbstr($this->tc_darken($pal['accent'], 0.45)),
            'c_seal_ink'  => $this->tc_darken($pal['accent'], 0.75),
            'hex_accent'  => ltrim($pal['accent'], '#'),
            'hex_accent_light' => ltrim($pal['accent_light'], '#'),
            'hex_primary' => ltrim($pal['primary'], '#'),
            'hex_ink'     => ltrim($pal['ink'], '#'),
        ];
    }

    private function tc_hex2rgb($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function tc_rgb2hex($rgb) {
        return sprintf('#%02x%02x%02x', max(0, min(255, round($rgb[0]))), max(0, min(255, round($rgb[1]))), max(0, min(255, round($rgb[2]))));
    }

    private function tc_mix($hex1, $hex2, $amount) {
        $a = $this->tc_hex2rgb($hex1); $b = $this->tc_hex2rgb($hex2);
        return $this->tc_rgb2hex([
            $a[0] + ($b[0] - $a[0]) * $amount,
            $a[1] + ($b[1] - $a[1]) * $amount,
            $a[2] + ($b[2] - $a[2]) * $amount,
        ]);
    }

    private function tc_darken($hex, $amount) { return $this->tc_mix($hex, '#000000', $amount); }
    private function tc_lighten($hex, $amount) { return $this->tc_mix($hex, '#ffffff', $amount); }
    private function tc_rgbstr($hex) { $r = $this->tc_hex2rgb($hex); return round($r[0]) . ',' . round($r[1]) . ',' . round($r[2]); }

    private function normalizeWhatsappNumber($value) {
        $value = trim((string) $value);
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') return '';

        if (strlen($digits) > 10 && substr($digits, 0, 2) === '94') {
            $digits = '0' . substr($digits, 2);
        } elseif (strlen($digits) === 9) {
            $digits = '0' . $digits;
        }

        return $digits;
    }
}