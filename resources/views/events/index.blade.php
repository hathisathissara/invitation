<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Wedding Events —Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <!-- Load custom CSS with cache buster -->
        <link rel="stylesheet" href="{{ asset('css/events.css') }}?v=1.3">
    </x-slot>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #16a34a !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(34,197,94,0.25);">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <!-- Page toolbar -->
    <div class="page-toolbar">
        <div>
            <h1>Wedding Events</h1>
            <p>Add every ceremony guests need to know about — Poruwa, Reception, Church, Homecoming.</p>
        </div>
        <button type="button" class="btn-open-add-event" data-bs-toggle="modal" data-bs-target="#addEventModal">
            <i class="fas fa-calendar-plus"></i> Add Event
        </button>
    </div>

    <!-- Events Grid (Using native CSS Grid) -->
    @if ($events->count() > 0)
        <div class="events-grid">
            @foreach ($events as $event)
                <div class="event-card">
                    <div class="event-card-name">{{ $event->event_name }}</div>
                    <div class="event-meta-row">
                        <i class="far fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($event->event_date_time)->format('l, d F Y') }}
                    </div>
                    <div class="event-meta-row">
                        <i class="far fa-clock"></i>
                        {{ \Carbon\Carbon::parse($event->event_date_time)->format('h:i A') }}
                    </div>
                    <div class="event-meta-row">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $event->location_name }}
                    </div>
                    <div class="event-card-actions">
                        @if ($event->google_map_link)
                        <a href="{{ $event->google_map_link }}" target="_blank" class="btn-map-sm" rel="noopener">
                            <i class="fas fa-map-marked-alt"></i> View Map
                        </a>
                        @else
                        <span style="font-size:0.75rem; color:#9ea3b0; font-style:italic;">No map link</span>
                        @endif

                        <!-- Delete Event Form -->
                        <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Remove this event?');" style="margin-left: auto;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-del-sm">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-events">
            <i class="fas fa-calendar-alt"></i>
            <p>No events added yet.<br>Click "Add Event" above to add your first — Poruwa, Reception, Church, Homecoming.</p>
        </div>
    @endif


    <!-- ================= ADD EVENT MODAL ================= -->
    <x-slot name="modals">
        <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEventModalLabel"><i class="fas fa-calendar-plus"></i> Add Wedding Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('events.store') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-field">
                                <label>Event Name <span style="color:#c9a96e;">*</span></label>
                                <input type="text" name="event_name" placeholder="e.g. Poruwa Ceremony, Reception" required>
                            </div>
                            <div class="form-field">
                                <label>Date & Time <span style="color:#c9a96e;">*</span></label>
                                <input type="datetime-local" name="event_date_time" required>
                            </div>
                            <div class="form-field">
                                <label>Venue / Location <span style="color:#c9a96e;">*</span></label>
                                <input type="text" name="location_name" placeholder="Hotel or hall name" value="{{ $defaultVenue }}" required>
                            </div>
                            <div class="form-field" style="margin-bottom:0;">
                                <label>Google Maps Link</label>
                                <input type="url" name="google_map_link" placeholder="https://maps.google.com/...">
                                <div class="hint">Paste the share link from Google Maps</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-add">
                                <i class="fas fa-plus"></i> Add Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modal triggers -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </x-slot>

</x-app-layout>