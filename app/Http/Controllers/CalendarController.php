<?php

namespace App\Http\Controllers;

use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Generate and download .ics file for Wedding Events.
     */
    public function download($id)
    {
        $wedding = Wedding::with('events')->findOrFail($id);
        $events = $wedding->events;

        if ($events->isEmpty()) {
            return back()->with('error', 'No events found to add to calendar.');
        }

        // VCALENDAR Format Start
        $vCalendar = "BEGIN:VCALENDAR\r\n";
        $vCalendar .= "VERSION:2.0\r\n";
        $vCalendar .= "PRODID:-//Lumus Studio//Wedding Invitation//EN\r\n";

        // Loop through events and add them
        foreach ($events as $event) {
            // Convert time to UTC for universal calendar compatibility
            $start = Carbon::parse($event->event_date_time)->setTimezone('UTC')->format('Ymd\THis\Z');
            $end = Carbon::parse($event->event_date_time)->addHours(2)->setTimezone('UTC')->format('Ymd\THis\Z'); // Assuming 2 hours duration
            
            $uid = md5($event->id . $wedding->id) . "@lumusstudio.com";
            $summary = $event->event_name . " - " . $wedding->bride_name . " & " . $wedding->groom_name;

            $vCalendar .= "BEGIN:VEVENT\r\n";
            $vCalendar .= "UID:" . $uid . "\r\n";
            $vCalendar .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
            $vCalendar .= "DTSTART:" . $start . "\r\n";
            $vCalendar .= "DTEND:" . $end . "\r\n";
            $vCalendar .= "SUMMARY:" . $this->escapeIcalString($summary) . "\r\n";
            $vCalendar .= "LOCATION:" . $this->escapeIcalString($event->location_name) . "\r\n";
            $vCalendar .= "END:VEVENT\r\n";
        }

        // VCALENDAR Format End
        $vCalendar .= "END:VCALENDAR\r\n";

        // Create a clean filename
        $filename = "Wedding_" . Str::slug($wedding->bride_name . '_' . $wedding->groom_name) . ".ics";

        // Return as a downloadable text/calendar file
        return response($vCalendar)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Escape special characters for iCalendar format.
     */
    private function escapeIcalString($string)
    {
        $string = str_replace('\\', '\\\\', $string);
        $string = str_replace(',', '\,', $string);
        $string = str_replace(';', '\;', $string);
        $string = str_replace("\n", '\n', $string);
        return $string;
    }
}