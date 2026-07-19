<x-app-layout>

    <x-slot name="title">Delete Account — Lumos Studio</x-slot>

    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/admin_delete.css') }}?v=1.1">
    </x-slot>

    <div class="delete-confirm-card">
        <div class="delete-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="delete-title">Delete Account Permanently?</div>
        <div class="delete-subtitle">This action cannot be undone. All database records and physical image files on the server will be permanently removed.</div>

        <!-- Info Group card -->
        <div class="delete-info-group">
            <div class="delete-info-row">
                <div class="delete-info-icon"><i class="fas fa-heart"></i></div>
                <div>
                    <div class="delete-info-label">Couple Names</div>
                    <div class="delete-info-value">{{ $coupleInfo->wedding->bride_name }} & {{ $coupleInfo->wedding->groom_name }}</div>
                </div>
            </div>
            <div class="delete-info-row">
                <div class="delete-info-icon"><i class="fas fa-envelope"></i></div>
                <div>
                    <div class="delete-info-label">Email Address</div>
                    <div class="delete-info-value">{{ $coupleInfo->email }}</div>
                </div>
            </div>
            <div class="delete-info-row">
                <div class="delete-info-icon"><i class="far fa-calendar-alt"></i></div>
                <div>
                    <div class="delete-info-label">Wedding Date</div>
                    <div class="delete-info-value">
                        @if ($coupleInfo->wedding && $coupleInfo->wedding->wedding_date)
                            {{ \Carbon\Carbon::parse($coupleInfo->wedding->wedding_date)->format('d F Y') }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning list rules -->
        <div class="delete-warning-list">
            <p><i class="fas fa-shield-alt"></i> Destructive Deletion Checkpoint:</p>
            <ul>
                <li><i class="fas fa-circle"></i> User credentials & system authorizations</li>
                <li><i class="fas fa-circle"></i> Complete wedding profile metadata</li>
                <li><i class="fas fa-circle"></i> All guest reservations & RSVP configurations</li>
                <li><i class="fas fa-circle"></i> Full schedules and event locations</li>
                <li><i class="fas fa-circle"></i> Couple engagement moments (files deleted)</li>
                <li><i class="fas fa-circle"></i> Guest shared candid photos (files deleted)</li>
                <li><i class="fas fa-circle"></i> Planning progress, budgets, & checklist logs</li>
            </ul>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" style="background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.15); color: #ef4444; font-size: 0.82rem; border-radius: 12px; padding: 14px 22px;">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        <!-- Secure DELETE Form -->
        <form method="POST" action="{{ route('admin.delete.destroy', $coupleInfo->id) }}">
            @csrf
            @method('DELETE')
            <div class="delete-actions">
                <a href="{{ route('admin.index') }}" class="btn-cancel-del">
                    <i class="fas fa-arrow-left"></i> Cancel, Safe back
                </a>
                <button type="submit" class="btn-confirm-del">
                    <i class="fas fa-trash-alt"></i> Yes, Delete Everything
                </button>
            </div>
        </form>
    </div>

    <!-- Page Scripts Slot -->
    <x-slot name="scripts">
        <!-- Load Bootstrap JS for Modals -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </x-slot>

</x-app-layout>