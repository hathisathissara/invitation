<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'g-recaptcha-response' => [
                // If RECAPTCHA is set in .env, make it required
                env('RECAPTCHA_SITE_KEY') && env('RECAPTCHA_SITE_KEY') !== 'YOUR_SITE_KEY_HERE' ? 'required' : 'nullable',
                function ($attribute, $value, $fail) {
                    if (! (env('RECAPTCHA_SITE_KEY') && env('RECAPTCHA_SITE_KEY') !== 'YOUR_SITE_KEY_HERE')) {
                        return; // reCAPTCHA not configured — skip
                    }

                    if (empty($value)) {
                        $fail('Please complete the reCAPTCHA.');

                        return;
                    }

                    try {
                        $response = Http::timeout(5)
                            ->withoutVerifying()
                            ->asForm()
                            ->post('https://www.google.com/recaptcha/api/siteverify', [
                                'secret' => env('RECAPTCHA_SECRET_KEY'),
                                'response' => $value,
                                'remoteip' => request()->ip(),
                            ]);

                        $json = $response->json();

                        if (! ($json['success'] ?? false)) {
                            // Log error codes for debugging (visible in Vercel logs)
                            $errorCodes = implode(', ', $json['error-codes'] ?? ['unknown']);
                            Log::warning('reCAPTCHA failed', [
                                'error-codes' => $errorCodes,
                                'host' => $json['hostname'] ?? 'N/A',
                            ]);
                            $fail('Please complete the reCAPTCHA correctly. (Error: '.$errorCodes.')');
                        }
                    } catch (\Exception $e) {
                        // If Google is unreachable (network issue on serverless), let it pass
                        // to avoid locking users out due to infrastructure problems
                        Log::error('reCAPTCHA verification request failed: '.$e->getMessage());
                    }
                },
            ],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey(), 900);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
