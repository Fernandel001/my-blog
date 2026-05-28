<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const ADMIN_EMAIL = 'admin@thehackerexperiment.com';

    // ──────────────────────────────────────────────
    // Affichage du formulaire de connexion
    // ──────────────────────────────────────────────

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    // ──────────────────────────────────────────────
    // Traitement de la soumission du formulaire
    // ──────────────────────────────────────────────

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = $request->input('email');

        // ── Chemin admin : connexion classique par mot de passe ──
        if ($email === self::ADMIN_EMAIL) {
            $request->validate([
                'password' => ['required'],
            ]);

            if (Auth::attempt(['email' => $email, 'password' => $request->input('password')], $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('home'));
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants incorrects.']);
        }

        // ── Chemin visiteur : OTP ──
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => strstr($email, '@', before_needle: true)]
        );

        $code = (string) rand(1000, 9999);

        $user->update([
            'otp_code'       => $code,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        $user->notify(new OtpCodeNotification($code));

        // Stocker l'email en session pour la page de vérification
        $request->session()->put('otp_email', $email);

        return redirect()->route('otp.show');
    }

    // ──────────────────────────────────────────────
    // Affichage du formulaire de saisie du code OTP
    // ──────────────────────────────────────────────

    public function showOtpForm(Request $request): RedirectResponse|View
    {
        if (! $request->session()->has('otp_email')) {
            return redirect()->route('login');
        }

        return view('auth.otp', [
            'email' => $request->session()->get('otp_email'),
        ]);
    }

    // ──────────────────────────────────────────────
    // Vérification du code OTP
    // ──────────────────────────────────────────────

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:4'],
        ]);

        $email = $request->session()->get('otp_email');

        if (! $email) {
            return redirect()->route('login');
        }

        $user = User::where('email', $email)->first();

        if (
            ! $user
            || $user->otp_code !== $request->input('code')
            || ! $user->otp_expires_at
            || now()->isAfter($user->otp_expires_at)
        ) {
            return back()->withErrors(['code' => 'Code invalide ou expiré.']);
        }

        // Code valide : on connecte et on nettoie
        $user->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        Auth::login($user, remember: true);
        $request->session()->forget('otp_email');
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    // ──────────────────────────────────────────────
    // Déconnexion
    // ──────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
