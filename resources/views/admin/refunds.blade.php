<x-app-layout>

    <x-slot name="title">Refund Requests — Lumos Studio</x-slot>

    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/admin_refunds.css') }}?v=1.2">
    </x-slot>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
        <div>
            <h3 class="page-heading mb-1" style="font-family: 'Cormorant Garamond', serif; font-size: 2.2rem; font-weight: 700;">Refund Requests Dashboard</h3>
            <p class="page-subheading mb-0">පරිපාලක මුදල් ආපසු ගෙවීම් — review and process couple refund requests</p>
        </div>
        <div class="admin-nav-tabs">
            <a href="{{ route('admin.index') }}" class="admin-nav-tab"><i class="fas fa-shield-alt"></i> Admin Panel</a>
            <a href="{{ route('admin.refunds') }}" class="admin-nav-tab active">
                <i class="fas fa-undo-alt"></i> Refund Requests
                @if ($refundRequests->count() > 0)
                    <span class="tab-badge">{{ $refundRequests->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-20 rounded-3 mb-4" style="color: #16a34a !important; font-size: 0.87rem; border-radius: 12px; padding: 13px 18px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(16,185,129,0.25);">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
        </div>
    @endif

    <!-- Refund Stats grid -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="refund-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
                <div class="refund-stat-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(239,68,68,0.12); color:#ef4444;"><i class="fas fa-exclamation-circle"></i></div>
                <div>
                    <div class="refund-stat-num" id="live-refund-stat-pending">{{ $refundRequests->count() }}</div>
                    <div class="refund-stat-label">Pending Reviews</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="refund-stat p-4 rounded-4 d-flex align-items-center gap-3 h-100">
                <div class="refund-stat-icon d-flex align-items-center justify-content-center flex-shrink-0 rounded-3" style="background:rgba(16,185,129,0.12); color:#10b981;"><i class="fas fa-university"></i></div>
                <div>
                    <div class="refund-stat-num" id="live-refund-stat-payout">{{ $payoutsList->count() }}</div>
                    <div class="refund-stat-label">Awaiting Payout</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. TABLE: PENDING REFUND REQUESTS REVIEWS -->
    <div class="table-card border" style="border-color: rgba(239,68,68,0.2) !important; overflow: hidden;">
        <div class="table-card-header text-danger" style="background: rgba(239,68,68,0.04);">
            <h5><i class="fas fa-exclamation-circle"></i> Phase 1: Refund Reviews (අනුමැතිය අපේක්ෂාවෙන්)
                <span class="header-count" id="live-refund-pending-count" style="color:#ef4444;">{{ $refundRequests->count() }}</span>
            </h5>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Couple Info</th>
                        <th>Request Details</th>
                        <th>Shared Track Validation</th>
                        <th>Payment Slip</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="live-refund-requests-tbody">
                    @if ($refundRequests->count() > 0)
                        @foreach ($refundRequests as $ref)
                        @php
                            $openedGuestsCount = $ref->wedding ? $ref->wedding->guests()->where(function($q) {
                                $q->where('is_opened', true)->orWhere('rsvp_status', '!=', 'pending');
                            })->count() : 0;
                            $isEligible = ($openedGuestsCount == 0);
                        @endphp
                        <tr>
                            <td data-label="Couple">
                                <div class="couple-cell">
                                    <div class="couple-avatar avatar-danger">{{ strtoupper(substr($ref->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.9rem; text-align: left;">{{ $ref->name }}</div>
                                        <div class="text-muted small" style="margin-top:2px; text-align: left;">{{ $ref->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Request">
                                <div>
                                    <div class="small fw-bold text-muted" style="text-align: left;"><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($ref->refund_requested_at)->format('d M Y, h:i A') }}</div>
                                    <div class="reason-box">"{{ $ref->refund_reason }}"</div>
                                </div>
                            </td>
                            <td data-label="Validation">
                                @if ($isEligible)
                                    <span class="badge-eligible"><i class="fas fa-check-circle"></i> Eligible (0 opened)</span>
                                @else
                                    <span class="badge-non-eligible" title="This couple has already shared the link with guests.">
                                        <i class="fas fa-times-circle"></i> Non-Refundable ({{ $openedGuestsCount }} opened)
                                    </span>
                                @endif
                            </td>
                            <td data-label="Slip">
                                @if (!empty($ref->payment_slip))
                                    <a href="{{ asset($ref->payment_slip) }}" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;">
                                        <i class="fas fa-file-invoice"></i> View Slip
                                    </a>
                                @else
                                    <span class="text-muted small">No Slip</span>
                                @endif
                            </td>
                            <td data-label="Actions" class="action-cell" style="white-space:nowrap;">
                                <form action="{{ route('admin.refunds.approve', $ref->user_id) }}" method="POST" onsubmit="return confirm('Approve refund for {{ addslashes($ref->name) }}? This will deactivated their account and ask them for bank details.');" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-action-approve">
                                        <i class="fas fa-check"></i> Approve Refund
                                    </button>
                                </form>

                                <form action="{{ route('admin.refunds.reject', $ref->user_id) }}" method="POST" onsubmit="return confirm('Reject refund request for {{ addslashes($ref->name) }}?');" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-action btn-action-reject">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="5" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-inbox"></i>Review කිරීමට කිසිදු Refund ඉල්ලීමක් දැනට නැත.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>


    <!-- 2. TABLE: APPROVED REFUNDS - AWAITING BANK PAYOUT -->
    <div class="table-card border" style="border-color: rgba(16,185,129,0.2) !important; overflow: hidden;">
        <div class="table-card-header text-success" style="background: rgba(16,185,129,0.04);">
            <h5><i class="fas fa-university"></i> Phase 2: Pending Bank Payouts (ගෙවීම් කිරීමට ඇති ගිණුම්)
                <span class="header-count" id="live-refund-payout-count" style="color:#10b981;">{{ $payoutsList->count() }}</span>
            </h5>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Couple Info</th>
                        <th>Submitted Bank Account Details</th>
                        <th>Initial Receipt</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="live-payouts-tbody">
                    @if ($payoutsList->count() > 0)
                        @foreach ($payoutsList as $pay)
                        <tr>
                            <td data-label="Couple">
                                <div class="couple-cell">
                                    <div class="couple-avatar avatar-success">{{ strtoupper(substr($pay->name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.9rem; text-align: left;">{{ $pay->name }}</div>
                                        <div class="text-muted small" style="margin-top:2px; text-align: left;">{{ $pay->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Bank Details">
                                <div class="bank-box"><i class="fas fa-university me-1 text-success"></i> {{ $pay->refund_bank_details }}</div>
                            </td>
                            <td data-label="Receipt">
                                @if (!empty($pay->payment_slip))
                                    <a href="{{ asset($pay->payment_slip) }}" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;">
                                        <i class="fas fa-file-invoice"></i> View Slip
                                    </a>
                                @else
                                    <span class="text-muted small">No Slip</span>
                                @endif
                            </td>
                            <td data-label="Action">
                                <form action="{{ route('admin.refunds.complete', $pay->user_id) }}" method="POST" onsubmit="return confirm('Confirm payout to {{ addslashes($pay->name) }}? This will send a refund completed receipt email.');">
                                    @csrf
                                    <button type="submit" class="btn-action-complete">
                                        <i class="fas fa-check-circle"></i> Mark Payout as Completed
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr><td colspan="4" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-check-double"></i>ගෙවීම් කිරීමට ඇති කිසිදු බැංකු ගිණුමක් දැනට ලැබී නැත.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>


    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modals -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>
        function updateLiveText(id, newValue) {
            const el = document.getElementById(id);
            if (el && el.textContent != newValue) {
                el.style.transition = 'opacity 0.2s';
                el.style.opacity = '0.2';
                setTimeout(() => {
                    el.textContent = newValue;
                    el.style.opacity = '1';
                }, 200);
            }
        }

        const csrfTokenJS = "{{ csrf_token() }}";

        function buildRefundRequestRow(ref) {
            const avatarLetter = ref.name.charAt(0).toUpperCase();
            const eligibleBadge = ref.is_eligible
                ? `<span class="badge-eligible"><i class="fas fa-check-circle"></i> Eligible (0 opened)</span>`
                : `<span class="badge-non-eligible" title="This couple has already shared the link with guests."><i class="fas fa-times-circle"></i> Non-Refundable (${ref.opened_count} opened)</span>`;
            
            const slipCell = ref.payment_slip
                ? `<a href="${ref.payment_slip}" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;"><i class="fas fa-file-invoice"></i> View Slip</a>`
                : `<span class="text-muted small">No Slip</span>`;

            const approveUrl = `{{ url('/admin/refunds') }}/${ref.user_id}/approve`;
            const rejectUrl = `{{ url('/admin/refunds') }}/${ref.user_id}/reject`;

            return `<tr>
                <td data-label="Couple">
                    <div class="couple-cell">
                        <div class="couple-avatar avatar-danger">${avatarLetter}</div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size:0.9rem;">${ref.name}</div>
                            <div class="text-muted small" style="margin-top:2px;">${ref.email}</div>
                        </div>
                    </div>
                </td>
                <td data-label="Request">
                    <div>
                        <div class="small fw-bold text-muted"><i class="far fa-clock"></i> ${ref.requested_at}</div>
                        <div class="reason-box">"${ref.reason}"</div>
                    </div>
                </td>
                <td data-label="Validation">${eligibleBadge}</td>
                <td data-label="Slip">${slipCell}</td>
                <td data-label="Actions" class="action-cell" style="white-space:nowrap;">
                    <form action="${approveUrl}" method="POST" onsubmit="return confirm('Approve refund for ${ref.name.replace(/'/g, "\\'")}? This will deactivated their account and ask them for bank details.');" style="display:inline;">
                        <input type="hidden" name="_token" value="${csrfTokenJS}">
                        <button type="submit" class="btn-action btn-action-approve"><i class="fas fa-check"></i> Approve Refund</button>
                    </form>
                    
                    <form action="${rejectUrl}" method="POST" onsubmit="return confirm('Reject refund request for ${ref.name.replace(/'/g, "\\'")}?');" style="display:inline;">
                        <input type="hidden" name="_token" value="${csrfTokenJS}">
                        <button type="submit" class="btn-action btn-action-reject"><i class="fas fa-times"></i> Reject</button>
                    </form>
                </td>
            </tr>`;
        }

        function buildPayoutRow(pay) {
            const avatarLetter = pay.name.charAt(0).toUpperCase();
            const slipCell = pay.payment_slip
                ? `<a href="${pay.payment_slip}" target="_blank" class="btn btn-sm btn-outline-secondary p-2 fw-semibold" style="font-size:0.75rem; border-radius:8px;"><i class="fas fa-file-invoice"></i> View Slip</a>`
                : `<span class="text-muted small">No Slip</span>`;

            const completeUrl = `{{ url('/admin/refunds') }}/${pay.user_id}/complete`;

            return `<tr>
                <td data-label="Couple">
                    <div class="couple-cell">
                        <div class="couple-avatar avatar-success">${avatarLetter}</div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size:0.9rem;">${pay.name}</div>
                            <div class="text-muted small" style="margin-top:2px;">${pay.email}</div>
                        </div>
                    </div>
                </td>
                <td data-label="Bank Details"><div class="bank-box"><i class="fas fa-university me-1 text-success"></i> ${pay.bank_details}</div></td>
                <td data-label="Receipt">${slipCell}</td>
                <td data-label="Action">
                    <form action="${completeUrl}" method="POST" onsubmit="return confirm('Confirm payout to ${pay.name.replace(/'/g, "\\'")}? This will send a refund completed receipt email.');">
                        <input type="hidden" name="_token" value="${csrfTokenJS}">
                        <button type="submit" class="btn-action-complete"><i class="fas fa-check-circle"></i> Mark Payout as Completed</button>
                    </form>
                </td>
            </tr>`;
        }

        let lastRequestsSnapshot = null;
        let lastPayoutsSnapshot = null;
        let refundPollPaused = false;
        let refundPollingInterval = 5000;
        let refundErrors = 0;
        let refundTimer = null;

        document.addEventListener('mousedown', function(e) {
            if (e.target.closest('#live-refund-requests-tbody') || e.target.closest('#live-payouts-tbody')) {
                refundPollPaused = true;
                setTimeout(() => { refundPollPaused = false; }, 3000);
            }
        });

        // ==========================================
        // Live Refunds requests polling (5s)
        // ==========================================
        function fetchRefundLiveCounts() {
            if (refundPollPaused || document.hidden) return;

            fetch("{{ route('admin.refunds.live') }}")
                .then(r => r.json())
                .then(data => {
                    refundErrors = 0;
                    if (data.error) return;

                    updateLiveText('live-refund-pending-count', data.pending_count);
                    updateLiveText('live-refund-payout-count', data.payout_count);
                    updateLiveText('live-refund-stat-pending', data.pending_count);
                    updateLiveText('live-refund-stat-payout', data.payout_count);

                    const requestsSnapshot = JSON.stringify(data.refund_requests);
                    if (requestsSnapshot !== lastRequestsSnapshot) {
                        lastRequestsSnapshot = requestsSnapshot;

                        const reqTbody = document.getElementById('live-refund-requests-tbody');
                        if (reqTbody && data.refund_requests) {
                            reqTbody.innerHTML = data.refund_requests.length > 0
                                ? data.refund_requests.map(buildRefundRequestRow).join('')
                                : `<tr><td colspan="5" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-inbox"></i>Review කිරීමට කිසිදු Refund ඉල්ලීමක් දැනට නැත.</td></tr>`;
                        }
                    }

                    const payoutsSnapshot = JSON.stringify(data.payouts);
                    if (payoutsSnapshot !== lastPayoutsSnapshot) {
                        lastPayoutsSnapshot = payoutsSnapshot;

                        const payTbody = document.getElementById('live-payouts-tbody');
                        if (payTbody && data.payouts) {
                            payTbody.innerHTML = data.payouts.length > 0
                                ? data.payouts.map(buildPayoutRow).join('')
                                : `<tr><td colspan="4" class="text-center py-5 text-muted empty-table-row"><i class="fas fa-university"></i>Payout කිරීමට කිසිදු ගිණුමක් දැනට නැත.</td></tr>`;
                        }
                    }

                    if (refundPollingInterval > 5000) {
                        refundPollingInterval = 5000;
                        resetRefundTimer();
                    }
                })
                .catch(err => {
                    console.error('Error syncing admin refund counts:', err);
                    refundErrors++;
                    if (refundErrors > 2) {
                        refundPollingInterval = Math.min(60000, refundPollingInterval * 2);
                        resetRefundTimer();
                    }
                });
        }

        function resetRefundTimer() {
            if (refundTimer) clearInterval(refundTimer);
            refundTimer = setInterval(fetchRefundLiveCounts, refundPollingInterval);
        }

        document.addEventListener("visibilitychange", () => {
            if (!document.hidden) {
                fetchRefundLiveCounts();
            }
        });
        resetRefundTimer();
        </script>
    </x-slot>

</x-app-layout>