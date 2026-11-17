<?php

namespace Tests\Feature\Auth;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAttemptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fifth_failed_attempt_starts_a_fifteen_minute_lockout(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.test',
            'password' => 'CorrectPassword123',
        ]);

        for ($attempt = 1; $attempt <= LoginAttempt::MAX_ATTEMPTS; $attempt++) {
            $response = $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'WrongPassword123',
            ]);

            $response->assertSessionHasErrors('email');
        }

        $this->assertStringContainsString('bloqueada', session('errors')->first('email'));
        $this->assertSame(
            LoginAttempt::MAX_ATTEMPTS,
            LoginAttempt::countRecentAttempts($user->email, '127.0.0.1'),
        );

        $this->travel(10)->minutes();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'CorrectPassword123',
        ])->assertSessionHasErrors('email');
    }

    public function test_user_can_login_after_lockout_expires_and_attempts_are_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.test',
            'password' => 'CorrectPassword123',
        ]);

        for ($attempt = 0; $attempt < LoginAttempt::MAX_ATTEMPTS; $attempt++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'WrongPassword123',
            ]);
        }

        $this->travel(16)->minutes();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'CorrectPassword123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('login_attempts', ['email' => $user->email]);
    }

    public function test_successful_login_before_the_limit_clears_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.test',
            'password' => 'CorrectPassword123',
        ]);

        for ($attempt = 0; $attempt < LoginAttempt::MAX_ATTEMPTS - 1; $attempt++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'WrongPassword123',
            ]);
        }

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'CorrectPassword123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('login_attempts', ['email' => $user->email]);
    }

    public function test_lockout_is_scoped_to_the_email_and_ip_combination(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.test',
            'password' => 'CorrectPassword123',
        ]);

        for ($attempt = 0; $attempt < LoginAttempt::MAX_ATTEMPTS; $attempt++) {
            LoginAttempt::record($user->email, '192.0.2.10');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'CorrectPassword123',
            ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(
            LoginAttempt::MAX_ATTEMPTS,
            LoginAttempt::countRecentAttempts($user->email, '192.0.2.10'),
        );
    }
}
