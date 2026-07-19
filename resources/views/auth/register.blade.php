<x-guest-layout>
    
    <!-- Title Slot එක -->
    <x-slot name="title">
        Create Your Invitation — Lumos Studio
    </x-slot>

    <!-- Styles Slot එක -->
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    </x-slot>

    <!-- Form Container (මේක කෙලින්ම යන්නේ layout එකේ $slot කියන තැනටයි) -->
    <div class="register-container">
        <div class="reg-brand">
            <a href="{{ url('/') }}" class="reg-logo">Lumos Studio</a>
            <p class="reg-tagline">Create your beautiful wedding invitation</p>
        </div>

        <div class="steps-indicator" id="steps-indicator">
            <div class="step-dot"><div class="step-circle active" id="s1c">1</div><span class="step-label">Couple</span></div>
            <div class="step-line"></div>
            <div class="step-dot"><div class="step-circle" id="s2c">2</div><span class="step-label">Wedding</span></div>
            <div class="step-line"></div>
            <div class="step-dot"><div class="step-circle" id="s3c">3</div><span class="step-label">Account</span></div>
        </div>

        <div class="reg-card">
            @if ($errors->any())
            <div class="error-box">
                <ul style="list-style-type: none;">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle" style="margin-right:8px;"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="reg-form">
                @csrf
                
                <div class="step-panel active" id="step-1">
                    <h2 class="step-heading">Introduce the couple</h2>
                    <p class="step-sub">The names that will grace your invitation</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Bride's Name <span class="required">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-heart"></i>
                                <input type="text" name="bride_name" id="bride_name" placeholder="e.g. Amara" value="{{ old('bride_name') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Groom's Name <span class="required">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-heart"></i>
                                <input type="text" name="groom_name" id="groom_name" placeholder="e.g. Sithum" value="{{ old('groom_name') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="btn-row end">
                        <button type="button" class="btn-next" onclick="goStep(2)">
                            Wedding Details <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <div class="step-panel" id="step-2">
                    <h2 class="step-heading">When & Where?</h2>
                    <p class="step-sub">Your wedding date and venue</p>

                    <div class="form-group">
                        <label>Wedding Date <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="far fa-calendar"></i>
                            <input type="date" name="wedding_date" id="wedding_date" value="{{ old('wedding_date') }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Main Venue <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="venue" id="venue" placeholder="Hotel or location name" value="{{ old('venue') }}" required>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button type="button" class="btn-prev" onclick="goStep(1)"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="button" class="btn-next" onclick="goStep(3)">Your Account <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>

                <div class="step-panel" id="step-3">
                    <h2 class="step-heading">Your Account</h2>
                    <p class="step-sub">Sign in details to manage your invitation</p>

                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email" placeholder="you@example.com" value="{{ old('email') }}" required autocomplete="username">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Password <span class="required">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id="password" placeholder="Min. 6 chars" required minlength="6" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password <span class="required">*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px; display: flex; justify-content: center;">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                    </div>

                    <div class="btn-row">
                        <button type="button" class="btn-prev" onclick="goStep(2)"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="submit" class="btn-submit"><i class="fas fa-heart"></i> Create My Invitation</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="signin-link">
            Already have an account? <a href="{{ route('login') }}">Sign in →</a>
        </div>
    </div>

    <!-- Scripts Slot එක -->
    <x-slot name="scripts">
        <script src="{{ asset('js/register.js') }}"></script>
    </x-slot>

</x-guest-layout>