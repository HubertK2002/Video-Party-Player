<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Register extends Controller
{
   public function __invoke(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Konto czeka na potwierdzenie kodem wysłanym mailem
        $user->sendEmailVerificationCode();

        $request->session()->put(EmailVerificationController::SESSION_KEY, $user->id);

        return redirect()->route('verification.notice')
            ->with('success', 'Wysłaliśmy kod weryfikacyjny na '.$user->email.'.');
    }
}
