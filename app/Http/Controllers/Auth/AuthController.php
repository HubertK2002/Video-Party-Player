<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user — konto jest nieaktywne do czasu potwierdzenia kodem z maila
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $user->sendEmailVerificationCode();

        $request->session()->put(EmailVerificationController::SESSION_KEY, $user->id);

        return redirect()->route('verification.notice')
            ->with('success', 'Wysłaliśmy kod weryfikacyjny na ' . $user->email . '.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate the request data
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to log the user in
        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Konto bez potwierdzonego maila wraca na ekran z kodem
            if (! $user->hasVerifiedEmail()) {
                auth()->logout();
                $request->session()->regenerate();
                $request->session()->put(EmailVerificationController::SESSION_KEY, $user->id);
                $user->sendEmailVerificationCode();

                return redirect()->route('verification.notice')
                    ->with('info', 'Najpierw potwierdź swój email — wysłaliśmy nowy kod na ' . $user->email . '.');
            }

            // Authentication passed...
            $request->session()->regenerate();

            return redirect('/')->with('success', 'Zalogowano pomyślnie!');
        }

        // Authentication failed...
        return back()->withErrors([
            'email' => 'Wprowadzone dane logowania są nieprawidłowe.',
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/')->with('success', 'Wylogowano pomyślnie!');
    }
}
