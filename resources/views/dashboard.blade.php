<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Dashboard — Lumos Studio
    </x-slot>

    <!-- Page Specific CSS Slot -->
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/dashboard_new.css') }}?v=1.1">
    </x-slot>

    <div class="dashboard-container">
        
       <!-- ================= ROW 1: WELCOME & COUNTDOWN BANNER (NESTED) ================= -->
        <!-- දැන් Countdown එක ලස්සනට Welcome Card එක ඇතුලෙන්ම දකුණු පැත්තෙන් පෙන්වයි -->
        <div class="welcome-card">
            
            <!-- Left Welcome Content -->
            <div class="welcome-content">
                <span class="welcome-badge">WEDDING DASHBOARD</span>
                <h1 class="welcome-heading">Welcome back, {{ $wedding->bride_name }} & {{ $wedding->groom_name }} 👋</h1>
                <p class="welcome-text">Your planning hub is ready. Review guest activity, share the invitation, and keep momentum going toward your celebration.</p>
                
                <div class="welcome-pills">
                    <span class="pill-badge"><i class="fas fa-users"></i> {{ $totalGuests }} guests</span>
                    <span class="pill-badge"><i class="fas fa-tasks"></i> {{ $taskPercentage }}% tasks done</span>
                    <span class="pill-badge"><i class="fas fa-heart"></i> RSVP progress live</span>
                </div>
            </div>

            <!-- Right Countdown Card nested inside welcome card -->
            <div class="countdown-card">
                <span class="countdown-label">Counting down to your day</span>
                <div class="countdown-timer" id="live-countdown">Calculating...</div>
                <span class="countdown-sub">Days to go</span>
            </div>

        </div>

        <!-- ================= ROW 2: INVITATION LINK PANEL ================= -->
        <div class="invitation-link-panel">
            <div class="link-info-col">
                <h3 class="link-panel-title">
                    <i class="fas fa-link"></i> Your Invitation Link — Share this with all guests
                </h3>
                <p class="invitation-url" id="invitation-url-text">{{ url('/invitation/' . $wedding->slug) }}</p>
            </div>
            <div class="link-action-col">
                <a href="{{ route('invitation.invite', ['slug' => $wedding->slug, 'preview' => 1]) }}" target="_blank" class="btn-preview-dark">
                    <i class="far fa-eye"></i> Preview
                </a>
                <button class="btn-copy-link" onclick="copyInviteLink('{{ url('/invitation/' . $wedding->slug) }}')">
                    <i class="fas fa-link"></i> Copy Link
                </button>
            </div>
        </div>

        <!-- ================= ROW 3: QUICK ACTIONS GRID (4 Cards) ================= -->
        <div class="quick-actions-grid">
            <a href="{{ route('guests.index') }}" class="q-action-card">
                <div class="q-icon-wrap bg-orange"><i class="fas fa-user-plus"></i></div>
                <div class="q-info">
                    <h4>Add Guest</h4>
                    <p>Grow your guest list</p>
                </div>
            </a>
            <a href="{{ route('events.index') }}" class="q-action-card">
                <div class="q-icon-wrap bg-blue"><i class="fas fa-calendar-plus"></i></div>
                <div class="q-info">
                    <h4>Add Event</h4>
                    <p>Poruwa, Reception & more</p>
                </div>
            </a>
            <a href="{{ route('gallery.index') }}" class="q-action-card">
                <div class="q-icon-wrap bg-green"><i class="fas fa-images"></i></div>
                <div class="q-info">
                    <h4>Upload Photos</h4>
                    <p>Share your love story</p>
                </div>
            </a>
            <a href="{{ route('tasks.index') }}" class="q-action-card">
                <div class="q-icon-wrap bg-gold"><i class="fas fa-tasks"></i></div>
                <div class="q-info">
                    <h4>Checklist</h4>
                    <p>{{ $taskPercentage }}% planning complete</p>
                </div>
            </a>
        </div>

        <!-- ================= ROW 4: BIG STATS CARDS ================= -->
        <div class="stats-cards-row">
            <div class="widget-card stat-card">
                <div class="stat-top">
                    <div class="stat-icon-circle bg-gray-light"><i class="fas fa-user-friends"></i></div>
                </div>
                <div class="stat-number">{{ $totalGuests }}</div>
                <div class="stat-text">Total Guests</div>
            </div>
            <div class="widget-card stat-card">
                <div class="stat-top">
                    <div class="stat-icon-circle bg-blue-light"><i class="fas fa-envelope-open-text"></i></div>
                </div>
                <div class="stat-number">{{ $openedInvites }}</div>
                <div class="stat-text">Opened Invitation</div>
            </div>
            <div class="widget-card stat-card">
                <div class="stat-top">
                    <div class="stat-icon-circle bg-green-light"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-number text-success">{{ $acceptedRsvp }}</div>
                <div class="stat-text">Attending (RSVP)</div>
            </div>
            <div class="widget-card stat-card">
                <div class="stat-top">
                    <div class="stat-icon-circle bg-red-light"><i class="fas fa-times-circle"></i></div>
                </div>
                <div class="stat-number text-danger">{{ $rejectedRsvp }}</div>
                <div class="stat-text">Not Attending</div>
            </div>
        </div>

        <!-- ================= ROW 5: PLANNING PROGRESS & RECENT GUESTS ================= -->
        <div class="bottom-widgets-grid">
            
            <!-- Bottom Left: Planning Progress -->
            <div class="widget-card flex-col">
                <h3 class="widget-title">Planning Progress</h3>
                <div class="progress-box">
                    <div class="progress-big-number">{{ $taskPercentage }}%</div>
                    <div class="progress-subtext">{{ $taskPercentage }}% tasks done</div>
                </div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width: {{ $taskPercentage }}%;"></div>
                </div>
                <a href="{{ route('tasks.index') }}" class="view-checklist-link">View Checklist →</a>
            </div>

            <!-- Bottom Right: Recent Guests Table -->
            <div class="widget-card flex-col">
                <div class="widget-title-row">
                    <h3 class="widget-title">Recent Guests</h3>
                    <a href="{{ route('guests.index') }}" class="view-all-link">View All →</a>
                </div>

                <div class="recent-table-wrap">
                    @if($recentGuests->count() > 0)
                        <table class="recent-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>WhatsApp</th>
                                    <th>Opened</th>
                                    <th>RSVP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentGuests as $g)
                                    <tr>
                                        <td class="td-name">{{ $g->name }}</td>
                                        <td class="td-phone">{{ $g->whatsapp_number }}</td>
                                        <td>
                                            @if($g->is_opened)
                                                <span class="status-badge opened"><i class="fas fa-check"></i> Opened</span>
                                            @else
                                                <span class="status-badge unopened">Unopened</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($g->rsvp_status === 'accepted')
                                                <span class="status-badge attending">Attending</span>
                                            @elseif($g->rsvp_status === 'rejected')
                                                <span class="status-badge declined">Declined</span>
                                            @else
                                                <span class="status-badge pending">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="no-recent-data">No recent guest activity yet.</div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <!-- Live Dynamic Countdown Timer JS -->
    <x-slot name="scripts">
        <script>
        function updateLiveCountdown() {
            const weddingDateStr = "{{ $wedding->wedding_date }}"; // format: YYYY-MM-DD
            if(!weddingDateStr) {
                document.getElementById('live-countdown').textContent = "Not Set";
                return;
            }
            
            const weddingTime = new Date(weddingDateStr + "T00:00:00").getTime();
            const now = new Date().getTime();
            const distance = weddingTime - now;

            if (distance < 0) {
                document.getElementById('live-countdown').textContent = "Happened! ❤️";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Double digit formatting
            const d = days + "d";
            const h = String(hours).padStart(2, '0') + "h";
            const m = String(minutes).padStart(2, '0') + "m";
            
            document.getElementById('live-countdown').textContent = `${d} ${h} ${m}`;
        }
        
        updateLiveCountdown();
        setInterval(updateLiveCountdown, 60000); // Update every minute
        </script>
    </x-slot>

</x-app-layout>