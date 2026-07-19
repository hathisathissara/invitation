<x-app-layout>

    <x-slot name="title">Upgrade Requests — Lumos Studio</x-slot>

    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/admin_upgrades.css') }}?v=1.2">
    </x-slot>

    <div class="admin-page-header">
        <div class="admin-page-header-left">
            <h1><i class="fas fa-arrow-up-right-dots" style="color:#6366f1;margin-right:10px;font-size:1.3rem;"></i>Upgrade Requests</h1>
            <p>Manage package upgrade requests from couples</p>
        </div>
        <div class="live-dot">Live Sync Active</div>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #16a34a !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <div class="panel-card" id="upgrade-requests-card">
        <div class="panel-card-header panel-card-header--alert">
            <h5 class="panel-card-title">
                <span class="title-icon title-icon--blue"><i class="fas fa-arrow-up-right-dots"></i></span>
                Pending Package Upgrade Reviews
            </h5>
        </div>
        <div class="upgrade-alert-banner">
            <i class="fas fa-info-circle"></i>
            Review the upgrade slip and approve or reject each request below.
        </div>
        <div class="ap-table-wrap">
            <table class="ap-table">
                <thead>
                    <tr>
                        <th>Couple Info</th>
                        <th>Current Plan</th>
                        <th>Requested Plan</th>
                        <th>Payment Slip</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="upgrade-requests-tbody">
                    @if ($upgradeRequests->count() > 0)
                        @foreach ($upgradeRequests as $upg)
                        @php
                            $parts   = explode('|', $upg->pending_upgrade_plan);
                            $req_pkg = $parts[0] ?? 'standard';
                            $req_gal = intval($parts[1] ?? 0);
                            $req_text = ucfirst($req_pkg) . ($req_gal ? " + Guest Gallery" : "");
                        @endphp
                        <tr>
                            <td data-label="Couple Info">
                                <div class="couple-name">{{ $upg->name }}</div>
                                <div class="couple-email">{{ $upg->email }}</div>
                            </td>
                            <td data-label="Current Plan">
                                <span class="badge-pill badge-pkg">{{ ucfirst($upg->package ?? 'Basic') }}</span>
                                @if (!empty($upg->has_guest_gallery))
                                    <br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>
                                @endif
                            </td>
                            <td data-label="Requested Plan">
                                <span style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:#6366f1;font-size:0.85rem;">
                                    <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
                                    {{ $req_text }}
                                </span>
                            </td>
                            <td data-label="Payment Slip">
                                @if (!empty($upg->upgrade_slip))
                                    @php $ext_upg = strtolower(pathinfo($upg->upgrade_slip, PATHINFO_EXTENSION)); @endphp
                                    @if ($ext_upg === 'pdf')
                                        <a href="{{ asset($upg->upgrade_slip) }}" target="_blank" class="slip-pdf-link">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                    @else
                                        <img src="{{ asset($upg->upgrade_slip) }}"
                                             class="slip-thumb"
                                             onclick="openLightbox(this.src)"
                                             alt="Upgrade receipt">
                                    @endif
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div class="actions-cell">
                                    <!-- Secure POST requests for actions -->
                                    <form action="{{ route('admin.upgrades.approve', $upg->id) }}" method="POST" onsubmit="return confirm('Approve package upgrade to {{ $req_text }} for {{ addslashes($upg->name) }}?');" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-ap btn-approve">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.upgrades.reject', $upg->id) }}" method="POST" onsubmit="return confirm('Reject upgrade request for {{ addslashes($upg->name) }}? This will delete the slip receipt.');" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-ap btn-reject">
                                            <i class="fas fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                                    <p>No pending upgrade requests at the moment.</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Image previewer -->
    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
        <div class="lightbox-inner" onclick="event.stopPropagation()">
            <div class="lightbox-close" onclick="closeLightbox()"><i class="fas fa-xmark"></i></div>
            <img src="" id="lightbox-img" alt="Payment slip">
        </div>
    </div>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modal triggers -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
        let lastUpgradeSnapshot = null;
        let adminPollPaused = false;
        let pollingInterval = 5000;
        let consecutiveErrors = 0;
        let adminStatsTimer = null;

        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('#upgrade-requests-tbody')) {
                adminPollPaused = true;
                setTimeout(() => { adminPollPaused = false; }, 3000);
            }
        });

        function openLightbox(src) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox').classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { document.getElementById('lightbox-img').src = ''; }, 300);
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

        function esc(str) {
            return String(str).replace(/'/g, "\\'");
        }

        function buildUpgradeRow(upg) {
            const galleryNote = upg.has_guest_gallery ? `<br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>` : '';
            let slipHtml = '';
            if (upg.upgrade_slip) {
                slipHtml = upg.upgrade_slip_is_pdf
                    ? `<a href="${upg.upgrade_slip}" target="_blank" class="slip-pdf-link"><i class="fas fa-file-pdf"></i> View PDF</a>`
                    : `<img src="${upg.upgrade_slip}" class="slip-thumb" onclick="openLightbox(this.src)" alt="Upgrade Receipt">`;
            }

            const csrfToken = "{{ csrf_token() }}";
            const approveUrl = `{{ url('/admin/upgrades') }}/${upg.id}/approve`;
            const rejectUrl = `{{ url('/admin/upgrades') }}/${upg.id}/reject`;

            return `<tr>
                <td data-label="Couple Info">
                    <div class="couple-name">${upg.name}</div>
                    <div class="couple-email">${upg.email}</div>
                </td>
                <td data-label="Current Plan"><span class="badge-pill badge-pkg">${upg.package}</span>${galleryNote}</td>
                <td data-label="Requested Plan">
                    <span style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:#6366f1;font-size:0.85rem;">
                        <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i> ${upg.req_text}
                    </span>
                </td>
                <td data-label="Payment Slip">${slipHtml}</td>
                <td data-label="Actions">
                    <div class="actions-cell">
                        <form action="${approveUrl}" method="POST" onsubmit="return confirm('Approve package upgrade to ${esc(upg.req_text)} for ${esc(upg.name)}?');" style="display:inline;">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button type="submit" class="btn-ap btn-approve"><i class="fas fa-check"></i> Approve</button>
                        </form>
                        
                        <form action="${rejectUrl}" method="POST" onsubmit="return confirm('Reject upgrade request for ${esc(upg.name)}? This will delete the slip receipt.');" style="display:inline;">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button type="submit" class="btn-ap btn-reject"><i class="fas fa-xmark"></i> Reject</button>
                        </form>
                    </div>
                </td>
            </tr>`;
        }

        // ==========================================
        // Live Upgrades Requests Table Polling (5s)
        // ==========================================
        function fetchAdminLiveStats() {
            if (adminPollPaused || document.hidden) return;

            fetch("{{ route('admin.upgrades.live') }}")
                .then(r => r.json())
                .then(data => {
                    consecutiveErrors = 0;
                    if (data.error) return;

                    const upgradeSnapshot = JSON.stringify(data.upgrade_requests);
                    if (upgradeSnapshot !== lastUpgradeSnapshot) {
                        lastUpgradeSnapshot = upgradeSnapshot;
                        const upgradeTbody = document.getElementById('upgrade-requests-tbody');
                        if (upgradeTbody && data.upgrade_requests) {
                            if (data.upgrade_requests.length > 0) {
                                upgradeTbody.innerHTML = data.upgrade_requests.map(buildUpgradeRow).join('');
                            } else {
                                upgradeTbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><i class="fas fa-check-circle" style="color:#10b981;"></i><p>No pending upgrade requests at the moment.</p></div></td></tr>`;
                            }
                        }
                    }

                    if (pollingInterval > 5000) {
                        pollingInterval = 5000;
                        resetAdminStatsTimer();
                    }
                })
                .catch(err => {
                    console.error('Admin live stats error:', err);
                    consecutiveErrors++;
                    if (consecutiveErrors > 2) {
                        pollingInterval = Math.min(60000, pollingInterval * 2);
                        resetAdminStatsTimer();
                    }
                });
        }

        function resetAdminStatsTimer() {
            if (adminStatsTimer) clearInterval(adminStatsTimer);
            adminStatsTimer = setInterval(fetchAdminLiveStats, pollingInterval);
        }

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) fetchAdminLiveStats();
        });
        resetAdminStatsTimer();
        </script>
    </x-slot>

</x-app-layout>