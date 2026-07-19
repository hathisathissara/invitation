<x-guest-layout>
    <x-slot name="title">Forgot Password — Lumos Studio</x-slot>

    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    </x-slot>

    <div class="login-wrapper">
        <div class="panel-left">
            <a href="{{ url('/') }}" class="panel-logo">Lumos Studio</a>
            <div>
                <p class="panel-quote">
                    "Recover your account securely with a<br><em>password reset link.</em>"
                </p>
            </div>
            <p class="panel-footer-text">© {{ date('Y') }} Lumos Studio · Digital Wedding Invitations</p>
        </div>

        <div class="panel-right">
            <div class="form-box">
                <h1 class="form-box-title">Reset Password</h1>
                <p class="form-box-sub">
                    Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
                </p>

                <!-- Session Status / Success Message -->
                @if (session('status'))
                    <div class="success-box">
                        <i class="fas fa-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if ($errors->any())
                    <div class="error-box">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="email">
                        </div>
                    </div>

                    <button type="submit" class="btn-login" style="margin-top: 15px;">Email Password Reset Link</button>
                </form>

                <div class="back-home">
                    <a href="{{ route('login') }}"><i class="fas fa-arrow-left" style="margin-right:4px;"></i> Back to Sign In</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>