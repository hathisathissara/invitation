<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation Setup
        $request->validate([
            'bride_name' => ['required', 'string', 'max:100'],
            'groom_name' => ['required', 'string', 'max:100'],
            'wedding_date' => ['required', 'date'],
            'venue' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                // reCAPTCHA API call
                $response = Http::withoutVerifying()->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                    'remoteip' => request()->ip()
                ]);

                if (! $response->json('success')) {
                    $fail('Please complete the reCAPTCHA correctly.');
                }
            }],
        ], [
            'g-recaptcha-response.required' => 'Please confirm you are not a robot.'
        ]);

        // 2. Database Transaction for User & Wedding
        try {
            DB::beginTransaction();

            // Create User
            $user = User::create([
                'name' => trim($request->bride_name) . ' & ' . trim($request->groom_name),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'couple',
                'status' => 'pending',
            ]);

            // Generate Unique Slug
            $slugBase = Str::slug($request->bride_name . '-' . $request->groom_name);
            $slug = $slugBase;
            while (Wedding::where('slug', $slug)->exists()) {
                $slug = $slugBase . '-' . rand(100, 999);
            }

            // Create Wedding linked to User
            $user->wedding()->create([
                'bride_name' => trim($request->bride_name),
                'groom_name' => trim($request->groom_name),
                'wedding_date' => $request->wedding_date,
                'venue' => trim($request->venue),
                'slug' => $slug,
            ]);

            DB::commit();

            // 3. Redirect to login instead of auto-login
            return redirect()->route('login')->with('status', 'Registration successful! Please sign in to continue.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Registration failed. Please try again later.']);
        }
    }
}