<x-app-layout>

    <!-- Page Title Slot -->
    <x-slot name="title">
        Guest List — Lumos Studio
    </x-slot>

    <!-- Page CSS Slot -->
    <x-slot name="styles">
        <!-- Load Bootstrap CSS for Modals -->
        <link rel="stylesheet" href="{{ asset('css/guests.css') }}">
    </x-slot>

    <!-- Success & Error Alert Messages -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #86efac !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-danger border-opacity-20 rounded-3 mb-4" style="color: #fca5a5 !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px;">
            <ul style="list-style: none; margin: 0; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-triangle me-2"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Page toolbar -->
    <div class="page-toolbar">
        <div>
            <h1>Guest List</h1>
            <p>Manage invitations, track RSVPs, and keep your seat count on point.</p>
        </div>
        <button type="button" class="btn-open-add-guest" data-bs-toggle="modal" data-bs-target="#addGuestModal">
            <i class="fas fa-user-plus"></i> Add New Guest
        </button>
    </div>

    <!-- Guest Data Table/Card Container -->
    <div class="row g-3">
        <div class="col-12">
            <!-- Header Filter Bar -->
            <div class="guest-list-header">
                <div class="guest-count">
                    Total Guests (Seats)
                    <span id="visible-count">{{ $totalSeats }}</span>
                    <small style="color:#9ea3b0; font-size:0.75rem; margin-left:5px; font-weight: 500;">
                        (from {{ $guests->count() }} invitations)
                    </small>
                </div>
                <div class="search-filter-bar">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-input" id="guest-search" placeholder="Search guests...">
                    </div>
                    <select class="filter-select" id="filter-cat">
                        <option value="">All Categories</option>
                        <option value="Family">Family</option>
                        <option value="Friends">Friends</option>
                        <option value="Office">Office</option>
                        <option value="VIP">VIP</option>
                    </select>
                    <select class="filter-select" id="filter-rsvp">
                        <option value="">All RSVP</option>
                        <option value="accepted">Attending</option>
                        <option value="rejected">Declined</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>

            <div class="guest-table-wrap">
                @if ($guests->count() > 0)
                
                <!-- Desktop Table View -->
                <div style="overflow-x:auto;" class="guest-table-scroll">
                    <table class="guest-table" id="guest-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>WhatsApp & Seats</th>
                                <th>Category</th>
                                <th>Status (Opened / Sent)</th>
                                <th>RSVP</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guests as $g)
                            <tr
                                data-id="{{ $g->id }}"
                                data-name="{{ strtolower($g->name) }}"
                                data-cat="{{ $g->category }}"
                                data-rsvp="{{ $g->rsvp_status }}"
                                data-seats="{{ $g->seats_reserved ?? 1 }}"
                            >
                                <td class="guest-name-cell">
                                    {{ $g->name }}
                                    
                                    @if (!empty($g->guest_note))
                                        <div class="guest-note-box">
                                            <i class="fas fa-comment-dots" style="color: #c9a96e;"></i>
                                            <strong>Note:</strong> "{{ $g->guest_note }}"
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="guest-phone">{{ $g->whatsapp_number }}</span>
                                    <br>
                                    <small style="color:#9ea3b0; font-size:0.78rem; margin-top:4px; display:inline-block; font-weight: 600;">
                                        <i class="fas fa-chair" style="color:#c9a96e; margin-right:4px;"></i> 
                                        Seats: {{ $g->seats_reserved ?? 1 }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $catClass = strtolower($g->category);
                                        $sideClass = strtolower(str_replace("'s", '', $g->side)) . '-side';
                                    @endphp
                                    <span class="badge badge-{{ $catClass }}">{{ $g->category }}</span>
                                    <br>
                                    <span class="badge badge-{{ $sideClass }}" style="margin-top:4px;">{{ $g->side }}</span>
                                </td>
                                
                                <td class="opened-status-cell">
                                    @if ($g->is_opened)
                                        <span class="badge badge-opened"><i class="fas fa-check-double"></i> Opened</span>
                                        @if ($g->opened_at)
                                        <br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">
                                            {{ \Carbon\Carbon::parse($g->opened_at)->format('d M h:i A') }}
                                        </small>
                                        @endif
                                    @elseif ($g->is_sent)
                                        <span class="badge badge-sent"><i class="fas fa-paper-plane"></i> Sent</span>
                                        @if ($g->sent_at)
                                        <br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">
                                            {{ \Carbon\Carbon::parse($g->sent_at)->format('d M h:i A') }}
                                        </small>
                                        @endif
                                    @else
                                        <span class="badge badge-not-sent">Not sent</span>
                                    @endif
                                </td>
                                
                                <td class="rsvp-status-cell">
                                    @if ($g->rsvp_status == 'accepted')
                                        <span class="badge badge-attending"><i class="fas fa-check"></i> Attending</span>
                                    @elseif ($g->rsvp_status == 'rejected')
                                        <span class="badge badge-declined"><i class="fas fa-times"></i> Declined</span>
                                    @else
                                        <span class="badge badge-pending-rsvp">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <!-- WhatsApp Direct Send trigger -->
                                    @php
                                        // Generate WhatsApp compatible international number format (e.g. 9477xxxxxx)
                                        $digits = preg_replace('/\D+/', '', $g->whatsapp_number);
                                        $guest_wa_intl = '';
                                        if (!empty($digits)) {
                                            $guest_wa_intl = (substr($digits, 0, 1) === '0') ? '94' . substr($digits, 1) : ((substr($digits, 0, 2) !== '94') ? '94' . $digits : $digits);
                                        }
                                    @endphp
                                    @if (!empty($guest_wa_intl) && !empty($inviteUrl))
                                        <a href="#"
                                           class="btn-wa-send"
                                           data-id="{{ $g->id }}"
                                           data-phone="{{ $guest_wa_intl }}"
                                           data-token="{{ $g->invite_token }}"
                                           data-guest-name="{{ $g->name }}"
                                           title="Send personalized invitation to {{ $g->name }} via WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                    @else
                                        <span class="btn-wa-send disabled" title="No valid WhatsApp number">
                                            <i class="fab fa-whatsapp"></i>
                                        </span>
                                    @endif

                                    <!-- Delete Guest Form -->
                                    <form action="{{ route('guests.destroy', $g) }}" method="POST" onsubmit="return confirm('Remove {{ addslashes($g->name) }} from the guest list?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-del">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards Responsive View -->
                <div class="guest-cards-mobile">
                    @foreach ($guests as $g)
                    @php
                        $digits = preg_replace('/\D+/', '', $g->whatsapp_number);
                        $guest_wa_intl = '';
                        if (!empty($digits)) {
                            $guest_wa_intl = (substr($digits, 0, 1) === '0') ? '94' . substr($digits, 1) : ((substr($digits, 0, 2) !== '94') ? '94' . $digits : $digits);
                        }
                        $catClass = strtolower($g->category);
                        $sideClass = strtolower(str_replace("'s", '', $g->side)) . '-side';
                        $seats_count = intval($g->seats_reserved ?? 1);
                        
                        // Personal Guest Link generator
                        $separator = strpos($inviteUrl, '?') !== false ? '&' : '?';
                        $card_personal_link = $inviteUrl . $separator . 't=' . urlencode($g->invite_token);
                    @endphp
                    <div class="guest-card"
                         data-id="{{ $g->id }}"
                         data-name="{{ strtolower($g->name) }}"
                         data-cat="{{ $g->category }}"
                         data-rsvp="{{ $g->rsvp_status }}"
                         data-seats="{{ $seats_count }}">

                        <div class="guest-card-top">
                            <div class="guest-card-name">
                                {{ $g->name }}
                                @if (!empty($g->guest_note))
                                    <div class="guest-note-box">
                                        <i class="fas fa-comment-dots" style="color:#c9a96e;"></i>
                                        <strong>Note:</strong> "{{ $g->guest_note }}"
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Mobile Delete Form -->
                            <form action="{{ route('guests.destroy', $g) }}" method="POST" onsubmit="return confirm('Remove {{ addslashes($g->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn-del-top">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>

                        <div class="guest-card-phone">
                            <i class="fas fa-phone-alt"></i> {{ $g->whatsapp_number }}
                        </div>

                        <div class="guest-card-badges">
                            @if ($g->rsvp_status == 'accepted')
                                <span class="badge badge-attending"><i class="fas fa-check"></i> Attending</span>
                            @elseif ($g->rsvp_status == 'rejected')
                                <span class="badge badge-declined"><i class="fas fa-times"></i> Declined</span>
                            @else
                                <span class="badge badge-pending-rsvp">Pending</span>
                            @endif
                            
                            <span class="guest-card-delivery-status">
                                @if ($g->is_opened)
                                    <span class="mini-status"><i class="fas fa-check-double" style="color:#16a34a;"></i> Opened</span>
                                @elseif ($g->is_sent)
                                    <span class="mini-status"><i class="fas fa-paper-plane" style="color:#0284c7;"></i> Sent</span>
                                @else
                                    <span class="mini-status"><i class="far fa-circle"></i> Not sent</span>
                                    <span class="mini-status"><i class="fas fa-eye-slash"></i> Not opened</span>
                                @endif
                            </span>
                        </div>

                        <div class="guest-card-meta">
                            To You: <strong>{{ $seats_count }} head{{ $seats_count > 1 ? 's' : '' }}</strong>
                            &nbsp;·&nbsp; {{ $g->category }}
                            &nbsp;·&nbsp; {{ $g->side }}
                        </div>

                        <div class="guest-card-actions">
                            @if (!empty($guest_wa_intl) && !empty($inviteUrl))
                                <a href="#"
                                   class="btn-wa-send btn-wa-send-full"
                                   data-id="{{ $g->id }}"
                                   data-phone="{{ $guest_wa_intl }}"
                                   data-token="{{ $g->invite_token }}"
                                   data-guest-name="{{ $g->name }}">
                                    <i class="fab fa-whatsapp"></i> Send via WhatsApp
                                </a>
                            @else
                                <span class="btn-wa-send btn-wa-send-full disabled">
                                    <i class="fab fa-whatsapp"></i> No valid number
                                </span>
                            @endif

                            <button type="button"
                                    class="icon-btn-mobile icon-btn-note"
                                    title="Guest note"
                                    data-note="{{ $g->guest_note ?? '' }}">
                                <i class="far fa-comment"></i>
                            </button>

                            <button type="button"
                                    class="icon-btn-mobile icon-btn-copy"
                                    title="Copy invitation link"
                                    data-link="{{ $card_personal_link }}">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                @else
                <div class="empty-state">
                    <div><i class="fas fa-users"></i></div>
                    <p>No guests yet. Click "Add New Guest" above to get started.</p>
                </div>
                @endif
            </div>
        </div>
    </div>


    <!-- ================= ADD GUEST MODAL ================= -->
    <x-slot name="modals">
        <div class="modal fade" id="addGuestModal" tabindex="-1" aria-labelledby="addGuestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGuestModalLabel"><i class="fas fa-user-plus"></i> Add New Guest</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('guests.store') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-field">
                                <label>Guest Name <span style="color:#c9a96e;">*</span></label>
                                <input type="text" name="name" placeholder="e.g. Kamal Perera" required>
                            </div>
                            <div class="form-field">
                                <label>WhatsApp Number <span style="color:#c9a96e;">*</span></label>
                                <input type="text" name="whatsapp_number" placeholder="e.g. 0771234567" required>
                                <div class="hint">Guests enter this to open their invitation</div>
                            </div>
                            <div class="form-field">
                                <label>Category</label>
                                <select name="category">
                                    <option value="Family">👨‍👩‍👧 Family</option>
                                    <option value="Friends">👥 Friends</option>
                                    <option value="Office">💼 Office</option>
                                    <option value="VIP">⭐ VIP</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label>Side</label>
                                <select name="side">
                                    <option value="Bride">Bride's Side</option>
                                    <option value="Groom">Groom's Side</option>
                                    <option value="Both">Both Sides</option>
                                </select>
                            </div>
                            <div class="form-field" style="margin-bottom:0;">
                                <label>Seats Reserved (ආසන ගණන)</label>
                                <input type="number" name="seats_reserved" value="1" min="1" required>
                                <div class="hint">වෙන් කර ඇති උපරිම ආසන ගණන</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-add-guest">
                                <i class="fas fa-plus" style="margin-right:6px;"></i> Add to Guest List
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </x-slot>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modals -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <script>
        function filterGuests() {
            const search = document.getElementById('guest-search').value.toLowerCase();
            const cat    = document.getElementById('filter-cat').value;
            const rsvp   = document.getElementById('filter-rsvp').value;
            const rows   = document.querySelectorAll('#guest-table tbody tr');
            const cards  = document.querySelectorAll('.guest-cards-mobile .guest-card');
            let visibleSeats  = 0;
            let countedIds    = new Set();

            function matches(el) {
                const name    = el.dataset.name || '';
                const elCat   = el.dataset.cat  || '';
                const elRsvp  = el.dataset.rsvp || '';

                const matchSearch = name.includes(search);
                const matchCat    = !cat  || elCat  === cat;
                const matchRsvp   = !rsvp || elRsvp === rsvp;

                return matchSearch && matchCat && matchRsvp;
            }

            rows.forEach(row => {
                const show = matches(row);
                row.style.display = show ? '' : 'none';
                if (show && !countedIds.has(row.dataset.id)) {
                    visibleSeats += parseInt(row.dataset.seats) || 1;
                    countedIds.add(row.dataset.id);
                }
            });

            cards.forEach(card => {
                const show = matches(card);
                card.style.display = show ? '' : 'none';
                if (show && !countedIds.has(card.dataset.id)) {
                    visibleSeats += parseInt(card.dataset.seats) || 1;
                    countedIds.add(card.dataset.id);
                }
            });

            document.getElementById('visible-count').textContent = visibleSeats;
        }

        document.getElementById('guest-search').addEventListener('input', filterGuests);
        document.getElementById('filter-cat').addEventListener('change', filterGuests);
        document.getElementById('filter-rsvp').addEventListener('change', filterGuests);

        // WhatsApp Direct Link Builder Logic
        document.querySelectorAll('.btn-wa-send').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                const guestId = this.getAttribute('data-id');
                const phone = this.getAttribute('data-phone');
                const token = this.getAttribute('data-token');
                const guestName = this.getAttribute('data-guest-name');
                const coupleName = "{{ auth()->user()->name }}";
                
                const inviteBaseUrl = "{{ $inviteUrl }}";
                const separator = inviteBaseUrl.includes('?') ? '&' : '?';
                const personalLink = inviteBaseUrl + separator + 't=' + encodeURIComponent(token);
                
                const flower = "\u{1F338}"; 
                const heart = "\u{2764}\u{FE0F}";
                
                const personalMessage = `Hi ${guestName} ${flower}\n\n`
                    + `With so much love and happiness in our hearts, we're excited to invite you to celebrate the invitation of our journey together - ${coupleName}\n\n`
                    + `It would truly mean the world to us to have you with us on this special day\n\n`
                    + `Invitation: ${personalLink}\n\n`
                    + `We can't wait to celebrate, laugh, and create beautiful memories with you! ${heart}`;
                    
                const encodedMessage = encodeURIComponent(personalMessage);
                
                // 1. AJAX request to backend to mark guest as sent in DB
                fetch(`/dashboard/guests/mark-sent/${guestId}`);

                // 2. Instantly update UI on desktop row / mobile card to "Sent" (High responsiveness!)
                const row = this.closest('tr');
                if (row) {
                    const statusCell = row.querySelector('.opened-status-cell');
                    if (statusCell && !statusCell.querySelector('.badge-opened')) {
                        statusCell.innerHTML = `<span class="badge badge-sent"><i class="fas fa-paper-plane"></i> Sent</span><br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">Just now</small>`;
                    }
                }
                const card = this.closest('.guest-card');
                if (card) {
                    const deliveryStatus = card.querySelector('.guest-card-delivery-status');
                    if (deliveryStatus && !deliveryStatus.querySelector('.fa-check-double')) {
                        deliveryStatus.innerHTML = `<span class="mini-status"><i class="fas fa-paper-plane" style="color:#0284c7;"></i> Sent</span>`;
                    }
                }
                
                let waUrl = "";
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                
                if (isMobile) {
                    waUrl = `whatsapp://send?phone=${phone}&text=${encodedMessage}`;
                    window.open(waUrl, '_blank');
                } else {
                    let hasApp = false;       
                    const checkBlur = () => { hasApp = true; };
                    window.addEventListener('blur', checkBlur);           
                    window.location.href = `whatsapp://send?phone=${phone}&text=${encodedMessage}`;           
                    setTimeout(() => {
                        window.removeEventListener('blur', checkBlur);
                        if (!hasApp) {
                            const webUrl = `https://web.whatsapp.com/send?phone=${phone}&text=${encodedMessage}`;
                            window.open(webUrl, '_blank');
                        }
                    }, 1000);
                }
            });
        });

        // Live status polling helpers
        function formatLiveDateTime(mysqlDatetime) {
            if (!mysqlDatetime) return '';
            const d = new Date(mysqlDatetime.replace(' ', 'T'));
            if (isNaN(d.getTime())) return '';
            return d.toLocaleString('en-US', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit', hour12: true });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>'"]/g, 
                tag => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[tag] || tag)
            );
        }

        function renderOpenedStatusCell(g) {
            if (g.is_opened == 1) {
                let html = `<span class="badge badge-opened"><i class="fas fa-check-double"></i> Opened</span>`;
                if (g.opened_at) html += `<br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">${formatLiveDateTime(g.opened_at)}</small>`;
                return html;
            } else if (g.is_sent == 1) {
                let html = `<span class="badge badge-sent"><i class="fas fa-paper-plane"></i> Sent</span>`;
                if (g.sent_at) html += `<br><small style="color:#9ea3b0; font-size:0.7rem; margin-top:3px; display:block;">${formatLiveDateTime(g.sent_at)}</small>`;
                return html;
            }
            return `<span class="badge badge-not-sent">Not sent</span>`;
        }

        function renderRsvpStatusCell(rsvp) {
            if (rsvp === 'accepted') return `<span class='badge badge-attending'><i class='fas fa-check'></i> Attending</span>`;
            if (rsvp === 'rejected') return `<span class='badge badge-declined'><i class='fas fa-times'></i> Declined</span>`;
            return `<span class='badge badge-pending-rsvp'>Pending</span>`;
        }

        // ==========================================
        // Live Real-Time 5s Polling (Bypass WA & Delete buttons)
        // ==========================================
        function fetchGuestsLiveStatus() {
            fetch("{{ route('guests.live-status') }}")
                .then(r => r.json())
                .then(data => {
                    if (!data.guests) return;
                    data.guests.forEach(g => {
                        const row = document.querySelector(`#guest-table tr[data-id="${g.id}"]`);
                        if (!row) return;

                        // Real-time Guest Note Render Algorithm
                        const nameCell = row.querySelector('.guest-name-cell');
                        if (nameCell) {
                            let cellHtml = escapeHtml(g.name);
                            if (g.guest_note && g.guest_note.trim() !== '') {
                                cellHtml += `
                                    <div class="guest-note-box">
                                        <i class="fas fa-comment-dots" style="color: #c9a96e;"></i>
                                        <strong>Note:</strong> "${escapeHtml(g.guest_note)}"
                                    </div>
                                `;
                            }
                            nameCell.innerHTML = cellHtml;
                        }

                        const openedCell = row.querySelector('.opened-status-cell');
                        if (openedCell) openedCell.innerHTML = renderOpenedStatusCell(g);

                        const rsvpCell = row.querySelector('.rsvp-status-cell');
                        if (rsvpCell) rsvpCell.innerHTML = renderRsvpStatusCell(g.rsvp_status);

                        row.dataset.rsvp = g.rsvp_status;

                        // Live sync card view on mobile
                        const card = document.querySelector(`.guest-card[data-id="${g.id}"]`);
                        if (card) {
                            const cardName = card.querySelector('.guest-card-name');
                            if (cardName) {
                                let cellHtml = escapeHtml(g.name);
                                if (g.guest_note && g.guest_note.trim() !== '') {
                                    cellHtml += `
                                        <div class="guest-note-box">
                                            <i class="fas fa-comment-dots" style="color: #c9a96e;"></i>
                                            <strong>Note:</strong> "${escapeHtml(g.guest_note)}"
                                        </div>
                                    `;
                                }
                                cardName.innerHTML = cellHtml;
                            }

                            const noteBtn = card.querySelector('.icon-btn-note');
                            if (noteBtn) noteBtn.setAttribute('data-note', g.guest_note || '');

                            let deliveryHtml = '';
                            if (g.is_opened == 1) {
                                deliveryHtml = `<span class="mini-status"><i class="fas fa-check-double" style="color:#16a34a;"></i> Opened</span>`;
                            } else if (g.is_sent == 1) {
                                deliveryHtml = `<span class="mini-status"><i class="fas fa-paper-plane" style="color:#0284c7;"></i> Sent</span>`;
                            } else {
                                deliveryHtml = `<span class="mini-status"><i class="far fa-circle"></i> Not sent</span><span class="mini-status"><i class="fas fa-eye-slash"></i> Not opened</span>`;
                            }
                            const deliveryStatus = card.querySelector('.guest-card-delivery-status');
                            if (deliveryStatus) deliveryStatus.innerHTML = deliveryHtml;

                            let rsvpBadgeHtml = renderRsvpStatusCell(g.rsvp_status);
                            const badgesWrap = card.querySelector('.guest-card-badges');
                            if (badgesWrap) {
                                const keepDelivery = badgesWrap.querySelector('.guest-card-delivery-status');
                                badgesWrap.innerHTML = rsvpBadgeHtml + (keepDelivery ? keepDelivery.outerHTML : '');
                                const newDeliverySpan = badgesWrap.querySelector('.guest-card-delivery-status');
                                if (newDeliverySpan) newDeliverySpan.innerHTML = deliveryHtml;
                            }

                            card.dataset.rsvp = g.rsvp_status;
                        }
                    });
                })
                .catch(err => console.error('Error syncing guest live status:', err));
        }
        setInterval(fetchGuestsLiveStatus, 5000);

        // Mobile copy & note action buttons
        document.querySelectorAll('.icon-btn-copy').forEach(btn => {
            btn.addEventListener('click', function() {
                const link = this.getAttribute('data-link');
                if (!link) { alert('No invitation link available for this guest.'); return; }
                navigator.clipboard.writeText(link).then(() => {
                    const icon = this.querySelector('i');
                    icon.classList.remove('fa-copy');
                    icon.classList.add('fa-check');
                    setTimeout(() => { icon.classList.remove('fa-check'); icon.classList.add('fa-copy'); }, 1200);
                }).catch(() => alert('Could not copy the link automatically. Link: ' + link));
            });
        });

        document.querySelectorAll('.icon-btn-note').forEach(btn => {
            btn.addEventListener('click', function() {
                const note = this.getAttribute('data-note');
                alert(note && note.trim() !== '' ? note : 'No note from this guest yet.');
            });
        });
        </script>
    </x-slot>

</x-app-layout>