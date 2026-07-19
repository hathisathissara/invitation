<!DOCTYPE html>
<html lang="{{ $wedding->invite_language ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wedding->bride_name }} &amp; {{ $wedding->groom_name }} — Wedding Invitation</title>
    <meta name="description" content="You're invited! Open your personal wedding invitation.">
    
    <!-- DYNAMIC OPEN GRAPH METADATA (WhatsApp Rich Previews) -->
    <meta property="og:title" content="{{ $wedding->bride_name }} &amp; {{ $wedding->groom_name }} — Wedding Invitation" />
    <meta property="og:description" content="Together with our families, we joyfully invite you to celebrate our wedding. Click to open your personal digital invitation." />
    <!-- Dynamic og:image generated based on whether hero cover image exists -->
    <meta property="og:image" content="{{ !empty($wedding->hero_image) ? asset($wedding->hero_image) : asset('uploads/lumos.jpg') }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $wedding->bride_name }} &amp; {{ $wedding->groom_name }} — Wedding Invitation">
    <meta name="twitter:description" content="You're invited! Open your personal digital wedding invitation.">
    <meta name="twitter:image" content="{{ !empty($wedding->hero_image) ? asset($wedding->hero_image) : asset('uploads/lumos.jpg') }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=Great+Vibes&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- ThreeJS WebGL engine -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root{
            --emerald-950:{{ $themeColors['c_env_dark'] }};
            --emerald-900:{{ $themeColors['c_env_mid'] }};
            --emerald-800:{{ $themeColors['c_env_light'] }};
            --gold:{{ $themeColors['c_accent'] }};
            --gold-light:{{ $themeColors['c_accent_light'] }};
            --gold-soft:rgba({{ $themeColors['c_accent_rgb'] }},0.35);
            --gold-rgb:{{ $themeColors['c_accent_rgb'] }};
            --gold-light-rgb:{{ $themeColors['c_accent_light_rgb'] }};
            --cream:{{ $themeColors['c_paper'] }};
            --paper-a:{{ $themeColors['c_paper'] }};
            --paper-b:{{ $themeColors['c_paper2'] }};
            --ink:{{ $themeColors['c_ink'] }};
            --ink-rgb:{{ $themeColors['c_ink_rgb'] }};
            --paper-accent-strong:{{ $themeColors['c_paper_accent_strong'] }};
            --paper-accent-mid:{{ $themeColors['c_paper_accent_mid'] }};
            --paper-accent-rgb:{{ $themeColors['c_paper_accent_rgb'] }};
            --seal-ink:{{ $themeColors['c_seal_ink'] }};
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--emerald-900);
            opacity: 1;
            transition: opacity 0.6s ease;
            background-image:
                radial-gradient(circle at 12% 15%, rgba(var(--gold-rgb),0.10), transparent 40%),
                radial-gradient(circle at 88% 85%, rgba(var(--gold-rgb),0.08), transparent 40%),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240' viewBox='0 0 240 240'%3E%3Cg fill='none' stroke='%23{{ ltrim($themeColors['c_accent'], '#') }}' stroke-width='1.1' opacity='0.14'%3E%3Cpath d='M20 220 C 8 175, 44 165, 38 120 C 32 75, 66 65, 60 20'/%3E%3Cpath d='M38 120 C 60 114, 72 96, 66 74'/%3E%3Cpath d='M38 165 C 60 162, 74 148, 68 129'/%3E%3Ccircle cx='60' cy='20' r='4.5'/%3E%3Ccircle cx='66' cy='74' r='3'/%3E%3Cpath d='M210 20 C 222 65, 188 75, 194 120 C 200 165, 166 175, 172 220'/%3E%3Cpath d='M194 120 C 172 114, 160 96, 166 74'/%3E%3Cpath d='M194 165 C 172 162, 158 148, 164 129'/%3E%3Ccircle cx='172' cy='220' r='4.5'/%3E%3Ccircle cx='166' cy='74' r='3'/%3E%3C/g%3E%3C/svg%3E");
            background-size: auto, auto, 240px 240px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        #theme3d-canvas { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 0; pointer-events: none; opacity: 0; transition: opacity 1.2s ease; }
        #theme3d-canvas.ready { opacity: 1; }

        .bg-glow { position: fixed; border-radius: 50%; filter: blur(90px); pointer-events: none; }
        .glow-1 { width: 480px; height: 480px; background: radial-gradient(circle, rgba(var(--gold-rgb),0.10), transparent); top: -120px; right: -120px; }
        .glow-2 { width: 420px; height: 420px; background: radial-gradient(circle, rgba(var(--gold-rgb),0.06), transparent); bottom: -100px; left: -100px; }

        .particles { position: fixed; inset: 0; pointer-events: none; overflow: hidden; }
        .particle { position: absolute; width: 2px; height: 2px; border-radius: 50%; background: rgba(var(--gold-light-rgb),0.5); animation: drift linear infinite; }
        @keyframes drift {
            from { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            to { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }

        /* Stage */
        .stage { position: relative; width: min(440px, 90vw); z-index: 1; text-align: center; }
        .stage-label { font-family:'Cormorant Garamond',serif; font-size:0.82rem; letter-spacing:5px; text-transform:uppercase; color:rgba(var(--gold-light-rgb),0.75); margin-bottom:22px; transition:opacity .4s ease; }

        /* Envelope */
        .envelope-wrap { position: relative; perspective: 1600px; --flap-h: clamp(108px, 36vw, 168px); }
        .envelope { position: relative; background: linear-gradient(155deg, var(--emerald-800), var(--emerald-800) 55%, var(--emerald-950)); border: 1px solid var(--gold-soft); border-radius: 10px; box-shadow: 0 30px 90px rgba(0,0,0,0.55), inset 0 1px 0 rgba(var(--gold-rgb),0.12); padding-top: var(--flap-h); min-height: calc(var(--flap-h) * 1.6); animation: envIn 0.7s ease; overflow: hidden; transition: min-height 0.6s ease, padding-top 0.6s ease; }
        @keyframes envIn { from { opacity: 0; transform: translateY(40px) scale(0.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .envelope-face-text { position:absolute; top: calc(var(--flap-h) * 0.22); left:0; right:0; text-align:center; font-family:'Cormorant Garamond',serif; font-style:italic; font-size: clamp(0.95rem, 3.4vw, 1.15rem); letter-spacing:1px; color:rgba(247,241,227,0.55); z-index:1; transition:opacity .35s ease; }
        .envelope-flap { position: absolute; top: 0; left: 0; right: 0; height: var(--flap-h); overflow: visible; transform-origin: top center; transform-style: preserve-3d; transition: transform 0.9s cubic-bezier(.6,-0.1,.35,1.4); z-index: 3; }
        .envelope-flap svg { width: 100%; height: 100%; display: block; }

        /* Wax Seal */
        .wax-seal { position: absolute; top: calc(var(--flap-h) * 0.7); left: 50%; transform: translate(-50%, -50%); width: clamp(50px, 15vw, 62px); height: clamp(50px, 15vw, 62px); border-radius: 50%; background: radial-gradient(circle at 35% 30%, var(--gold-light), var(--gold) 60%, var(--seal-ink) 100%); box-shadow: 0 8px 18px rgba(0,0,0,0.5), inset 0 2px 3px rgba(255,255,255,0.35); display: flex; align-items: center; justify-content: center; color: var(--seal-ink); font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: clamp(0.82rem, 2.6vw, 1rem); letter-spacing: 0.5px; cursor: pointer; z-index: 4; transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .wax-seal:hover { transform: translate(-50%, -50%) scale(1.07); box-shadow: 0 10px 22px rgba(0,0,0,0.55), inset 0 2px 3px rgba(255,255,255,0.4); }
        .wax-seal.cracked { animation: crack 0.4s ease forwards; }
        @keyframes crack { 0% { transform: translate(-50%,-50%) scale(1); } 40% { transform: translate(-50%,-50%) scale(1.2); filter: brightness(1.25); } 100% { transform: translate(-50%,-50%) scale(0); opacity: 0; } }

        .tap-hint { margin-top:22px; font-family:'Cormorant Garamond',serif; font-size:0.78rem; letter-spacing:4px; text-transform:uppercase; color:rgba(var(--gold-light-rgb),0.55); transition:opacity .4s ease; }

        /* Inside Letter Card */
        .card { position: relative; background: linear-gradient(180deg, var(--paper-a), var(--paper-b)); border-radius: 0 0 9px 9px; padding: 0 clamp(24px, 8vw, 40px); text-align: center; z-index: 1; opacity: 0; max-height: 0; overflow: hidden; transform: translateY(-16px); transition: opacity 0.5s ease 0.3s, transform 0.5s ease 0.3s, max-height 0.7s ease, padding 0.7s ease; }
        .card-top-line { position: absolute; top: 0; left: 10%; right: 10%; height: 1px; background: linear-gradient(to right, transparent, var(--gold-soft), transparent); }

        .greeting { font-family:'Cormorant Garamond',serif; font-size: 0.85rem; color: var(--paper-accent-mid); letter-spacing: 3.5px; text-transform: uppercase; margin-bottom: 18px; }
        .couple-names { font-family: 'Great Vibes', cursive; font-size: clamp(2.3rem, 9vw, 3.1rem); color: var(--paper-accent-strong); line-height: 1.2; margin-bottom: 4px; }
        .ampersand { display: block; font-size: 1.4rem; color: rgba(var(--paper-accent-rgb),0.55); margin: -6px 0; font-family:'Cormorant Garamond',serif; }
        .wedding-date { font-family: 'Cormorant Garamond', serif; font-size: 0.92rem; color: rgba(var(--ink-rgb),0.55); letter-spacing: 2px; margin-bottom: 30px; text-transform: uppercase; }

        .divider { display: flex; align-items: center; gap: 12px; margin-bottom: 26px; }
        .divider-line { flex: 1; height: 1px; background: rgba(var(--paper-accent-rgb),0.3); }
        .divider-icon { color: rgba(var(--paper-accent-rgb),0.55); font-size: 0.75rem; }

        .instruction { font-size: 0.86rem; color: rgba(var(--ink-rgb),0.65); margin-bottom: 18px; line-height: 1.6; }

        /* Form Inputs */
        .input-group { position: relative; margin-bottom: 14px; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: rgba(var(--paper-accent-rgb),0.6); font-size: 0.95rem; z-index: 1; }
        input[type="text"] { width: 100%; background: rgba(255,255,255,0.6); border: 1px solid rgba(var(--paper-accent-rgb),0.3); border-radius: 12px; padding: 15px 18px 15px 46px; color: var(--ink); font-size: 1.05rem; font-family: 'Inter', sans-serif; font-weight: 400; letter-spacing: 1.2px; text-align: center; transition: all 0.3s; outline: none; }
        input[type="text"]::placeholder { color: rgba(var(--ink-rgb),0.3); letter-spacing: 0; font-size: 0.88rem; }
        input[type="text"]:focus { border-color: var(--gold); background: #fff; box-shadow: 0 0 0 3px rgba(var(--gold-rgb),0.15); }

        .error-msg { background: rgba(139,26,26,0.08); border: 1px solid rgba(139,26,26,0.25); border-radius: 10px; padding: 11px 15px; color: #7a1c1c; font-size: 0.83rem; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; text-align: left; }

        .btn-open { width: 100%; background: linear-gradient(135deg, var(--gold-light), var(--gold) 60%, #a3821e); color: var(--seal-ink); border: none; border-radius: 12px; padding: 15px; font-size: 0.9rem; font-weight: 700; font-family: 'Inter', sans-serif; letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; transition: all 0.3s; position: relative; overflow: hidden; }
        .btn-open::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(to right, transparent, rgba(255,255,255,0.35), transparent); transition: left 0.4s; }
        .btn-open:hover::before { left: 100%; }
        .btn-open:hover { transform: translateY(-2px); box-shadow: 0 10px 26px rgba(var(--gold-rgb),0.4); }
        .btn-open:disabled { opacity: 0.6; cursor: default; transform: none; box-shadow: none; }

        .hint { margin-top: 18px; font-size: 0.76rem; color: rgba(var(--ink-rgb),0.45); line-height: 1.6; }

        .owner-banner { background: rgba(var(--gold-rgb),0.1); border: 1px solid rgba(var(--gold-rgb),0.3); border-radius: 12px; padding: 11px 15px; margin-bottom: 20px; font-size: 0.8rem; color: var(--paper-accent-strong); }
        .owner-banner a { color: var(--paper-accent-strong); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }

        .redirect-note { margin-top: 16px; font-size: 0.78rem; color: var(--paper-accent-mid); letter-spacing: 0.5px; }

        /* Opening States */
        body.opening .envelope-flap { transform: rotateX(180deg); z-index: 0; }
        body.opening .wax-seal { animation: crack 0.4s ease forwards; }
        body.opening .envelope { min-height: 0; padding-top: 0; }
        body.opening .card { opacity: 1; transform: translateY(0); max-height: 2000px; padding: clamp(26px, 7vw, 34px) clamp(24px, 8vw, 40px) clamp(36px, 9vw, 46px); }
        body.opening .envelope-face-text, body.opening .stage-label, body.opening .tap-hint { opacity: 0; }

        body.page-fade-out { opacity: 0; }
    </style>
</head>
<body>

@if (request()->has('preview_template'))
<div style="position:fixed; top:0; left:0; right:0; z-index:9999; background:#1a1a2e; color:#c9a96e; text-align:center; padding:8px 14px; font-family:sans-serif; font-size:0.82rem; letter-spacing:0.5px;">
    <i class="fas fa-eye" style="margin-right:6px;"></i>
    Previewing "{{ $wedding->template_name }}" template — this is a test view only, nothing is saved.
</div>
@endif

<canvas id="theme3d-canvas" aria-hidden="true"></canvas>
<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>
<div class="particles" id="particles"></div>

<div class="stage">
    <p class="stage-label" id="stageLabel">{{ __('invitation.stage_you_are_invited') }}</p>

    <div class="envelope-wrap">
        <div class="envelope" id="envelope">

            <p class="envelope-face-text">{{ __('invitation.envelope_dear_guest') }}</p>

            <div class="envelope-flap" id="envelopeFlap">
                <svg viewBox="0 0 440 160" preserveAspectRatio="none">
                    <polygon points="0,0 440,0 220,150" style="fill:var(--emerald-800); stroke:rgba(var(--gold-rgb),0.3); stroke-width:1.5;"/>
                </svg>
            </div>

            @php
                $monogram = mb_strtoupper(mb_substr($wedding->bride_name, 0, 1)) . ' · ' . mb_strtoupper(mb_substr($wedding->groom_name, 0, 1));
            @endphp
            <div class="wax-seal" id="waxSeal" title="Tap to unseal">{{ $monogram }}</div>

            <div class="card">
                <div class="card-top-line"></div>

                @if ($isOwner)
                <div class="owner-banner">
                    <i class="fas fa-user-shield" style="margin-right:6px;"></i>You're viewing as the couple.
                    <br><a href="{{ route('invitation.view', ['slug' => $wedding->slug, 'preview' => 1]) }}">
                        <i class="fas fa-eye"></i> Click to Preview Invitation →
                    </a>
                </div>
                @endif

                <p class="greeting">{{ __('invitation.the_wedding_of') }}</p>

                <div class="couple-names">
                    {{ $wedding->bride_name }}
                    <span class="ampersand">&amp;</span>
                    {{ $wedding->groom_name }}
                </div>

                @php
                    $formattedDate = date("d", strtotime($wedding->wedding_date)) . ' ' . \Carbon\Carbon::parse($wedding->wedding_date)->translatedFormat('F') . ' ' . date("Y", strtotime($wedding->wedding_date));
                @endphp
                <p class="wedding-date">{{ $formattedDate }}</p>

                <div class="divider">
                    <div class="divider-line"></div>
                    <div class="divider-icon"><i class="fas fa-heart"></i></div>
                    <div class="divider-line"></div>
                </div>

                @if (!$justVerified)
                <p class="instruction">
                    {{ __('invitation.enter_whatsapp') }}
                </p>

                @if (session('error') || $errors->any())
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') ?: $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('invitation.verify', $wedding->slug) }}" id="inviteForm">
                    @csrf
                    <div class="input-group">
                        <i class="input-icon fab fa-whatsapp"></i>
                        <input
                            type="text"
                            name="whatsapp_number"
                            id="whatsapp_input"
                            placeholder="e.g. 0771234567"
                            required
                            autocomplete="tel"
                            inputmode="numeric"
                            value="{{ old('whatsapp_number') }}"
                        >
                    </div>
                    <button type="submit" class="btn-open" id="openBtn">
                        <i class="fas fa-envelope-open-text" style="margin-right:8px;"></i> {{ __('invitation.open_invitation_btn') }}
                    </button>
                </form>

                <p class="hint">
                    <i class="fas fa-lock" style="margin-right:4px;"></i>
                    {{ __('invitation.number_privacy_hint') }}
                </p>
                @else
                <p class="redirect-note"><i class="fas fa-spinner fa-spin"></i> {{ __('invitation.opening_invitation') }}</p>
                @endif
            </div>
        </div>
    </div>

    <p class="tap-hint" id="tapHint">{{ __('invitation.tap_seal_to_open') }}</p>
</div>

<script>
// Generate floating particles
const container = document.getElementById('particles');
for (let i = 0; i < 18; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    p.style.left = Math.random() * 100 + 'vw';
    p.style.width = p.style.height = (Math.random() * 3 + 1) + 'px';
    p.style.animationDuration = (Math.random() * 15 + 10) + 's';
    p.style.animationDelay = (Math.random() * 10) + 's';
    container.appendChild(p);
}

const waxSeal = document.getElementById('waxSeal');
const whatsappInput = document.getElementById('whatsapp_input');
const openBtn = document.getElementById('openBtn');

let sealCracked = false;
function openInvitationFlow() {
    if (sealCracked) return;
    sealCracked = true;
    waxSeal.classList.add('cracked');
    document.body.classList.add('opening');

    @if ($justVerified)
    if (openBtn) openBtn.disabled = true;
    setTimeout(() => {
        document.body.classList.add('page-fade-out');
        setTimeout(() => {
            @if (request()->has('preview_template'))
                window.location.href = "{{ route('invitation.view', ['slug' => $wedding->slug, 'preview_template' => request('preview_template')]) }}";
            @else
                window.location.href = "{{ route('invitation.view', $wedding->slug) }}";
            @endif
        }, 600);
    }, 3000);
    @else
    setTimeout(() => whatsappInput && whatsappInput.focus(), 350);
    @endif
}

waxSeal.addEventListener('click', openInvitationFlow);

@if ($justVerified && $verifiedViaForm)
openInvitationFlow();
@endif
</script>

<script>
(function initTheme3D() {
    const canvas = document.getElementById('theme3d-canvas');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!canvas || typeof THREE === 'undefined' || prefersReducedMotion) return;

    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 0, 9);

    function resize() {
        const w = window.innerWidth, h = window.innerHeight;
        renderer.setSize(w, h);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    resize();
    window.addEventListener('resize', resize);

    let mouseX = 0, mouseY = 0;
    window.addEventListener('mousemove', (e) => {
        mouseX = (e.clientX / window.innerWidth - 0.5);
        mouseY = (e.clientY / window.innerHeight - 0.5);
    });

    const ACCENT       = 0x{{ $themeColors['hex_accent'] }};
    const ACCENT_LIGHT = 0x{{ $themeColors['hex_accent_light'] }};
    const PRIMARY      = 0x{{ $themeColors['hex_primary'] }};
    const INK          = 0x{{ $themeColors['hex_ink'] }};

    const clock = new THREE.Clock();

    @if ($wedding->template_name === 'beach_tropical')
    const goldMat = new THREE.MeshStandardMaterial({ color: ACCENT, metalness: 0.85, roughness: 0.28, emissive: PRIMARY, emissiveIntensity: 0.35 });
    const ringGroup = new THREE.Group();
    const ring1 = new THREE.Mesh(new THREE.TorusGeometry(3.1, 0.045, 24, 120), goldMat);
    ring1.rotation.x = Math.PI / 2.3;
    ringGroup.add(ring1);
    const ring2 = new THREE.Mesh(new THREE.TorusGeometry(2.3, 0.03, 24, 120), goldMat.clone());
    ring2.rotation.x = Math.PI / 3.1; ring2.rotation.y = Math.PI / 6;
    ringGroup.add(ring2);
    scene.add(ringGroup);

    const starCount = 220;
    const starPos = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        starPos[i * 3] = (Math.random() - 0.5) * 16;
        starPos[i * 3 + 1] = (Math.random() - 0.5) * 10;
        starPos[i * 3 + 2] = (Math.random() - 0.5) * 10 - 2;
    }
    const starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
    const stars = new THREE.Points(starGeo, new THREE.PointsMaterial({ color: ACCENT_LIGHT, size: 0.035, transparent: true, opacity: 0.7 }));
    scene.add(stars);

    const smallRingGeo = new THREE.TorusGeometry(0.16, 0.02, 12, 32);
    const smallRingMat = new THREE.MeshStandardMaterial({ color: ACCENT_LIGHT, metalness: 0.8, roughness: 0.3, emissive: PRIMARY, emissiveIntensity: 0.5, transparent: true, opacity: 0.85 });
    const fallingRings = [];
    for (let i = 0; i < 26; i++) {
        const mesh = new THREE.Mesh(smallRingGeo, smallRingMat.clone());
        mesh.position.set((Math.random() - 0.5) * 14, Math.random() * 12 - 4, (Math.random() - 0.5) * 6 - 2);
        mesh.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
        mesh.scale.setScalar(0.6 + Math.random() * 0.8);
        mesh.userData = { fallSpeed: 0.3 + Math.random() * 0.45, swaySpeed: 0.4 + Math.random() * 0.7, swayAmp: 0.5 + Math.random() * 0.7, rotSpeed: (Math.random() - 0.5) * 0.5, baseX: mesh.position.x };
        fallingRings.push(mesh);
        scene.add(mesh);
    }

    scene.add(new THREE.AmbientLight(0x8899cc, 0.55));
    const key = new THREE.PointLight(ACCENT, 2.2, 30); key.position.set(5, 4, 6); scene.add(key);
    const rim = new THREE.PointLight(PRIMARY, 1.4, 30); rim.position.set(-6, -3, -4); scene.add(rim);

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        ringGroup.rotation.z = t * 0.15;
        ringGroup.rotation.y = t * 0.08 + mouseX * 0.4;
        ringGroup.rotation.x = Math.PI / 2.3 + mouseY * 0.2;
        stars.rotation.y = t * 0.02;
        fallingRings.forEach(r => {
            r.position.y -= r.userData.fallSpeed * 0.016;
            r.position.x = r.userData.baseX + Math.sin(t * r.userData.swaySpeed) * r.userData.swayAmp;
            r.rotation.z += r.userData.rotSpeed * 0.016;
            if (r.position.y < -6) r.position.y = 6;
        });
        camera.position.x += (mouseX * 1.2 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 1.2 - camera.position.y) * 0.02;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();

    @elseif ($wedding->template_name === 'floral_garden')
    scene.add(new THREE.AmbientLight(0xffe4ec, 0.9));
    const key = new THREE.PointLight(ACCENT_LIGHT, 1.6, 30); key.position.set(4, 5, 6); scene.add(key);

    function makePetalGeometry() {
        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.bezierCurveTo(0.35, 0.15, 0.4, 0.55, 0, 0.85);
        shape.bezierCurveTo(-0.4, 0.55, -0.35, 0.15, 0, 0);
        return new THREE.ShapeGeometry(shape);
    }
    const petalGeo = makePetalGeometry();
    const petalColors = [ACCENT_LIGHT, PRIMARY, ACCENT, 0xffffff];
    const petals = [];
    for (let i = 0; i < 46; i++) {
        const mat = new THREE.MeshStandardMaterial({ color: petalColors[i % petalColors.length], side: THREE.DoubleSide, roughness: 0.6, metalness: 0.05, transparent: true, opacity: 0.9 });
        const mesh = new THREE.Mesh(petalGeo, mat);
        mesh.position.set((Math.random() - 0.5) * 14, Math.random() * 12 - 4, (Math.random() - 0.5) * 6 - 2);
        mesh.rotation.z = Math.random() * Math.PI * 2;
        mesh.scale.setScalar(0.5 + Math.random() * 0.7);
        mesh.userData = { fallSpeed: 0.35 + Math.random() * 0.5, swaySpeed: 0.5 + Math.random() * 0.8, swayAmp: 0.6 + Math.random() * 0.8, rotSpeed: (Math.random() - 0.5) * 0.6, baseX: mesh.position.x };
        petals.push(mesh);
        scene.add(mesh);
    }

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        petals.forEach(p => {
            p.position.y -= p.userData.fallSpeed * 0.016;
            p.position.x = p.userData.baseX + Math.sin(t * p.userData.swaySpeed) * p.userData.swayAmp;
            p.rotation.z += p.userData.rotSpeed * 0.016;
            if (p.position.y < -6) p.position.y = 6;
        });
        camera.position.x += (mouseX * 1 - camera.position.x) * 0.02;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();

    @elseif ($wedding->template_name === 'indian_royal')
    const goldMat = new THREE.MeshStandardMaterial({ color: ACCENT, metalness: 0.9, roughness: 0.25, emissive: PRIMARY, emissiveIntensity: 0.35 });
    const mandala = new THREE.Group();
    [{ r: 3.4, t: 0.05, seg: 128 }, { r: 2.7, t: 0.035, seg: 96 }, { r: 2.0, t: 0.03, seg: 72 }].forEach((def, i) => {
        const ring = new THREE.Mesh(new THREE.TorusGeometry(def.r, def.t, 16, def.seg), goldMat.clone());
        ring.rotation.x = Math.PI / 2.4 + i * 0.15;
        ring.userData = { spinDir: i % 2 === 0 ? 1 : -1, speed: 0.05 + i * 0.03 };
        mandala.add(ring);
    });
    const jewelMat = new THREE.MeshStandardMaterial({ color: PRIMARY, metalness: 0.6, roughness: 0.2, emissive: PRIMARY, emissiveIntensity: 0.5 });
    for (let i = 0; i < 12; i++) {
        const angle = (i / 12) * Math.PI * 2;
        const jewel = new THREE.Mesh(new THREE.OctahedronGeometry(0.14, 0), jewelMat);
        jewel.position.set(Math.cos(angle) * 3.4, Math.sin(angle) * 3.4, 0);
        mandala.add(jewel);
    }
    mandala.rotation.x = Math.PI / 2.4;
    scene.add(mandala);

    function makeRosePetalShape() {
        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.bezierCurveTo(0.26, 0.11, 0.3, 0.42, 0, 0.6);
        shape.bezierCurveTo(-0.3, 0.42, -0.26, 0.11, 0, 0);
        return new THREE.ShapeGeometry(shape);
    }
    const rosePetalGeo = makeRosePetalShape();
    function createRose(color) {
        const rose = new THREE.Group();
        const mat = new THREE.MeshStandardMaterial({ color, roughness: 0.45, metalness: 0.05, side: THREE.DoubleSide, transparent: true, opacity: 0.92 });
        for (let j = 0; j < 5; j++) {
            const petal = new THREE.Mesh(rosePetalGeo, mat);
            petal.rotation.z = (j / 5) * Math.PI * 2;
            rose.add(petal);
        }
        return rose;
    }
    const roseColors = [PRIMARY, ACCENT, INK];
    const roses = [];
    const rosesGroup = new THREE.Group();
    for (let i = 0; i < 22; i++) {
        const rose = createRose(roseColors[i % roseColors.length]);
        rose.position.set((Math.random() - 0.5) * 14, Math.random() * 12 - 4, (Math.random() - 0.5) * 6 - 2);
        rose.rotation.z = Math.random() * Math.PI * 2;
        rose.scale.setScalar(0.45 + Math.random() * 0.55);
        rose.userData = { fallSpeed: 0.28 + Math.random() * 0.4, swaySpeed: 0.4 + Math.random() * 0.7, swayAmp: 0.5 + Math.random() * 0.7, rotSpeed: (Math.random() - 0.5) * 0.5, baseX: rose.position.x };
        roses.push(rose);
        rosesGroup.add(rose);
    }
    scene.add(rosesGroup);

    const starCount = 200;
    const starPos = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        starPos[i * 3] = (Math.random() - 0.5) * 16;
        starPos[i * 3 + 1] = (Math.random() - 0.5) * 10;
        starPos[i * 3 + 2] = (Math.random() - 0.5) * 10 - 2;
    }
    const starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
    const stars = new THREE.Points(starGeo, new THREE.PointsMaterial({ color: ACCENT_LIGHT, size: 0.035, transparent: true, opacity: 0.7 }));
    scene.add(stars);

    scene.add(new THREE.AmbientLight(0xcc8899, 0.5));
    const key = new THREE.PointLight(ACCENT, 2.4, 30); key.position.set(5, 4, 6); scene.add(key);
    const rim = new THREE.PointLight(PRIMARY, 1.6, 30); rim.position.set(-6, -3, -4); scene.add(rim);

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        mandala.children.forEach(c => { if (c.userData.speed) c.rotation.z += c.userData.speed * 0.016 * c.userData.spinDir; });
        mandala.rotation.y = t * 0.08 + mouseX * 0.4;
        mandala.rotation.x = Math.PI / 2.4 + mouseY * 0.2;
        stars.rotation.y = t * 0.015;
        roses.forEach(r => {
            r.position.y -= r.userData.fallSpeed * 0.016;
            r.position.x = r.userData.baseX + Math.sin(t * r.userData.swaySpeed) * r.userData.swayAmp;
            r.rotation.z += r.userData.rotSpeed * 0.016;
            if (r.position.y < -6) r.position.y = 6;
        });
        camera.position.x += (mouseX * 1.2 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 1.2 - camera.position.y) * 0.02;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();

    @elseif ($wedding->template_name === 'minimal_light')
    scene.add(new THREE.AmbientLight(0xfff3e0, 0.8));
    const key = new THREE.PointLight(ACCENT, 1.6, 30); key.position.set(4, 5, 6); scene.add(key);
    scene.add(new THREE.PointLight(0xffffff, 0.8, 30));

    const count = 80;
    const positions = new Float32Array(count * 3);
    const velocities = [];
    for (let i = 0; i < count; i++) {
        positions[i * 3] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 20;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 6;
        velocities.push({ x: (Math.random() - 0.5) * 0.004, y: (Math.random() - 0.5) * 0.004 });
    }
    const dotGeo = new THREE.BufferGeometry();
    dotGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    const dots = new THREE.Points(dotGeo, new THREE.PointsMaterial({ color: ACCENT, size: 0.05, transparent: true, opacity: 0.55 }));
    scene.add(dots);

    const maxLines = count * 3;
    const lineGeo = new THREE.BufferGeometry();
    const linePositions = new Float32Array(maxLines * 2 * 3);
    lineGeo.setAttribute('position', new THREE.BufferAttribute(linePositions, 3));
    const lines = new THREE.LineSegments(lineGeo, new THREE.LineBasicMaterial({ color: ACCENT_LIGHT, transparent: true, opacity: 0.14 }));
    scene.add(lines);

    function updateLines() {
        const pos = dotGeo.attributes.position.array;
        let lineIdx = 0;
        const threshold = 3.2;
        for (let i = 0; i < count && lineIdx < maxLines; i++) {
            for (let j = i + 1; j < count && lineIdx < maxLines; j++) {
                const dx = pos[i*3] - pos[j*3], dy = pos[i*3+1] - pos[j*3+1], dz = pos[i*3+2] - pos[j*3+2];
                if (Math.sqrt(dx*dx + dy*dy + dz*dz) < threshold) {
                    linePositions[lineIdx*6] = pos[i*3]; linePositions[lineIdx*6+1] = pos[i*3+1]; linePositions[lineIdx*6+2] = pos[i*3+2];
                    linePositions[lineIdx*6+3] = pos[j*3]; linePositions[lineIdx*6+4] = pos[j*3+1]; linePositions[lineIdx*6+5] = pos[j*3+2];
                    lineIdx++;
                }
            }
        }
        for (let k = lineIdx; k < maxLines; k++) { for (let c = 0; c < 6; c++) linePositions[k*6+c] = 0; }
        lineGeo.attributes.position.needsUpdate = true;
    }

    function makeWingGeometry() {
        const shape = new THREE.Shape();
        shape.moveTo(0, 0);
        shape.bezierCurveTo(0.42, 0.32, 0.52, 0.62, 0.16, 0.78);
        shape.bezierCurveTo(-0.12, 0.6, -0.08, 0.28, 0, 0);
        return new THREE.ExtrudeGeometry(shape, { depth: 0.035, bevelEnabled: true, bevelThickness: 0.01, bevelSize: 0.01, bevelSegments: 1 });
    }
    const wingGeo = makeWingGeometry();
    const bodyGeo = new THREE.SphereGeometry(0.055, 10, 10);
    const antennaGeo = new THREE.CylinderGeometry(0.004, 0.004, 0.22, 6);
    const butterflyColors = [ACCENT, PRIMARY, ACCENT_LIGHT];

    function createButterfly(color) {
        const bfly = new THREE.Group();
        const wingMat = new THREE.MeshStandardMaterial({ color, side: THREE.DoubleSide, transparent: true, opacity: 0.88, roughness: 0.4, metalness: 0.15, emissive: color, emissiveIntensity: 0.12 });
        const wingL = new THREE.Mesh(wingGeo, wingMat); wingL.rotation.z = Math.PI / 2; wingL.position.x = -0.02;
        const wingR = new THREE.Mesh(wingGeo, wingMat.clone()); wingR.rotation.z = Math.PI / 2; wingR.scale.x = -1; wingR.position.x = 0.02;
        const wingLBack = new THREE.Mesh(wingGeo, wingMat.clone()); wingLBack.rotation.z = Math.PI / 2; wingLBack.position.set(-0.02, -0.15, -0.01); wingLBack.scale.setScalar(0.6);
        const wingRBack = new THREE.Mesh(wingGeo, wingMat.clone()); wingRBack.rotation.z = Math.PI / 2; wingRBack.scale.x = -0.6; wingRBack.scale.y = 0.6; wingRBack.position.set(0.02, -0.15, -0.01);
        const bodyMat = new THREE.MeshStandardMaterial({ color: INK, roughness: 0.5, metalness: 0.1 });
        const body = new THREE.Mesh(bodyGeo, bodyMat); body.scale.set(0.7, 1.6, 0.7);
        const antennaMat = new THREE.MeshStandardMaterial({ color: INK, roughness: 0.5 });
        const antL = new THREE.Mesh(antennaGeo, antennaMat); antL.position.set(-0.03, 0.16, 0); antL.rotation.z = 0.35;
        const antR = new THREE.Mesh(antennaGeo, antennaMat.clone()); antR.position.set(0.03, 0.16, 0); antR.rotation.z = -0.35;
        bfly.add(wingL, wingR, wingLBack, wingRBack, body, antL, antR);
        bfly.userData.wingL = wingL; bfly.userData.wingR = wingR; bfly.userData.wingLBack = wingLBack; bfly.userData.wingRBack = wingRBack;
        return bfly;
    }
    const butterflies = [];
    const butterflyGroup = new THREE.Group();
    for (let i = 0; i < 12; i++) {
        const bfly = createButterfly(butterflyColors[i % butterflyColors.length]);
        bfly.position.set((Math.random() - 0.5) * 14, Math.random() * 12 - 8, (Math.random() - 0.5) * 6 - 2);
        bfly.rotation.y = Math.random() * Math.PI * 2;
        bfly.scale.setScalar(0.7 + Math.random() * 0.6);
        bfly.userData.riseSpeed = 0.25 + Math.random() * 0.35;
        bfly.userData.swaySpeed = 0.5 + Math.random() * 0.8;
        bfly.userData.swayAmp = 0.6 + Math.random() * 0.8;
        bfly.userData.flapSpeed = 6 + Math.random() * 4;
        bfly.userData.baseX = bfly.position.x;
        butterflies.push(bfly);
        butterflyGroup.add(bfly);
    }
    scene.add(butterflyGroup);

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        const pos = dotGeo.attributes.position.array;
        for (let i = 0; i < count; i++) {
            pos[i*3] += velocities[i].x; pos[i*3+1] += velocities[i].y;
            if (pos[i*3] > 10) pos[i*3] = -10; if (pos[i*3] < -10) pos[i*3] = 10;
            if (pos[i*3+1] > 10) pos[i*3+1] = -10; if (pos[i*3+1] < -10) pos[i*3+1] = 10;
        }
        dotGeo.attributes.position.needsUpdate = true;
        updateLines();
        butterflies.forEach(b => {
            b.position.y += b.userData.riseSpeed * 0.016;
            b.position.x = b.userData.baseX + Math.sin(t * b.userData.swaySpeed) * b.userData.swayAmp;
            const flap = Math.sin(t * b.userData.flapSpeed) * 0.9;
            b.userData.wingL.rotation.y = flap; b.userData.wingR.rotation.y = -flap;
            b.userData.wingLBack.rotation.y = flap * 0.8; b.userData.wingRBack.rotation.y = -flap * 0.8;
            if (b.position.y > 10) b.position.y = -10;
        });
        camera.position.x += (mouseX * 0.6 - camera.position.x) * 0.02;
        camera.position.y += (-mouseY * 0.6 - camera.position.y) * 0.02;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();

    @elseif ($wedding->template_name === 'plum_parchment' || $wedding->template_name === 'royal_classic')
    scene.add(new THREE.AmbientLight(0xfff2d0, 0.75));
    const key = new THREE.PointLight(ACCENT_LIGHT, 1.5, 40); key.position.set(4, 5, 8); scene.add(key);
    const rim = new THREE.PointLight(PRIMARY, 0.7, 40); rim.position.set(-5, -3, 4); scene.add(rim);

    const ringMat = new THREE.MeshStandardMaterial({ color: ACCENT, metalness: 0.95, roughness: 0.22, emissive: PRIMARY, emissiveIntensity: 0.15 });
    const ringGroup = new THREE.Group();
    const ringA = new THREE.Mesh(new THREE.TorusGeometry(1.1, 0.14, 24, 90), ringMat);
    const ringB = new THREE.Mesh(new THREE.TorusGeometry(1.1, 0.14, 24, 90), ringMat);
    ringA.rotation.x = Math.PI / 2.4; ringB.rotation.y = Math.PI / 2.4;
    ringB.position.set(0.7, -0.2, 0);
    ringGroup.add(ringA, ringB);
    scene.add(ringGroup);

    function makeSparkleTexture() {
        const c = document.createElement('canvas'); c.width = c.height = 64;
        const ctx = c.getContext('2d');
        const g = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        g.addColorStop(0, 'rgba(255,244,214,1)'); g.addColorStop(0.4, 'rgba({{ $themeColors["c_accent_rgb"] }},0.8)'); g.addColorStop(1, 'rgba({{ $themeColors["c_accent_rgb"] }},0)');
        ctx.fillStyle = g; ctx.fillRect(0, 0, 64, 64);
        return new THREE.CanvasTexture(c);
    }
    const sparkleCount = 60;
    const sparklePos = new Float32Array(sparkleCount * 3);
    for (let i = 0; i < sparkleCount; i++) {
        sparklePos[i * 3] = (Math.random() - 0.5) * 16;
        sparklePos[i * 3 + 1] = (Math.random() - 0.5) * 10;
        sparklePos[i * 3 + 2] = (Math.random() - 0.5) * 5;
    }
    const sparkleGeo = new THREE.BufferGeometry();
    sparkleGeo.setAttribute('position', new THREE.BufferAttribute(sparklePos, 3));
    const sparkles = new THREE.Points(sparkleGeo, new THREE.PointsMaterial({ size: 0.2, map: makeSparkleTexture(), transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, opacity: 0.9 }));
    scene.add(sparkles);

    function makePetalTexture() {
        const c = document.createElement('canvas'); c.width = 64; c.height = 64;
        const ctx = c.getContext('2d'); ctx.translate(32, 32);
        const g = ctx.createLinearGradient(0, -28, 0, 28);
        g.addColorStop(0, '#{{ $themeColors["hex_accent_light"] }}'); g.addColorStop(1, '#{{ $themeColors["hex_primary"] }}');
        ctx.fillStyle = g;
        ctx.beginPath(); ctx.moveTo(0, -28); ctx.bezierCurveTo(22, -18, 22, 18, 0, 28); ctx.bezierCurveTo(-22, 18, -22, -18, 0, -28); ctx.fill();
        return new THREE.CanvasTexture(c);
    }
    const petalMat = new THREE.MeshBasicMaterial({ map: makePetalTexture(), transparent: true, side: THREE.DoubleSide, depthWrite: false });
    const petalGeo = new THREE.PlaneGeometry(0.45, 0.45);
    const petals = [];
    for (let i = 0; i < 20; i++) {
        const m = new THREE.Mesh(petalGeo, petalMat);
        m.position.set((Math.random() - 0.5) * 16, Math.random() * 12 - 6, (Math.random() - 0.5) * 5);
        m.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, Math.random() * Math.PI);
        m.userData = { speed: 0.25 + Math.random() * 0.35, drift: 0.3 + Math.random() * 0.5, spin: (Math.random() - 0.5) * 0.9, phase: Math.random() * Math.PI * 2 };
        scene.add(m); petals.push(m);
    }

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        ringGroup.rotation.y = t * 0.4 + mouseX * 0.4;
        ringGroup.rotation.x = Math.sin(t * 0.4) * 0.2 + mouseY * 0.15;
        const sp = sparkleGeo.attributes.position.array;
        for (let i = 0; i < sparkleCount; i++) { sp[i * 3 + 1] += 0.0025; if (sp[i * 3 + 1] > 6) sp[i * 3 + 1] = -6; }
        sparkleGeo.attributes.position.needsUpdate = true;
        sparkles.material.opacity = 0.55 + Math.sin(t * 2.2) * 0.35;
        petals.forEach(p => {
            const d = p.userData;
            p.position.y -= d.speed * 0.012;
            p.position.x += Math.sin(t * 0.6 + d.phase) * 0.004 * d.drift;
            p.rotation.z += d.spin * 0.01; p.rotation.x += 0.004;
            if (p.position.y < -6.5) { p.position.y = 6.5; p.position.x = (Math.random() - 0.5) * 16; }
        });
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();

    @elseif ($wedding->template_name === 'rustic_boho' || $wedding->template_name === 'terracotta_bloom')
    scene.add(new THREE.HemisphereLight(0xfff3e0, PRIMARY, 0.95));
    const key = new THREE.DirectionalLight(ACCENT_LIGHT, 1.1); key.position.set(4, 5, 6); scene.add(key);
    const rim = new THREE.DirectionalLight(ACCENT, 0.55); rim.position.set(-5, -2, -4); scene.add(rim);

    const archGroup = new THREE.Group();
    const archRadius = 3.5;
    const archTorus = new THREE.Mesh(new THREE.TorusGeometry(archRadius, 0.075, 10, 72, Math.PI), new THREE.MeshStandardMaterial({ color: PRIMARY, roughness: 0.8, metalness: 0.1 }));
    archGroup.add(archTorus);

    const flowerColors = [ACCENT, ACCENT_LIGHT, 0xffffff, PRIMARY];
    function makeFlower(color) {
        const g = new THREE.Group();
        const petalGeo = new THREE.SphereGeometry(0.14, 8, 8);
        const petalMat = new THREE.MeshStandardMaterial({ color, roughness: 0.65, metalness: 0.05 });
        for (let i = 0; i < 5; i++) {
            const p = new THREE.Mesh(petalGeo, petalMat);
            const ang = (i / 5) * Math.PI * 2;
            p.position.set(Math.cos(ang) * 0.16, Math.sin(ang) * 0.16, 0);
            p.scale.set(1, 0.55, 0.6);
            g.add(p);
        }
        g.add(new THREE.Mesh(new THREE.SphereGeometry(0.075, 8, 8), new THREE.MeshStandardMaterial({ color: ACCENT_LIGHT, roughness: 0.4 })));
        return g;
    }
    const leafMat = new THREE.MeshStandardMaterial({ color: 0x6f7d55, roughness: 0.7 });
    for (let i = 0; i <= 20; i++) {
        const t = i / 20;
        const ang = t * Math.PI;
        const fx = Math.cos(ang) * archRadius, fy = Math.sin(ang) * archRadius;
        const flower = makeFlower(flowerColors[i % flowerColors.length]);
        flower.position.set(fx, fy, (Math.random() - 0.5) * 0.3);
        flower.rotation.z = ang - Math.PI / 2;
        flower.scale.setScalar(0.6 + Math.random() * 0.5);
        archGroup.add(flower);
        if (i % 2 === 0) {
            const leaf = new THREE.Mesh(new THREE.ConeGeometry(0.06, 0.28, 5), leafMat);
            leaf.position.set(fx * 1.02, fy * 1.02, (Math.random() - 0.5) * 0.3);
            leaf.rotation.z = ang - Math.PI / 2 + (Math.random() - 0.5) * 0.6;
            leaf.rotation.x = Math.PI / 2;
            archGroup.add(leaf);
        }
    }
    archGroup.position.set(0, -1.9, -2.6);
    scene.add(archGroup);

    const ringGroup = new THREE.Group();
    const ringMatGold = new THREE.MeshStandardMaterial({ color: ACCENT, metalness: 0.9, roughness: 0.25 });
    const ringMatRose = new THREE.MeshStandardMaterial({ color: PRIMARY, metalness: 0.9, roughness: 0.25 });
    const ring1 = new THREE.Mesh(new THREE.TorusGeometry(0.6, 0.08, 24, 64), ringMatGold);
    const ring2 = new THREE.Mesh(new THREE.TorusGeometry(0.6, 0.08, 24, 64), ringMatRose);
    ring1.position.set(-0.35, 0.6, 0); ring2.position.set(0.35, 0.6, 0.18);
    ring1.rotation.x = Math.PI / 2.3; ring2.rotation.x = Math.PI / 2.3;
    ringGroup.add(ring1, ring2);
    const gem = new THREE.Mesh(new THREE.OctahedronGeometry(0.1, 0), new THREE.MeshStandardMaterial({ color: 0xffffff, metalness: 0.15, roughness: 0.05, emissive: ACCENT_LIGHT, emissiveIntensity: 0.2 }));
    gem.position.set(0.35, 1.16, 0.18);
    ringGroup.add(gem);
    scene.add(ringGroup);

    const sparkleCount = 50;
    const sparklePos = new Float32Array(sparkleCount * 3);
    for (let i = 0; i < sparkleCount; i++) {
        sparklePos[i * 3] = (Math.random() - 0.5) * 14;
        sparklePos[i * 3 + 1] = Math.random() * 10 - 5;
        sparklePos[i * 3 + 2] = (Math.random() - 0.5) * 6 - 1;
    }
    const sparkleGeo = new THREE.BufferGeometry();
    sparkleGeo.setAttribute('position', new THREE.BufferAttribute(sparklePos, 3));
    const sparkles = new THREE.Points(sparkleGeo, new THREE.PointsMaterial({ color: ACCENT_LIGHT, size: 0.05, transparent: true, opacity: 0.75 }));
    scene.add(sparkles);

    function animate() {
        requestAnimationFrame(animate);
        const t = clock.getElapsedTime();
        ringGroup.rotation.y = t * 0.55;
        ring1.rotation.z = Math.sin(t * 0.4) * 0.05; ring2.rotation.z = Math.cos(t * 0.4) * 0.05;
        gem.rotation.y = t * 2;
        archGroup.rotation.z = Math.sin(t * 0.15) * 0.012;
        const sp = sparkleGeo.attributes.position.array;
        for (let i = 0; i < sparkleCount; i++) { sp[i * 3 + 1] += 0.0025; if (sp[i * 3 + 1] > 5) sp[i * 3 + 1] = -5; }
        sparkleGeo.attributes.position.needsUpdate = true;
        camera.position.x += (mouseX * 0.8 - camera.position.x) * 0.03;
        camera.position.y += (0.25 - mouseY * 0.5 - camera.position.y) * 0.03;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();

    @else
    scene.add(new THREE.AmbientLight(0xfff1d6, 0.55));
    const key = new THREE.PointLight(ACCENT_LIGHT, 1.7, 30); key.position.set(-3, 2.5, 4); scene.add(key);
    const rim = new THREE.PointLight(ACCENT, 0.8, 30); rim.position.set(3, -1.5, 3); scene.add(rim);

    const starCount = 160;
    const starPos = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        starPos[i * 3] = (Math.random() - 0.5) * 16;
        starPos[i * 3 + 1] = (Math.random() - 0.5) * 10 + 1;
        starPos[i * 3 + 2] = -3 - Math.random() * 6;
    }
    const starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
    const stars = new THREE.Points(starGeo, new THREE.PointsMaterial({ color: ACCENT_LIGHT, size: 0.045, transparent: true, opacity: 0.8 }));
    scene.add(stars);

    const ringGroup = new THREE.Group();
    const ringMat = new THREE.MeshStandardMaterial({ color: ACCENT, metalness: 0.9, roughness: 0.22, emissive: PRIMARY, emissiveIntensity: 0.25 });
    const ringGeo = new THREE.TorusGeometry(0.72, 0.09, 28, 72);
    const ringA = new THREE.Mesh(ringGeo, ringMat);
    const ringB = new THREE.Mesh(ringGeo, ringMat);
    ringA.position.set(-0.4, 0, 0.15); ringB.position.set(0.4, 0, -0.15);
    ringA.rotation.y = 0.35; ringB.rotation.y = -0.35;
    ringGroup.add(ringA, ringB);
    scene.add(ringGroup);

    const accentGroup = new THREE.Group();
    const gemMat = new THREE.MeshStandardMaterial({ color: ACCENT_LIGHT, metalness: 0.3, roughness: 0.15, transparent: true, opacity: 0.75, emissive: ACCENT_LIGHT, emissiveIntensity: 0.15 });
    for (let i = 0; i < 6; i++) {
        const gem = new THREE.Mesh(new THREE.OctahedronGeometry(0.12 + Math.random() * 0.05, 0), gemMat);
        const ang = (i / 6) * Math.PI * 2;
        gem.position.set(Math.cos(ang) * 2.6, Math.sin(ang) * 1.4 - 0.4, -0.5 + Math.random() * 0.6);
        accentGroup.add(gem);
    }
    const roseColors = [PRIMARY, ACCENT, ACCENT_LIGHT];
    for (let i = 0; i < 7; i++) {
        const rose = new THREE.Mesh(new THREE.SphereGeometry(0.1 + Math.random() * 0.05, 8, 8), new THREE.MeshStandardMaterial({ color: roseColors[i % roseColors.length], roughness: 0.6 }));
        rose.position.set(-2.8 + Math.random() * 0.8, -1 + Math.random() * 0.9, -0.3 + Math.random() * 0.5);
        accentGroup.add(rose);
    }
    scene.add(accentGroup);

    let frame = 0;
    function animate() {
        frame += 1;
        requestAnimationFrame(animate);
        ringGroup.rotation.y += 0.008;
        ringGroup.rotation.x = mouseY * 0.4;
        ringGroup.position.y = Math.sin(frame * 0.02) * 0.06;
        accentGroup.rotation.y = frame * 0.0015;
        accentGroup.children.forEach((c, i) => { c.position.y += Math.sin(frame * 0.02 + i) * 0.0009; });
        stars.material.opacity = 0.6 + Math.sin(frame * 0.02) * 0.2;
        camera.position.x += (mouseX * 1.2 - camera.position.x) * 0.05;
        camera.position.y += (-mouseY * 0.8 - camera.position.y) * 0.05;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
        canvas.classList.add('ready');
    }
    animate();
    @endif
})();
</script>
</body>
</html>