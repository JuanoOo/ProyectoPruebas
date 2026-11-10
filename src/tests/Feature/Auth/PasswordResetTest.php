<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_STATUS = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación en breve.';

    public function test_request_shows_same_confirmation_for_registered_and_unknown_emails(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@example.test']);

        $registeredResponse = $this->post(route('password.email'), ['email' => strtoupper($user->email)]);
        $registeredResponse->assertSessionHas('status', self::GENERIC_STATUS);
        Notification::assertSentTo($user, ResetPassword::class);

        $unknownResponse = $this->post(route('password.email'), ['email' => 'unknown@example.test']);
        $unknownResponse->assertSessionHas('status', self::GENERIC_STATUS);
        Notification::assertNotSentTo(new User(['email' => 'unknown@example.test']), ResetPassword::class);
    }

    public function test_valid_token_resets_password_and_cannot_be_used_again(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'ana@example.test',
            'password' => 'OldPassword123',
        ]);

        $this->post(route('password.email'), ['email' => $user->email]);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->assertIsString($token);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
        $storedToken = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->value('token');
        $this->assertNotSame($token, $storedToken);
        $this->assertTrue(Hash::check($token, $storedToken));

        $payload = [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ];

        $this->post(route('password.update'), $payload)
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post(route('password.update'), $payload)
            ->assertSessionHasErrors('email');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'OldPassword123',
        ])->assertSessionHasErrors('email');
    }

    public function test_expired_token_is_rejected(): void
    {
        $this->assertSame(30, config('auth.passwords.users.expire'));

        $user = User::factory()->create(['email' => 'ana@example.test']);
        $token = Password::broker()->createToken($user);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(31)]);

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_new_password_must_follow_registration_security_rules(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.update'), [
                'email' => 'ana@example.test',
                'token' => 'any-token',
                'password' => 'weakpass',
                'password_confirmation' => 'weakpass',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('password');
    }
}
