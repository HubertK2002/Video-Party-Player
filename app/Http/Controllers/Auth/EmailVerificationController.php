<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    /**
     * Klucz sesji, w którym trzymamy id konta czekającego na weryfikację.
     */
    public const SESSION_KEY = 'verification.user_id';

    /**
     * Ile sekund trzeba odczekać między wysyłkami kodu.
     */
    private const RESEND_COOLDOWN = 60;

    /**
     * Formularz z polem na kod z maila.
     */
    public function show(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Zaloguj się, aby dokończyć weryfikację konta.');
        }

        return view('auth.verify-code', compact('user'));
    }

    /**
     * Sprawdza kod i — jeśli się zgadza — aktywuje konto oraz loguje użytkownika.
     */
    public function verify(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Sesja weryfikacji wygasła. Zaloguj się ponownie.');
        }

        $validated = $request->validate([
            'code' => 'required|string|digits:6',
        ], [
            'code.digits' => 'Kod składa się z 6 cyfr.',
        ]);

        // Zapora przed zgadywaniem kodu na siłę.
        $throttleKey = 'verify-code:'.$user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            throw ValidationException::withMessages([
                'code' => 'Zbyt wiele prób. Spróbuj ponownie za '.RateLimiter::availableIn($throttleKey).' sekund.',
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        $verificationCode = $user->verificationCodes()->latest('id')->first();

        if (! $verificationCode || $verificationCode->isExpired()) {
            return back()->withErrors([
                'code' => 'Kod wygasł. Wyślij nowy kod i spróbuj jeszcze raz.',
            ]);
        }

        if (! $verificationCode->matches($validated['code'])) {
            $verificationCode->increment('attempts');

            if ($verificationCode->attempts >= EmailVerificationCode::MAX_ATTEMPTS) {
                $verificationCode->delete();

                return back()->withErrors([
                    'code' => 'Za dużo błędnych prób — ten kod został unieważniony. Wyślij nowy kod.',
                ]);
            }

            $left = EmailVerificationCode::MAX_ATTEMPTS - $verificationCode->attempts;

            return back()->withErrors([
                'code' => 'Nieprawidłowy kod. Pozostało prób: '.$left.'.',
            ]);
        }

        $verificationCode->delete();
        $user->markEmailAsVerified();

        RateLimiter::clear($throttleKey);

        $request->session()->forget(self::SESSION_KEY);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Konto zostało potwierdzone. Miłego seansu!');
    }

    /**
     * Wysyła nowy kod na adres e-mail konta.
     */
    public function resend(Request $request)
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Sesja weryfikacji wygasła. Zaloguj się ponownie.');
        }

        $throttleKey = 'resend-code:'.$user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            return back()->with('error', 'Nowy kod możesz wysłać za '.RateLimiter::availableIn($throttleKey).' sekund.');
        }

        RateLimiter::hit($throttleKey, self::RESEND_COOLDOWN);

        $user->sendEmailVerificationCode();

        return back()->with('success', 'Wysłaliśmy nowy kod na '.$user->email.'.');
    }

    /**
     * Konto, które przechodzi przez weryfikację w tej sesji.
     */
    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get(self::SESSION_KEY);

        if (! $userId) {
            return null;
        }

        $user = User::find($userId);

        if (! $user || $user->hasVerifiedEmail()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $user;
    }
}
