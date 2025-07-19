<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // If you have any seeders
    }

    #[Test]
    public function it_can_list_paginated_users()
    {
        User::factory()->count(15)->create();

        $admin = User::factory()->create(['is_staff' => true]);
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'email', 'username']
                ],
                'links'
            ])
            ->assertJsonCount(10, 'data');
    }

    #[Test]
    public function it_denies_access_to_unauthenticated_users_for_listing()
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_create_a_new_user()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'username' => 'johndoe',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!'
        ];

        $response = $this->postJson('/api/user', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => 'User created successfully. Please verify your email to activate your account.',
                'user' => [
                    'email' => 'john.doe@example.com',
                    'username' => 'johndoe'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'username' => 'johndoe',
            'is_active' => true,
            'is_staff' => false
        ]);
    }

    #[Test]
    public function it_validates_required_fields_for_creation()
    {
        $response = $this->postJson('/api/user', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'username', 'password']);
    }

    #[Test]
    public function it_validates_unique_email_and_username_for_creation()
    {
        $existingUser = User::factory()->create();

        $response = $this->postJson('/api/user', [
            'email' => $existingUser->email,
            'username' => $existingUser->username,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'username']);
    }

    #[Test]
    public function it_validates_password_strength_for_creation()
    {
        $response = $this->postJson('/api/user', [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'weak',
            'password_confirmation' => 'weak'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function it_can_show_a_user_by_id()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_staff' => true]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/user/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email
            ]);
    }

    #[Test]
    public function it_can_show_a_user_by_slug_if_slug_exists()
    {
        // Create a unique username for this test
        $uniqueUsername = 'testuser_' . uniqid();

        $user = User::factory()->create([
            'username' => $uniqueUsername,
            'email' => 'unique_' . uniqid() . '@example.com' // Also make email unique
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/user/{$uniqueUsername}");

        $response->assertStatus(200)
            ->assertJson([
                'username' => $uniqueUsername
            ]);
    }

    #[Test]
    public function it_returns_404_for_nonexistent_user()
    {
        $admin = User::factory()->create(['is_staff' => true]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/user/9999');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_update_user_details()
    {
        $user = User::factory()->create();
        $newData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/user/{$user->id}", $newData);

        $response->assertStatus(200)
            ->assertJson($newData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'email' => 'updated@example.com'
        ]);
    }

    #[Test]
    public function it_can_update_password()
    {
        $user = User::factory()->create();
        $newPassword = 'NewStrongPass123!';

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/user/{$user->id}", [
                'password' => $newPassword,
                'password_confirmation' => $newPassword
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    #[Test]
    public function it_denies_update_for_unauthorized_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1, 'sanctum')
            ->patchJson("/api/user/{$user2->id}", [
                'first_name' => 'Unauthorized'
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_update_any_user()
    {
        $admin = User::factory()->create(['is_staff' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/user/{$user->id}", [
                'first_name' => 'AdminUpdated'
            ]);

        $response->assertStatus(200)
            ->assertJson(['first_name' => 'AdminUpdated']);
    }

    #[Test]
    public function it_validates_unique_email_during_update()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1, 'sanctum')
            ->patchJson("/api/user/{$user1->id}", [
                'email' => $user2->email
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_delete_a_user()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_staff' => true]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/user/{$user->id}");

        $response->assertStatus(204);
    }

    #[Test]
    public function user_can_delete_their_own_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/user/{$user->id}");

        $response->assertStatus(204);
    }

    #[Test]
    public function it_denies_deletion_for_unauthorized_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1, 'sanctum')
            ->deleteJson("/api/user/{$user2->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }

    #[Test]
    public function it_validates_is_staff_can_only_be_changed_by_admin()
    {
        $admin = User::factory()->create(['is_staff' => true]);
        $user = User::factory()->create();

        // Regular user can't make themselves admin
        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/user/{$user->id}", [
                'is_staff' => true
            ]);

        $response->assertStatus(403);

        // Admin can change staff status
        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/user/{$user->id}", [
                'is_staff' => true
            ]);

        $response->assertStatus(200);
        $this->assertTrue($user->fresh()->is_staff);
    }
}