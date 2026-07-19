<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Digital Wedding Invitations') | Lumos Studio</title>
    <link rel="icon" type="image/x-icon" href="/images/lumos.jpg">
    <meta name="description" content="Create elegant digital wedding invitations in Sri Lanka with Lumos Studio. Beautiful online wedding invitation websites with RSVP, WhatsApp sharing, countdown timer, photo gallery, event schedule, Google Maps, and multilingual support.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400;1,600&family=Inter:wght@300;400;500;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --gold: #c9a96e; --gold-light: #e8d5a3; --gold-dark: #a07840;
            --dark: #0f0f1a; --dark-2: #1a1a2e; --dark-3: #242440;
            --text-light: #e8e4dc; --text-muted: #9e9aaa; --pink: #d63384; --white: #ffffff;
        }
        html { scroll-behavior: smooth; }
        body { background: var(--dark); font-family: 'Inter', sans-serif; color: var(--text-light); overflow-x: hidden; }
        ::selection { background: var(--gold-light); color: var(--dark); }

        /* =========== NAVBAR =========== */
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 1030; padding: 18px 0; transition: all 0.3s ease; }
        .navbar.scrolled { background: rgba(15,15,26,0.97); border-bottom: 1px solid rgba(201,169,110,0.15); padding: 12px 0; }
        .navbar-brand.nav-logo { font-family: 'Great Vibes', cursive; font-size: 1.9rem; color: var(--gold) !important; }
        .navbar-nav .nav-link { color: var(--text-muted); font-size: 0.9rem; font-weight: 500; letter-spacing: 0.5px; transition: color 0.2s; }
        .navbar-nav .nav-link:hover { color: var(--gold); }
        .btn-nav { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); color: var(--dark) !important; padding: 9px 22px; border-radius: 50px; font-weight: 600; font-size: 0.85rem; transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; display: inline-block; }
        .btn-nav:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(201,169,110,0.35); color: var(--dark) !important; }

        .navbar-toggler { border: none; background: transparent !important; padding: 4px; box-shadow: none !important; }
        .toggler-icon-wrap { width: 24px; height: 18px; position: relative; display: inline-block; }
        .toggler-icon-wrap span { position: absolute; left: 0; width: 100%; height: 2px; background: var(--text-light); border-radius: 2px; transition: transform 0.3s ease, opacity 0.3s ease, top 0.3s ease; }
        .toggler-icon-wrap span:nth-child(1) { top: 0; } .toggler-icon-wrap span:nth-child(2) { top: 8px; } .toggler-icon-wrap span:nth-child(3) { top: 16px; }
        .navbar-toggler[aria-expanded="true"] .toggler-icon-wrap span:nth-child(1) { top: 8px; transform: rotate(45deg); }
        .navbar-toggler[aria-expanded="true"] .toggler-icon-wrap span:nth-child(2) { opacity: 0; }
        .navbar-toggler[aria-expanded="true"] .toggler-icon-wrap span:nth-child(3) { top: 8px; transform: rotate(-45deg); }

        @media (max-width: 991.98px) {
            .navbar-collapse { background: rgba(15,15,26,0.98); margin-top: 14px; border-radius: 16px; padding: 16px; border: 1px solid rgba(201,169,110,0.15); max-height: calc(100vh - 100px); overflow-y: auto; }
            .navbar-nav { gap: 4px; }
            .navbar-nav .nav-link { padding: 12px 16px; border-radius: 8px; text-align: center; }
            .navbar-nav .nav-link:hover { background: rgba(201,169,110,0.08); }
            .navbar-nav .btn-nav { width: 100%; text-align: center; margin-top: 8px; }
        }

        /* Shared Frontend Styles */
        section { padding: 100px 5%; }
        .section-tag { display: inline-block; font-size: 0.72rem; font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase; color: var(--gold); margin-bottom: 14px; }
        .section-title { font-family: 'Cormorant Garamond', serif; font-size: clamp(2rem, 5vw, 3.2rem); font-weight: 400; line-height: 1.2; color: var(--white); margin-bottom: 16px; }
        .section-title em { font-style: italic; color: var(--gold); }
        .section-subtitle { color: var(--text-muted); font-size: 1rem; line-height: 1.7; max-width: 560px; }
        .divider { width: 60px; height: 1px; background: linear-gradient(to right, transparent, var(--gold), transparent); margin: 20px auto; }
        
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* =========== FOOTER =========== */
        footer { text-align: center; padding: 56px 5% 40px; border-top: 1px solid rgba(201,169,110,0.12); display: flex; flex-direction: column; align-items: center; }
        .footer-logo { font-family: 'Great Vibes', cursive; font-size: 1.8rem; color: var(--gold); margin-bottom: 14px; }
        footer p { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; }

        @media (max-width: 1024px) { section { padding: 80px 4%; } }
        @media (max-width: 768px) {
            section { padding: 60px 3%; }
            .section-title { font-size: clamp(1.5rem, 4vw, 2.5rem); margin-bottom: 12px; }
            .section-subtitle { max-width: 100%; }
            footer { padding: 44px 5% 32px; }
        }
        @media (max-width: 480px) {
            section { padding: 50px 3%; }
            .section-tag { font-size: 0.65rem; margin-bottom: 10px; }
            .section-title { font-size: clamp(1.3rem, 4vw, 2.2rem); margin-bottom: 10px; line-height: 1.1; }
            .section-subtitle { font-size: 0.9rem; }
        }
    </style>

    {{ $styles ?? '' }}
