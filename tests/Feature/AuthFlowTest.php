<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_render(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->get('/login')->assertOk()->assertSee('Nie pamiętasz hasła?', false);
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
        $this->get('/reset-password/jakis-token')->assertOk();

        $this->withSession([EmailVerificationController::SESSION_KEY => $user->id])
            ->get('/verify-email')
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_verification_page_without_pending_account_redirects_to_login(): void
    {
        $this->get('/verify-email')->assertRedirect(route('login'));
    }

    public function test_registration_creates_unverified_user_and_sends_code(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'sekretne-haslo',
            'password_confirmation' => 'sekretne-haslo',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();

        $user = User::where('email', 'jan@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseCount('email_verification_codes', 1);

        Notification::assertSentTo($user, EmailVerificationCodeNotification::class);
    }

    public function test_correct_code_verifies_account_and_logs_user_in(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $code = EmailVerificationCode::issueFor($user);

        $response = $this->withSession([EmailVerificationController::SESSION_KEY => $user->id])
            ->post('/verify-email', ['code' => $code]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseCount('email_verification_codes', 0);
    }

    public function test_wrong_code_is_rejected_and_counted(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        EmailVerificationCode::issueFor($user);

        $response = $this->withSession([EmailVerificationController::SESSION_KEY => $user->id])
            ->post('/verify-email', ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame(1, $user->verificationCodes()->first()->attempts);
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $code = EmailVerificationCode::issueFor($user);
        $user->verificationCodes()->first()->update(['expires_at' => now()->subMinute()]);

        $response = $this->withSession([EmailVerificationController::SESSION_KEY => $user->id])
            ->post('/verify-email', ['code' => $code]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_login_with_unverified_email_redirects_to_verification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'password' => 'sekretne-haslo',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'sekretne-haslo',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        Notification::assertSentTo($user, EmailVerificationCodeNotification::class);
    }

    public function test_verified_user_can_log_in(): void
    {
        $user = User::factory()->create(['password' => 'sekretne-haslo']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'sekretne-haslo',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_link_is_sent(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_unknown_email_gets_the_same_response(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nikt@example.com'])
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create(['password' => 'stare-haslo']);
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nowe-haslo-123',
            'password_confirmation' => 'nowe-haslo-123',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('nowe-haslo-123', $user->fresh()->password));
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create(['password' => 'stare-haslo']);

        $response = $this->post('/reset-password', [
            'token' => 'nieprawidlowy-token',
            'email' => $user->email,
            'password' => 'nowe-haslo-123',
            'password_confirmation' => 'nowe-haslo-123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('stare-haslo', $user->fresh()->password));
    }
}
