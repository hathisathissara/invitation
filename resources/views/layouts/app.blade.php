<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard — Lumos Studio' }}</title>
    <link rel="icon" type="image/x-icon" href="/images/lumos.jpg">
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Great+Vibes&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Layout CSS -->
    <link rel="stylesheet" href="{{ asset('css/app_layout.css') }}?v=1.3">

    {{ $styles ?? '' }}
</head>
<body>

    @php
        $user = auth()->user();
        $wedding = $user->wedding;
        $role = $user->role;
        $status = $user->status;
        $userName = $user->name;

        // Initials for avatar
        $initials = '';
        $parts = explode(' ', $userName);
        foreach (array_slice($parts, 0, 2) as $p) {
            $initials .= strtoupper(mb_substr($p, 0, 1));
        }

        // Invitation URL & Share Message Generation
        $inviteUrl = '';
        $shareMessage = '';
        if ($role !== 'admin' && $wedding && $wedding->slug) {
            $inviteUrl = url('/invitation/' . $wedding->slug);
            
            $ring = "\u{1F48D}";
            $flower = "\u{1F338}"; 
            $heart = "\u{2764}\u{FE0F}";
            
            $shareMessage = $ring . " You're Invited! " . $ring . "\n\n"
                . "With so much love and happiness in our hearts, we're excited to invite you to celebrate the invitation of our journey together - " . $userName . "\n\n"
                . "It would truly mean the world to us on this special day\n\n"
                . "Invitation: " . $inviteUrl . "\n\n"
                . "We can't wait to celebrate, laugh, and create beautiful memories with you! " . $heart;
        }
    @endphp

    <div class="app-layout">
        
        <!-- ================= LEFT SIDEBAR ================= -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('dashboard') }}" class="logo-text">Lumos Studio</a>
                <div class="sidebar-tagline">WEDDING INVITATIONS</div>
            </div>

            <!-- Profile Widget -->
            <div class="profile-widget">
                <div class="profile-avatar">{{ $initials }}</div>
                <div class="profile-info">
                    <h4 class="profile-name" title="{{ $userName }}">{{ $userName }}</h4>
                    <span class="profile-role">{{ $role === 'admin' ? 'Administrator' : 'Couple' }}</span>
                </div>
            </div>

            <!-- ⚠️ PENDING ACTIVATION WARNING BANNER -->
            @if ($role !== 'admin' && $status === 'pending')
                <div class="sidebar-status-banner">
                    <i class="fas fa-exclamation-circle"></i> Account pending activation.<br>
                    <a href="{{ route('payment.index') }}">Activate now →</a>
                </div>
            @endif

            <!-- Navigation Menu -->
            <nav class="sidebar-menu">
               @if ($role === 'admin')
                    <div class="menu-group-label">Administration</div>
                    <a href="{{ route('admin.index') }}" class="menu-item {{ Route::is('admin.index') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i> <span>Admin Panel</span>
                    </a>
                    <a href="{{ route('admin.refunds') }}" class="menu-item {{ Route::is('admin.refunds') ? 'active' : '' }}">
                        <i class="fas fa-rotate-left"></i> <span>Refund Requests</span>
                    </a>
                    <a href="{{ route('admin.upgrades') }}" class="menu-item {{ Route::is('admin.upgrades') ? 'active' : '' }}">
                        <i class="fas fa-arrow-up-right-dots"></i> <span>Upgrade Requests</span>
                    </a>
                @else
                    <div class="menu-group-label">Overview</div>
                    <a href="{{ route('dashboard') }}" class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> <span>Dashboard</span>
                    </a>

                    <div class="menu-group-label">Invitation</div>
                    <a href="{{ route('guests.index') }}" class="menu-item {{ Route::is('guests.index') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> <span>Guest List</span>
                    </a>
                    <a href="{{ route('events.index') }}" class="menu-item {{ Route::is('events.index') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt"></i> <span>Events</span>
                    </a>
                    <a href="{{ route('gallery.index') }}" class="menu-item {{ Route::is('gallery.index') ? 'active' : '' }}">
                        <i class="fas fa-images"></i> <span>Gallery & Story</span>
                    </a>
                    <a href="{{ route('guest-gallery.index') }}" class="menu-item {{ Route::is('guest-gallery.index') ? 'active' : '' }}">
                        <i class="fas fa-camera-retro"></i> <span>Guest Shared Pics</span>
                    </a>
                    <a href="{{ route('customize.index') }}" class="menu-item {{ Route::is('customize.index') ? 'active' : '' }}" class="menu-item">
                        <i class="fas fa-magic"></i> <span>Customize</span>
                    </a>

                    <div class="menu-group-label">Tools</div>
                    <a href="{{ route('tasks.index') }}" class="menu-item {{ Route::is('tasks.index') ? 'active' : '' }}">
                        <i class="fas fa-tasks"></i> <span>Checklist</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="menu-item {{ Route::is('settings.index') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> <span>Settings</span>
                    </a>

                    <a href="{{ route('payment.index') }}" class="menu-item highlight-btn {{ Route::is('payment.index') ? 'active' : '' }}" style="margin-top: 20px;">
                        <i class="fas fa-credit-card"></i> <span>Activate Account</span>
                    </a>
                @endif
            </nav>

            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <a href="#" class="sidebar-logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> <span>Sign Out</span>
                </a>
            </form>
        </aside>

        <!-- ================= MAIN CONTENT WRAPPER ================= -->
        <div class="main-wrapper">
            
            <!-- Topbar Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="topbar-page-title" id="page-title">Dashboard</span>
                </div>

                @if ($role !== 'admin')
                    <div class="topbar-right">
                        @if ($status === 'pending')
                            <!-- 🔒 Guest link is locked - pending activation -->
                            <div class="topbar-lock-badge">
                                <i class="fas fa-lock"></i> Guest Link Locked
                            </div>
                            <a href="{{ route('invitation.invite', ['slug' => $wedding->slug, 'preview' => 1]) }}" target="_blank" class="topbar-btn topbar-btn-outline">
                                <i class="fas fa-eye"></i> Preview Only
                            </a>
                            <a href="{{ route('payment.index') }}" class="topbar-btn topbar-btn-amber">
                                <i class="fas fa-unlock-alt"></i> Activate Now
                            </a>
                        @else
                            <!-- ✅ Active - full access -->
                            <a href="{{ route('invitation.invite', ['slug' => $wedding->slug, 'preview' => 1]) }}" target="_blank" class="topbar-btn topbar-btn-outline">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <button class="topbar-btn topbar-btn-gold" onclick="copyInviteLink()">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        @endif
                    </div>
                @endif
            </header>

            <main class="content-body">
                {{ $slot }}
            </main>

        </div>
    </div>

    <!-- Modals slot -->
    {{ $modals ?? '' }}

    <!-- Shared Toast notification -->
    <div class="toast-notif" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toast-msg">Copied!</span>
    </div>

    <!-- Layout core script logic -->
    <script>
    // Copy invite share message
    function copyInviteLink() {
        const shareText = @json($shareMessage);
        navigator.clipboard.writeText(shareText).then(() => {
            showToast('✓ Copied invitation message');
        }).catch(() => {
            prompt('Copy this invitation message:', shareText);
        });
    }

    function showToast(msg) {
        const toast = document.getElementById('toast');
        document.getElementById('toast-msg').textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Set page title dynamically
    (function() {
        const titles = {
            'dashboard': 'Dashboard',
            'checklist': 'Guest Checklist',
            'events': 'Wedding Events',
            'guests': 'Guest List',
            'gallery': 'Gallery & Story',
            'guest-gallery': 'Shared Moments',
            'settings': 'Account Settings',
            'payment': 'Activate Account'
        };
        const path = window.location.pathname.split('/').pop();
        document.getElementById('page-title').textContent = titles[path] || 'Dashboard';
    })();

    // Mobile sidebar toggle close when click outside
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('.mobile-toggle')) {
            sidebar.classList.remove('open');
        }
    });

    @if ($role !== 'admin')
    // =====================================================================
    // 🔥 සජීවීව Header/Sidebar Status Check කිරීම — 8s Polling
    // =====================================================================
    const headerInitialStatus = @json($status);

    function checkHeaderStatusLive() {
        if (document.hidden) return;

        fetch("{{ route('global.status-check') }}")
            .then(r => r.json())
            .then(data => {
                if (data.status && data.status !== headerInitialStatus) {
                    showToast(data.status === 'active' ? '🎉 Your invitation is now active!' : '⚠️ Your account status has changed');
                    setTimeout(() => location.reload(), 1800);
                }
            })
            .catch(err => console.error('Error checking global status:', err));
    }
    setInterval(checkHeaderStatusLive, 8000);
    @endif
    </script>

    {{ $scripts ?? '' }}

</body>
</html>