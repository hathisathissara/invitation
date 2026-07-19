@extends('site.layouts.app')

@section('title', 'Features — Everything Your Wedding Invitation Can Do')

@section('content')
<!-- Page-specific styles -->
<style>
    /* =========== FEATURES GRID =========== */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-top: 60px;
    }
    .feature-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(201,169,110,0.12);
        border-radius: 20px;
        padding: 32px 28px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(201,169,110,0.4), transparent);
        transform: scaleX(0);
        transition: transform 0.4s ease;
    }
    .feature-card:hover::before { transform: scaleX(1); }
    .feature-card:hover {
        background: rgba(201,169,110,0.06);
        border-color: rgba(201,169,110,0.25);
        transform: translateY(-4px);
    }
    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(201,169,110,0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 1.2rem;
        color: var(--gold);
    }
    .feature-card h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--white);
        margin-bottom: 10px;
    }
    .feature-card p {
        color: var(--text-muted);
        font-size: 0.88rem;
        line-height: 1.65;
    }

    /* =========== PRICING / CTA =========== */
    .cta-section {
        text-align: center;
        background: radial-gradient(ellipse 80% 60% at 50% 50%, rgba(201,169,110,0.1) 0%, transparent 70%);
        border-top: 1px solid rgba(201,169,110,0.1);
    }
    .cta-section h2 {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(2.5rem, 6vw, 4rem);
        font-weight: 300;
        color: var(--white);
        margin-bottom: 16px;
    }
    .cta-section h2 em { font-style: italic; color: var(--gold); }
    .cta-section p {
        color: var(--text-muted);
        font-size: 1rem;
        margin-bottom: 40px;
    }
    .cta-features {
        display: flex;
        gap: 24px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 40px;
    }
    .cta-feature {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .cta-feature i { color: var(--gold); }
     .btn-primary-gold {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--gold), var(--gold-dark));
        color: var(--dark);
        padding: 14px 32px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(201,169,110,0.3);
    }
    .btn-primary-gold:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(201,169,110,0.45);
        color: var(--dark);
    }
    @media (max-width: 768px) {
        .features-grid { grid-template-columns: 1fr; gap: 16px; margin-top: 40px; }
        .feature-card { padding: 24px 20px; }
        .btn-primary-gold{ width: 100%; justify-content: center; }
    }
    @media (max-width: 480px) {
        .feature-card { padding: 20px 16px; border-radius: 16px; }
        .feature-icon { width: 40px; height: 40px; margin-bottom: 16px; font-size: 1rem; }
        .feature-card h3 { font-size: 1.15rem; margin-bottom: 8px; }
        .feature-card p { font-size: 0.8rem; }
    }
</style>

<!-- INVITATION FEATURES -->
<section id="features" style="padding-top:150px;">
    <div style="text-align:center; margin-bottom: 20px;">
        <span class="section-tag reveal">Inside the Invitation</span>
        <h2 class="section-title reveal">Everything your guests<br><em>will love</em></h2>
        <div class="divider"></div>
        <p class="section-subtitle reveal" style="margin: 0 auto;">Every detail, beautifully crafted — from personalized greetings to one-tap directions.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-user-tag"></i></div>
            <h3>Personalized Guest Names</h3>
            <p>Each guest opens an invitation with their own name on it — a personal touch that makes them feel truly invited.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fab fa-whatsapp"></i></div>
            <h3>One Link, Easy Sharing</h3>
            <p>Share one invitation link via WhatsApp to all your guests. Each guest enters their number to open their personal invitation.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-eye"></i></div>
            <h3>Live Open Tracking</h3>
            <p>See which guests have opened their invitation and exactly when — in real time from your dashboard.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-reply"></i></div>
            <h3>Live RSVP Dashboard</h3>
            <p>See who's attending, who can't come, and any dietary notes — all in one live, filterable dashboard.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3>All Events in One Place</h3>
            <p>Poruwa, Church, Reception, Homecoming — linked in one invitation with timelines and venue details.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-clock"></i></div>
            <h3>Live Countdown Timer</h3>
            <p>A real-time countdown to your big day — visible on every guest's invitation, building the excitement.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-map-marker-alt"></i></div>
            <h3>Google Maps Directions</h3>
            <p>One tap from the invitation opens turn-by-turn directions for each venue. No address confusion.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-calendar-plus"></i></div>
            <h3>Add to Calendar</h3>
            <p>Guests save the date to Google, Apple, or Outlook with a single tap — no one forgets the day.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
            <h3>QR Code for Printed Cards</h3>
            <p>Print a scan-to-open code on physical cards so paper and digital work together seamlessly.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-images"></i></div>
            <h3>Photo Gallery & Love Story</h3>
            <p>Share engagement photos and how you met — give guests a feel for your beautiful story before the day.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
            <h3>Beautiful on Any Phone</h3>
            <p>From your aunty's older phone to the latest iPhone — every guest opens it instantly, on slow or fast data.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
            <h3>Per-Event Guest Assignment</h3>
            <p>Invite guests to only the events that apply — Poruwa, Reception, or Homecoming separately.</p>
        </div>
    </div>
</section>

<!-- PLANNING TOOLS -->
<section>
    <div style="text-align:center; margin-bottom:20px;">
        <span class="section-tag reveal">Plan the Day</span>
        <h2 class="section-title reveal">Wedding planning tools<br><em>alongside your invitation</em></h2>
        <div class="divider"></div>
    </div>
    <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-tasks"></i></div>
            <h3>Task Checklist</h3>
            <p>Track what's done and what's coming — from saree fitting to thank-you notes.</p>
        </div>
        <div class="feature-card reveal">
            <div class="feature-icon"><i class="fas fa-tags"></i></div>
            <h3>Guest Categories</h3>
            <p>Tag guests by group (Family, Friends, Office, VIP) and side (Bride, Groom, Both) for easy filtering.</p>
        </div>
        <a href="/template" class="feature-card reveal" style="text-decoration:none; display:block; color:inherit;">
            <div class="feature-icon"><i class="fas fa-palette"></i></div>
            <h3>Choose From 9 Themes</h3>
            <p>Pick from 9 beautiful invitation themes and preview each one live before you publish your final design. <span style="color:var(--gold); font-weight:600;">See all themes ↓</span></p>
        </a>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" id="cta">
    <span class="section-tag reveal">Ready to Begin?</span>
    <h2 class="reveal">Start for <em>free</em> today</h2>
    <p class="reveal">Build your full invitation and preview it completely — no payment until you're ready.</p>
    <div class="reveal">
        <a href="/register" class="btn-primary-gold" style="font-size:1rem; padding:16px 40px;">
            <i class="fas fa-heart"></i> Create My Wedding Invitation
        </a>
    </div>
    <div class="cta-features reveal">
        <div class="cta-feature"><i class="fas fa-check"></i> Free to build & preview</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Pay only when ready</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Edit forever after</div>
        <div class="cta-feature"><i class="fas fa-check"></i> Beautiful on any phone</div>
    </div>
</section>
@endsection