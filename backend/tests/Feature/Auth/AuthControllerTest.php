<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true
        ]);
    }

    #[Test]
    public function it_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'token_type',
                'expires_at'
            ])
            ->assertJson([
                'token_type' => 'Bearer'
            ]);
    }

    #[Test]
    public function it_fails_to_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'Password123!'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'email' => ['Credentials are incorrect.']
                ]
            ]);
    }

    #[Test]
    public function it_fails_to_login_with_invalid_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword!'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => 'Credentials are incorrect.'
            ]);
    }

    #[Test]
    public function it_fails_to_login_with_inactive_account()
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => false
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'Password123!'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => 'Credentials are incorrect.'
            ]);
    }

    #[Test]
    public function it_validates_login_request_fields()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    #[Test]
    public function it_can_logout_authenticated_user()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => 'Successfully logged out.'
            ]);

        $this->assertEmpty($this->user->tokens);
    }

    #[Test]
    public function it_denies_logout_for_unauthenticated_users()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'errors' => 'You are not authenticated'
            ]);
    }

    // #[Test]
    // public function token_expiry_works_correctly()
    // {
    //     $response = $this->postJson('/api/login', [
    //         'email' => 'test@example.com',
    //         'password' => 'Password123!'
    //     ]);

    //     $response->assertStatus(200);

    //     $expiresAt = $response->json('expires_at');
    //     $this->assertNotNull($expiresAt);

    //     // Verify the token is created with 24-hour expiry
    //     $this->travelTo(now()->addHours(23));
    //     $token = $response->json('token');

    //     $validResponse = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token
    //     ])->getJson('/api/user');

    //     $validResponse->assertStatus(200);

    //     // Verify token expires after 24 hours
    //     $this->travelTo(now()->addHours(2)); // Total 25 hours
    //     $expiredResponse = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token
    //     ])->getJson('/api/user');

    //     $expiredResponse->assertStatus(401);
    // }
}