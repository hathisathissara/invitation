<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class GuestController extends Controller
{
     public function index()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        // Package Seat Limits Checking
        $package = $user->package ?? 'basic';
        $guestLimit = 150;
        if ($package === 'standard') {
            $guestLimit = 300;
        } elseif ($package === 'premium') {
            $guestLimit = 999999;
        }

        // Fetch guests
        $guests = $wedding->guests()->orderBy('id', 'desc')->get();

        // Safety Backfill: කලින් Token නැතුව ඇඩ් වුණු අය සිටීනම් Auto-generate කරනවා
        foreach ($guests as $guest) {
            if (empty($guest->invite_token)) {
                $guest->update([
                    'invite_token' => $this->generateInviteToken()
                ]);
            }
        }

        // Calculate total seats
        $totalSeats = $wedding->guests()->sum('seats_reserved');

        // Dynamic Invitation Link generation
        $inviteUrl = url('/invitation/' . $wedding->slug);

        return view('guests.index', compact('guests', 'totalSeats', 'guestLimit', 'package', 'inviteUrl'));
    }

    /**
     * Add a new Guest.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'category' => ['required', 'string'],
            'side' => ['required', 'string'],
            'seats_reserved' => ['required', 'integer', 'min:1'],
        ]);

        $user = Auth::user();
        $wedding = $user->wedding;

        // Plan checks
        $package = $user->package ?? 'basic';
        $guestLimit = 150;
        if ($package === 'standard') $guestLimit = 300;
        elseif ($package === 'premium') $guestLimit = 999999;

        $currentSeats = $wedding->guests()->sum('seats_reserved') ?? 0;
        $requestedSeats = intval($request->seats_reserved);

        if (($currentSeats + $requestedSeats) > $guestLimit) {
            return back()->withErrors(['limit' => "Your " . ucfirst($package) . " plan allows up to {$guestLimit} seats. Please upgrade."]);
        }

        // Whatsapp Number Normalization
        $whatsappNormalized = $this->normalizeWhatsappNumber($request->whatsapp_number);

        // Check if phone already exists
        if (!empty($whatsappNormalized)) {
            $exists = $wedding->guests()->where('whatsapp_number', $whatsappNormalized)->exists();
            if ($exists) {
                return back()->withErrors(['whatsapp' => 'This WhatsApp number is already in the guest list.']);
            }
        }

        // Create Guest with token
        $wedding->guests()->create([
            'name' => trim($request->name),
            'whatsapp_number' => $whatsappNormalized,
            'category' => $request->category,
            'side' => $request->side,
            'seats_reserved' => $requestedSeats,
            'invite_token' => $this->generateInviteToken(),
        ]);

        return redirect()->route('guests.index')->with('status', 'Guest added successfully!');
    }

    /**
     * Delete Guest.
     */
    public function destroy(Guest $guest)
    {
        if ($guest->wedding_id !== Auth::user()->wedding->id) {
            abort(403);
        }

        $guest->delete();

        return redirect()->route('guests.index')->with('status', 'Guest removed successfully!');
    }

    /**
     * AJAX action: Mark Sent.
     */
    public function markSent($id)
    {
        $wedding = Auth::user()->wedding;
        $guest = Guest::where('id', $id)->where('wedding_id', $wedding->id)->first();

        if ($guest) {
            $guest->update([
                'is_sent' => true,
                'sent_at' => now(),
            ]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * AJAX action: Fetch Live Status (Real-time sync).
     */
    public function liveStatus()
    {
        $wedding = Auth::user()->wedding;
        $guests = Guest::where('wedding_id', $wedding->id)
            ->select('id', 'name', 'is_opened', 'opened_at', 'is_sent', 'sent_at', 'rsvp_status', 'guest_note')
            ->get();

        return response()->json(['guests' => $guests]);
    }

    /* ================= HELPER METHODS ================= */

    private function generateInviteToken()
    {
        do {
            $token = bin2hex(random_bytes(6)); // 12 characters unique token
        } while (Guest::where('invite_token', $token)->exists());

        return $token;
    }

    private function normalizeWhatsappNumber($value)
    {
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
