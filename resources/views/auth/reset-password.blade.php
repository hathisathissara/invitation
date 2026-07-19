<x-guest-layout>
    <x-slot name="title">Reset Password — Lumos Studio</x-slot>

    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    </x-slot>

    <div class="login-wrapper">
        <div class="panel-left">
            <a href="{{ url('/') }}" class="panel-logo">Lumos Studio</a>
            <div>
                <p class="panel-quote">
                    "Set your new secure password and<br><em>get back to planning.</em>"
                </p>
            </div>
            <p class="panel-footer-text">© {{ date('Y') }} Lumos Studio · Digital Wedding Invitations</p>
        </div>

        <div class="panel-right">
            <div class="form-box">
                <h1 class="form-box-title">New Password</h1>
                <p class="form-box-sub">Please enter your new password below.</p>

                @if ($errors->any())
                    <div class="error-box">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="you@example.com" required autofocus autocomplete="username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Min. 6 chars" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn-login" style="margin-top: 15px;">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>