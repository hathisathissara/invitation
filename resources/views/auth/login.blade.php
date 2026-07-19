<x-guest-layout>

    <!-- Title Slot -->
    <x-slot name="title">Sign In — Lumos Studio</x-slot>

    <!-- Styles Slot -->
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    </x-slot>

    <div class="login-wrapper">
        <!-- Left Panel -->
        <div class="panel-left">
            <a href="{{ url('/') }}" class="panel-logo">Lumos Studio</a>
            <div>
                <p class="panel-quote">
                    "Where every love story gets a<br><em>beautiful beginning.</em>"
                </p>
            </div>
            <p class="panel-footer-text">© {{ date('Y') }} Lumos Studio · Digital Wedding Invitations</p>
        </div>

        <!-- Right Panel (Form) -->
        <div class="panel-right">
            <div class="form-box">
                <h1 class="form-box-title">Welcome back</h1>
                <p class="form-box-sub">
                    Don't have an account? <a href="{{ route('register') }}">Create one free →</a>
                </p>

                <!-- Success Messages (Registration / Password Reset) -->
                @if (session('status'))
                    <div class="success-box">
                        <i class="fas fa-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors (Incorrect Password / Too many attempts) -->
                @if ($errors->any())
                    <div class="error-box">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email" autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label style="margin-bottom: 0;">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" style="font-size: 0.75rem; color: #c9a96e; text-decoration: none;">Forgot password?</a>
                            @endif
                        </div>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password">
                        </div>
                    </div>

                    <!-- reCAPTCHA (only shows if .env has the keys) -->
                    @if (env('RECAPTCHA_SITE_KEY') && env('RECAPTCHA_SITE_KEY') !== 'YOUR_SITE_KEY_HERE')
                    <div class="form-group" style="margin-top: 20px; display: flex; justify-content: center;">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="dark"></div>
                    </div>
                    @endif

                    <button type="submit" class="btn-login">Sign In to Dashboard</button>
                </form>

                <div class="divider-or">or</div>
                <div style="text-align:center;">
                    <a href="{{ route('register') }}" style="font-size:0.88rem; color:#c9a96e; text-decoration:none; font-weight:500;">
                        <i class="fas fa-plus-circle" style="margin-right:6px;"></i> Create a New Invitation
                    </a>
                </div>

                <div class="back-home">
                    <a href="{{ url('/') }}"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Lumos Studio</a>
                </div>
            </div>
        </div>
    </div>

</x-guest-layout>