<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Formularz "zapomniałem hasła".
     */
    public function request()
    {
        return view('auth.forgot-password');
    }

    /**
     * Wysyła link resetujący na podany adres.
     */
    public function sendLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
        ]);

        $status = Password::sendResetLink($validated);

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()->with('error', 'Link został już wysłany chwilę temu. Odczekaj minutę i spróbuj ponownie.');
        }

        // Ta sama odpowiedź niezależnie od tego, czy konto istnieje — żeby nie
        // dało się sprawdzać, kto ma konto w serwisie.
        return back()->with('success', 'Jeśli konto o tym adresie istnieje, wysłaliśmy na nie link do zmiany hasła.');
    }

    /**
     * Formularz nowego hasła (link z maila).
     */
    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Zapisuje nowe hasło.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($validated, function ($user, string $password) {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Ten link do zmiany hasła jest nieprawidłowy lub wygasł.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Hasło zostało zmienione. Możesz się zalogować.');
    }
}
