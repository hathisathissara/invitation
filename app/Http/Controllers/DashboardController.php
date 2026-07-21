<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wedding = $user->wedding;

        if (! $wedding) {
            return redirect()->route('register')->withErrors(['error' => 'No wedding found for this user.']);
        }

        // Guest Stats
        $totalGuests = $wedding->guests()->count();
        $openedInvites = $wedding->guests()->where('is_opened', 1)->count();
        $acceptedRsvp = $wedding->guests()->where('rsvp_status', 'accepted')->count();
        $rejectedRsvp = $wedding->guests()->where('rsvp_status', 'rejected')->count(); // "Not Attending" count

        // Checklist Progress Percentage
        $totalTasks = $wedding->tasks()->count();
        $completedTasks = $wedding->tasks()->where('is_completed', 1)->count();
        $taskPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // Recent 5 Guests
        $recentGuests = $wedding->guests()
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'wedding',
            'totalGuests',
            'openedInvites',
            'acceptedRsvp',
            'rejectedRsvp',
            'taskPercentage',
            'recentGuests'
        ));
    }
}
