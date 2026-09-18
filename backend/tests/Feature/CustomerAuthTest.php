<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receives_verification_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Arben',
            'last_name' => 'Testi',
            'email' => 'arben@example.com',
            'phone' => '+38345977007',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'arben@example.com')
            ->assertJsonPath('user.email_verified', false)
            ->assertJsonStructure(['token']);

        $user = User::query()->where('email', 'arben@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_customer_can_login_and_read_authenticated_profile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Elira',
            'last_name' => 'Klienti',
            'email' => 'elira@example.com',
            'phone' => '+38344111222',
            'password' => 'Password1',
            'is_admin' => false,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'elira@example.com',
            'password' => 'Password1',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.email', 'elira@example.com')
            ->assertJsonStructure(['token']);

        $this->withToken($login->json('token'))
            ->getJson('/api/auth/user')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email_verified', true);
    }

    public function test_admin_user_cannot_login_through_customer_api(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Password1',
            'is_admin' => true,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'Password1',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Ky endpoint është vetëm për klientë.');
    }

    public function test_customer_can_verify_email_from_signed_link(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify@example.com',
            'is_admin' => false,
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->id, 'hash' => sha1($user->email)],
        );

        $this->getJson($url)->assertOk()
            ->assertJsonPath('user.email_verified', true);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_customer_can_request_and_use_password_reset_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'Password1',
            'is_admin' => false,
        ]);

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'reset@example.com',
        ])->assertOk();

        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'Newpass1',
            'password_confirmation' => 'Newpass1',
        ])->assertOk()
            ->assertJsonPath('message', 'Fjalëkalimi u ndryshua me sukses.');

        $this->postJson('/api/auth/login', [
            'email' => 'reset@example.com',
            'password' => 'Newpass1',
        ])->assertOk();
    }
}