</head>
<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar navbar-expand-lg fixed-top" id="navbar">
        <div class="container-fluid px-4 px-lg-5">
            <a href="{{ url('/') }}" class="navbar-brand nav-logo">Lumos Studio</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="toggler-icon-wrap"><span></span><span></span><span></span></span>
            </button>
            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('features') }}">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('template') }}">Template</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/#how-it-works') }}">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                    
                    @auth
                        <li class="nav-item"><a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="btn-nav" href="{{ url('/dashboard') }}">My Account</a></li>
                    @endauth
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Sign In</a></li>
                        @endif
                        @if (Route::has('register'))
                            <li class="nav-item"><a class="btn-nav" href="{{ route('register') }}">Get Started Free</a></li>
                        @endif
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Dynamic Section -->
    @yield('content')

    <!-- ================= FOOTER ================= -->
    <footer>
        <span class="footer-logo">Lumos Studio</span>
        <p>Digital Wedding Invitations in Sri Lanka · Designed by Hathisa Thissara</p>
        <p style="font-size: 0.72rem; color: rgba(158,154,170,0.5); margin-top: 8px;">© {{ date('Y') }} Lumos Studio. All rights reserved.</p>

        <div style="display:flex; gap:16px; margin-top:12px; flex-wrap:wrap; justify-content:center;">
            <a href="{{ route('privacy') }}" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Privacy Policy</a>
            <a href="{{ route('terms') }}" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Terms of Service</a>
            <a href="{{ route('refund') }}" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Refund Policy</a>
        </div>
        <div style="display:flex; gap:16px; margin-top:8px;">
            @auth
                <a href="{{ url('/dashboard') }}" style="color: var(--gold); text-decoration:none; font-size:0.85rem;">My Account</a>
            @endauth
            @guest
                <a href="{{ route('register') }}" style="color: var(--gold); text-decoration:none; font-size:0.85rem;">Get Started</a>
                <a href="{{ route('login') }}" style="color: var(--text-muted); text-decoration:none; font-size:0.85rem;">Sign In</a>
            @endguest
        </div>
    </footer>

    <!-- Bootstrap & Scroll Reveal scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            const reveals = document.querySelectorAll(".reveal");
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            reveals.forEach(el => observer.observe(el));
        });
    </script>

</body>
</html>