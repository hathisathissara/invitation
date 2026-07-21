<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of wedding events.
     */
    public function index()
    {
        $wedding = Auth::user()->wedding;

        // Wedding එකට අදාල events ටික දිනය අනුව පිළිවෙලට ගන්නවා
        $events = $wedding->events()->orderBy('event_date_time', 'asc')->get();

        // Form එකට auto-fill වෙන්න default venue එක ගන්නවා
        $defaultVenue = $wedding->venue;

        return view('events.index', compact('events', 'defaultVenue'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => ['required', 'string', 'max:100'],
            'event_date_time' => ['required', 'date'],
            'location_name' => ['required', 'string', 'max:255'],
            'google_map_link' => ['nullable', 'url'], // Google Maps Link එක URL එකක්ද කියලා බලනවා
        ]);

        $wedding = Auth::user()->wedding;

        $wedding->events()->create([
            'event_name' => trim($request->event_name),
            'event_date_time' => $request->event_date_time,
            'location_name' => trim($request->location_name),
            'google_map_link' => $request->google_map_link ? trim($request->google_map_link) : null,
        ]);

        return redirect()->route('events.index')->with('status', 'Wedding event added successfully!');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        // Security check: තමන්ගේම event එකක්ද කියලා බලනවා
        if ($event->wedding_id !== Auth::user()->wedding->id) {
            abort(403, 'Unauthorized action.');
        }

        $event->delete();

        return redirect()->route('events.index')->with('status', 'Event deleted successfully!');
    }
}
