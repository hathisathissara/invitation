<x-app-layout>

    <x-slot name="title">Admin Control Panel — Lumus Studio</x-slot>

    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/admin_dashboard.css') }}?v=1.2">
    </x-slot>

    <!-- Admin header -->
    <div class="admin-page-header">
        <div class="admin-page-header-left">
            <h1><i class="fas fa-shield-halved" style="color:#c9a96e;margin-right:10px;font-size:1.3rem;"></i>Admin Control Panel</h1>
            <p>Manage registered couples, packages & account lifecycle</p>
        </div>
        <div class="live-dot">Live Sync Active</div>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #16a34a !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle"></i> {!! session('status') !!}
        </div>
    @endif

    <!-- Admin Top Stats grid -->
    <div class="stat-grid">
        <div class="stat-card stat-card--gold">
            <div class="stat-icon stat-icon--gold"><i class="fas fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-num" id="live-admin-total">{{ $total }}</div>
                <div class="stat-label">Total Couples</div>
            </div>
        </div>
        <div class="stat-card stat-card--green">
            <div class="stat-icon stat-icon--green"><i class="fas fa-circle-check"></i></div>
            <div class="stat-body">
                <div class="stat-num" id="live-admin-active">{{ $active }}</div>
                <div class="stat-label">Active</div>
            </div>
        </div>
        <div class="stat-card stat-card--amber">
            <div class="stat-icon stat-icon--amber"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-body">
                <div class="stat-num" id="live-admin-pending">{{ $pending }}</div>
                <div class="stat-label">Pending Review</div>
            </div>
        </div>
        <a href="{{ route('admin.refunds') }}" class="stat-card stat-card--red" style="cursor:pointer;">
            <div class="stat-icon stat-icon--red"><i class="fas fa-rotate-left"></i></div>
            <div class="stat-body">
                <div class="stat-num" id="live-admin-refunds" style="color:#ef4444;">{{ $refundRequestsCount }}</div>
                <div class="stat-label">Refund Requests</div>
            </div>
        </a>
        <a href="{{ route('admin.upgrades') }}" class="stat-card stat-card--blue" style="cursor:pointer;">
            <div class="stat-icon stat-icon--blue"><i class="fas fa-arrow-up-right-dots"></i></div>
            <div class="stat-body">
                <div class="stat-num" id="live-admin-upgrades" style="color:#6366f1;">{{ $upgradeRequestsCount }}</div>
                <div class="stat-label">Upgrade Requests</div>
            </div>
        </a>
    </div>

    <!-- Registered couples list table -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h5 class="panel-card-title">
                <span class="title-icon title-icon--gold"><i class="fas fa-heart"></i></span>
                Registered Couples
                <span class="badge-pill badge-pkg" style="font-size:0.7rem;margin-left:6px;" id="couples-count-badge">{{ $total }}</span>
            </h5>
            <div class="search-wrap">
                <i class="fas fa-search search-ico"></i>
                <input type="text" class="search-input" id="admin-search" placeholder="Search by name or email…" autocomplete="off">
            </div>
        </div>

        <div class="ap-table-wrap">
            <table class="ap-table" id="admin-table">
                <thead>
                    <tr>
                        <th>Couple</th>
                        <th>Plan / Add-on</th>
                        <th>Wedding Date</th>
                        <th>Bank Slip</th>
                        <th>Status</th>
                        <th>Invite Link</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-table-tbody">
                    @if ($users->count() > 0)
                        @foreach ($users as $user)
                        @php
                            $wedding_past = !empty($user->wedding->wedding_date) && strtotime($user->wedding->wedding_date) < strtotime('today');
                            $invite_url = '';
                            if ($user->wedding && $user->wedding->slug) {
                                $invite_url = route('invitation.invite', $user->wedding->slug);
                            }

                            $notice_sent    = !empty($user->deletion_notice_sent_at);
                            $days_left      = 0;
                            $can_delete_now = false;
                            if ($notice_sent) {
                                $delete_eligible_at = strtotime($user->deletion_notice_sent_at . ' +7 days');
                                $seconds_left = $delete_eligible_at - time();
                                $days_left = $seconds_left > 0 ? (int) ceil($seconds_left / 86400) : 0;
                                $can_delete_now = $seconds_left <= 0;
                            }
                        @endphp
                        <tr data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                            <td data-label="Couple">
                                <div class="couple-name">{{ $user->name }}</div>
                                <div class="couple-email">{{ $user->email }}</div>
                                <div class="couple-badges">
                                    @if (!empty($user->refund_requested_at))
                                        <span class="badge-pill badge-refund"><i class="fas fa-triangle-exclamation"></i> Refund Req.</span>
                                    @endif
                                    @if (!empty($user->pending_upgrade_plan))
                                        <span class="badge-pill badge-upgrade"><i class="fas fa-arrow-up"></i> Upgrade Pending</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Plan">
                                <span class="badge-pill badge-pkg">{{ ucfirst($user->package ?? 'Basic') }}</span>
                                @if ($user->has_guest_gallery == 1)
                                    <br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>
                                @endif
                            </td>
                            <td data-label="Wedding Date">
                                @if ($user->wedding && $user->wedding->wedding_date)
                                    {{ \Carbon\Carbon::parse($user->wedding->wedding_date)->format('d M Y') }}
                                    @if ($wedding_past)
                                        <span class="badge-passed"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>
                                    @endif
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>
                            <td data-label="Bank Slip">
                                @if (!empty($user->payment_slip))
                                    @php $ext_slip = strtolower(pathinfo($user->payment_slip, PATHINFO_EXTENSION)); @endphp
                                    @if ($ext_slip === 'pdf')
                                        <a href="{{ asset($user->payment_slip) }}" target="_blank" class="slip-pdf-link">
                                            <i class="fas fa-file-pdf"></i> View PDF
                                        </a>
                                    @else
                                        <img src="{{ asset($user->payment_slip) }}"
                                             class="slip-thumb"
                                             onclick="openLightbox(this.src)"
                                             alt="Payment slip">
                                    @endif
                                @else
                                    <span class="slip-none">No slip yet</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                @if ($user->status === 'active')
                                    <span class="badge-pill badge-active">
                                        <span class="status-dot dot-active"></span> Active
                                    </span>
                                @else
                                    <span class="badge-pill badge-pending">
                                        <span class="status-dot dot-pending"></span> Pending
                                    </span>
                                @endif
                            </td>
                            <td data-label="Invite Link">
                                @if ($user->wedding && !empty($user->wedding->slug))
                                    <div class="invite-link-cell">
                                        <span class="invite-link-text" title="{{ $invite_url }}">{{ $invite_url }}</span>
                                        <button class="btn-copy" onclick="adminCopyLink('{{ $invite_url }}', this)" title="Copy link"><i class="fas fa-copy"></i></button>
                                    </div>
                                @elseif ($user->wedding)
                                    <span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>
                                @else
                                    <span style="color:#d1d5db;">—</span>
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div class="actions-cell">
                                    @if ($user->wedding && $user->wedding->slug)
                                    <a href="{{ route('invitation.invite', ['slug' => $user->wedding->slug, 'preview' => 1]) }}"
                                       target="_blank" class="btn-ap btn-preview">
                                        <i class="fas fa-eye"></i> Preview
                                    </a>
                                    @endif

                                    @if ($user->status === 'pending')
                                    <a href="{{ route('admin.toggle-status', $user->id) }}"
                                       class="btn-ap btn-activate"
                                       onclick="return confirm('Activate account for {{ addslashes($user->name) }}?');">
                                        <i class="fas fa-check"></i> Activate
                                    </a>
                                    @else
                                    <a href="{{ route('admin.toggle-status', $user->id) }}"
                                       class="btn-ap btn-deactivate"
                                       onclick="return confirm('Deactivate account for {{ addslashes($user->name) }}?');">
                                        <i class="fas fa-xmark"></i> Deactivate
                                    </a>
                                    @endif

                                    @if ($wedding_past)
                                        @if (!$notice_sent)
                                        <a href="{{ route('admin.notify-delete', $user->id) }}"
                                           class="btn-ap btn-notify"
                                           onclick="return confirm('Email {{ addslashes($user->name) }} that their invitation will be deleted in 7 days?')"
                                           title="Send 7-day deletion notice">
                                            <i class="fas fa-bell"></i> Notify
                                        </a>
                                        @elseif (!$can_delete_now)
                                        <span class="badge-countdown" title="Notice sent on {{ \Carbon\Carbon::parse($user->deletion_notice_sent_at)->format('d M Y') }}">
                                            <i class="fas fa-hourglass-half"></i> {{ $days_left }}d left
                                        </span>
                                        @else
                                        <a href="{{ route('admin.delete.confirm', $user->id) }}"
                                           class="btn-delete-icon"
                                           title="Notice period ended — delete this account">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-heart-crack"></i>
                                    <p>No couples registered yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Image lightbox previewer -->
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
        document.getElementById('admin-search').addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('#admin-table tbody tr').forEach(row => {
                row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
            });
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

        function updateLiveText(id, newValue) {
            const el = document.getElementById(id);
            if (el && el.textContent != newValue) {
                el.style.transition = 'opacity 0.18s, transform 0.18s';
                el.style.opacity    = '0';
                el.style.transform  = 'translateY(-6px)';
                setTimeout(() => {
                    el.textContent  = newValue;
                    el.style.opacity   = '1';
                    el.style.transform = 'translateY(0)';
                }, 180);
            }
        }

        function esc(str) {
            return String(str).replace(/'/g, "\\'");
        }

        function buildCoupleRow(user) {
            let badges = '';
            if (user.refund_requested) {
                badges += `<span class="badge-pill badge-refund"><i class="fas fa-triangle-exclamation"></i> Refund Req.</span>`;
            }
            if (user.upgrade_pending) {
                badges += `<span class="badge-pill badge-upgrade"><i class="fas fa-arrow-up"></i> Upgrade Pending</span>`;
            }
            const badgesHtml = badges ? `<div class="couple-badges">${badges}</div>` : '';

            const pkgHtml = `<span class="badge-pill badge-pkg">${user.package}</span>` +
                (user.has_guest_gallery ? `<br><span class="badge-pill badge-gallery" style="margin-top:4px;"><i class="fas fa-images"></i> +Gallery</span>` : '');

            let dateHtml = `<span style="color:#d1d5db;">—</span>`;
            if (user.wedding_date) {
                dateHtml = user.wedding_date;
                if (user.wedding_past) {
                    dateHtml += `<span class="badge-passed"><i class="fas fa-clock" style="font-size:0.6rem;"></i> Passed</span>`;
                }
            }

            let slipHtml = `<span class="slip-none">No slip yet</span>`;
            if (user.payment_slip) {
                slipHtml = user.slip_is_pdf
                    ? `<a href="${user.payment_slip}" target="_blank" class="slip-pdf-link"><i class="fas fa-file-pdf"></i> View PDF</a>`
                    : `<img src="${user.payment_slip}" class="slip-thumb" onclick="openLightbox(this.src)" alt="Payment slip">`;
            }

            const statusHtml = user.status === 'active'
                ? `<span class="badge-pill badge-active"><span class="status-dot dot-active"></span> Active</span>`
                : `<span class="badge-pill badge-pending"><span class="status-dot dot-pending"></span> Pending</span>`;

            let linkHtml = `<span style="color:#d1d5db;">—</span>`;
            if (user.wedding_id && user.has_slug) {
                linkHtml = `<div class="invite-link-cell">
                    <span class="invite-link-text" title="${user.invite_url}">${user.invite_url}</span>
                    <button class="btn-copy" onclick="adminCopyLink('${user.invite_url}', this)" title="Copy link"><i class="fas fa-copy"></i></button>
                </div>`;
            } else if (user.wedding_id) {
                linkHtml = `<span style="font-size:0.75rem;color:#d1d5db;">No slug yet</span>`;
            }

            let actionsHtml = '';
            if (user.wedding_id && user.slug) {
                // 💡 Laravel route template dynamically compiled for clean Preview URL
                const previewUrl = `{{ url('/invitation') }}/${user.slug}?preview=1`;
                actionsHtml += `<a href="${previewUrl}" target="_blank" class="btn-ap btn-preview"><i class="fas fa-eye"></i> Preview</a>`;
            }
            if (user.status === 'pending') {
                actionsHtml += `<a href="{{ url('/admin/toggle-status') }}/${user.id}" class="btn-ap btn-activate" onclick="return confirm('Activate account for ${esc(user.name)}?');"><i class="fas fa-check"></i> Activate</a>`;
            } else {
                actionsHtml += `<a href="{{ url('/admin/toggle-status') }}/${user.id}" class="btn-ap btn-deactivate" onclick="return confirm('Deactivate account for ${esc(user.name)}?');"><i class="fas fa-xmark"></i> Deactivate</a>`;
            }
            if (user.wedding_past) {
                if (!user.notice_sent) {
                    actionsHtml += `<a href="{{ url('/admin/notify-delete') }}/${user.id}" class="btn-ap btn-notify" onclick="return confirm('Email ${esc(user.name)} that their invitation will be deleted in 7 days?');" title="Send 7-day deletion notice"><i class="fas fa-bell"></i> Notify</a>`;
                } else if (!user.can_delete_now) {
                    actionsHtml += `<span class="badge-countdown" title="Notice sent on ${user.notice_sent_at}"><i class="fas fa-hourglass-half"></i> ${user.days_left}d left</span>`;
                } else {
                    actionsHtml += `<a href="{{ url('/admin/users') }}/${user.id}/delete" class="btn-delete-icon" title="Notice period ended — delete this account"><i class="fas fa-trash-alt"></i></a>`;
                }
            }

            return `<tr data-search="${(user.name + ' ' + user.email).toLowerCase()}">
                <td data-label="Couple">
                    <div class="couple-name">${user.name}</div>
                    <div class="couple-email">${user.email}</div>
                    ${badgesHtml}
                </td>
                <td data-label="Plan">${pkgHtml}</td>
                <td data-label="Wedding Date">${dateHtml}</td>
                <td data-label="Bank Slip">${slipHtml}</td>
                <td data-label="Status">${statusHtml}</td>
                <td data-label="Invite Link">${linkHtml}</td>
                <td data-label="Actions"><div class="actions-cell">${actionsHtml}</div></td>
            </tr>`;
        }

        let lastUsersSnapshot = null;
        let adminPollPaused   = false;
        let pollingInterval   = 5000;
        let consecutiveErrors = 0;
        let adminStatsTimer   = null;

        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('#admin-table-tbody')) {
                adminPollPaused = true;
                setTimeout(() => { adminPollPaused = false; }, 3000);
            }
        });

        // ==========================================
        // Admin real-time 5s Polling (registered couples)
        // ==========================================
        function fetchAdminLiveStats() {
            if (adminPollPaused || document.hidden) return;

            fetch("{{ route('admin.live-stats') }}")
                .then(r => r.json())
                .then(data => {
                    consecutiveErrors = 0;
                    if (data.error) return;

                    updateLiveText('live-admin-total',   data.total);
                    updateLiveText('live-admin-active',  data.active);
                    updateLiveText('live-admin-pending', data.pending);
                    updateLiveText('live-admin-refunds', data.refund_requests_count);
                    updateLiveText('live-admin-upgrades', data.upgrade_requests_count);
                    updateLiveText('couples-count-badge', data.total);

                    const usersSnapshot = JSON.stringify(data.users);
                    if (usersSnapshot !== lastUsersSnapshot) {
                        lastUsersSnapshot = usersSnapshot;
                        const tbody = document.getElementById('admin-table-tbody');
                        if (tbody && data.users) {
                            if (data.users.length > 0) {
                                tbody.innerHTML = data.users.map(buildCoupleRow).join('');
                            } else {
                                tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fas fa-heart-crack"></i><p>No couples registered yet.</p></div></td></tr>`;
                            }
                            const searchBox = document.getElementById('admin-search');
                            if (searchBox && searchBox.value) {
                                const q = searchBox.value.toLowerCase();
                                tbody.querySelectorAll('tr').forEach(row => {
                                    row.style.display = (row.dataset.search || '').includes(q) ? '' : 'none';
                                });
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

        function adminCopyLink(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check" style="color:#10b981;"></i>';
                btn.style.borderColor = '#10b981';
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.style.borderColor = '';
                }, 2000);
                
                if(typeof showToast === 'function') {
                    showToast('✓ Invite link copied!');
                }
            }).catch(() => {
                prompt('Copy this invite link:', url);
            });
        }
        </script>
    </x-slot>

</x-app-layout>