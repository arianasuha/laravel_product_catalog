<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if a user can be created using the factory.
     */
    public function test_user_can_be_created_using_factory(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertDatabaseHas('users', ['email' => $user->email]);
        $this->assertTrue(Hash::check('password', $user->password)); // Default password from factory
    }

    /**
     * Test default values for is_active and is_staff.
     */
    public function test_user_default_values(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->is_active);
        $this->assertFalse($user->is_staff);
    }

    /**
     * Test if password is hashed when set.
     */
    public function test_password_is_hashed_on_set(): void
    {
        $password = 'Password123!';
        $user = User::factory()->create(['password' => $password]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    /**
     * Test setting a password with a valid strength.
     */
    public function test_password_can_be_set_with_valid_strength(): void
    {
        $user = User::factory()->make(); // Use make to avoid saving immediately
        $validPassword = 'StrongPassword123!';
        $user->password = $validPassword;
        $user->save();

        $this->assertTrue(Hash::check($validPassword, $user->password));
    }

    /**
     * Test password validation for minimum length.
     */
    public function test_password_validation_for_minimum_length(): void
    {
        $user = User::factory()->make();
        $invalidPassword = 'Short1!';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters.');

        try {
            $user->password = $invalidPassword;
            $user->save();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
            $this->assertContains('Password must be at least 8 characters.', $e->errors()['password']);
            throw $e;
        }
    }

    /**
     * Test password validation for lowercase character.
     */
    public function test_password_validation_for_lowercase_character(): void
    {
        $user = User::factory()->make();
        $invalidPassword = 'NOLOWERCASE123!';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter.');

        try {
            $user->password = $invalidPassword;
            $user->save();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
            $this->assertContains('Password must contain at least one lowercase letter.', $e->errors()['password']);
            throw $e;
        }
    }

    /**
     * Test password validation for uppercase character.
     */
    public function test_password_validation_for_uppercase_character(): void
    {
        $user = User::factory()->make();
        $invalidPassword = 'nouppercase123!';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter.');

        try {
            $user->password = $invalidPassword;
            $user->save();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
            $this->assertContains('Password must contain at least one uppercase letter.', $e->errors()['password']);
            throw $e;
        }
    }

    /**
     * Test password validation for numeric character.
     */
    public function test_password_validation_for_numeric_character(): void
    {
        $user = User::factory()->make();
        $invalidPassword = 'NoNumbers!';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Password must contain at least one number.');

        try {
            $user->password = $invalidPassword;
            $user->save();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
            $this->assertContains('Password must contain at least one number.', $e->errors()['password']);
            throw $e;
        }
    }

    /**
     * Test password validation for special character.
     */
    public function test_password_validation_for_special_character(): void
    {
        $user = User::factory()->make();
        $invalidPassword = 'NoSpecialChar123';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Password must contain at least one special character.');

        try {
            $user->password = $invalidPassword;
            $user->save();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
            $this->assertContains('Password must contain at least one special character.', $e->errors()['password']);
            throw $e;
        }
    }

    /**
     * Test that no validation occurs if the password is already hashed.
     */
    public function test_password_validation_bypassed_if_already_hashed(): void
    {
        // Create a user with a valid password initially, so it's hashed in the DB
        $originalPassword = 'OriginalStrongPassword123!';
        $user = User::factory()->withPassword($originalPassword)->create();
        $initialHashedPassword = $user->password; // Get the hashed password from the model

        // Now, retrieve the user again (simulating a fresh retrieval from DB)
        $retrievedUser = User::find($user->id);

        // Make an update to another attribute, but do NOT touch the password
        $retrievedUser->first_name = 'Updated First Name';
        $retrievedUser->save();

        // The password should remain the same (not re-hashed or re-validated)
        $this->assertEquals($initialHashedPassword, $retrievedUser->password);
        $this->assertTrue(Hash::check($originalPassword, $retrievedUser->password)); // Ensure original password still works

        // Also test attempting to set an already hashed password directly (e.g., from an external source)
        $user2 = User::factory()->make();
        $preHashedPassword = Hash::make('alreadyhashedpassword');
        $user2->password = $preHashedPassword; // Set an already hashed password
        $user2->save(); // No validation exception should be thrown for strength
        $this->assertEquals($preHashedPassword, $user2->password);
    }

    /**
     * Test slug generation from email.
     */
    public function test_slug_is_generated_from_email(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $this->assertEquals('testuser-at-examplecom', $user->slug);
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'slug' => 'testuser-at-examplecom',
        ]);
    }

    /**
     * Test slug uniqueness and prevention of overwrite.
     */
    public function test_slug_uniqueness_and_updates_on_email_change(): void
    {
        // 1. Test slug uniqueness from *different* emails that would naturally generate the same base slug
        $user1 = User::factory()->create([
            'email' => 'emailtest@example.com',
        ]);
        // The slug should be 'emailtest-at-examplecom'

        $user2 = User::factory()->create([
            'email' => 'email.test@example.com', // Different email, but might produce similar slug base
        ]);
        // The slug for user2 should be 'emailtest-at-examplecom-1' or similar, due to uniqueness being handled by Spatie Sluggable

        $this->assertEquals('emailtest-at-examplecom', $user1->slug);
        $this->assertStringStartsWith('emailtest-at-examplecom-', $user2->slug);
        $this->assertNotEquals($user1->slug, $user2->slug);


        // 2. Test that slug is regenerated when email changes (after removing preventOverwrite())
        $existingUser = User::factory()->create([
            'email' => 'original@example.com',
        ]);
        $initialSlug = $existingUser->slug; // This will be 'original-at-examplecom'

        // Update the email
        $existingUser->email = 'updated@example.com';
        $existingUser->save();

        $newSlug = $existingUser->slug; // The slug should now reflect the new email

        // Assert that the slug has changed
        $this->assertNotEquals($initialSlug, $newSlug);
        $this->assertEquals('updated-at-examplecom', $newSlug);

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'email' => 'updated@example.com',
            'slug' => 'updated-at-examplecom', // Ensure the slug in DB is the new one
        ]);
    }

    /**
     * Test email_verified_at casting to datetime.
     */
    public function test_email_verified_at_casting(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $user->email_verified_at->format('Y-m-d H:i:s'));
    }

    /**
     * Test is_active and is_staff casting to boolean.
     */
    public function test_boolean_casting_for_flags(): void
    {
        $userTrue = User::factory()->create([
            'is_active' => 1,
            'is_staff' => true,
        ]);
        $userFalse = User::factory()->create([
            'is_active' => 0,
            'is_staff' => false,
        ]);

        $this->assertTrue($userTrue->is_active);
        $this->assertTrue($userTrue->is_staff);
        $this->assertFalse($userFalse->is_active);
        $this->assertFalse($userFalse->is_staff);
    }

    /**
     * Test that password is not included in hidden attributes during serialization.
     */
    public function test_password_is_hidden_from_serialization(): void
    {
        $user = User::factory()->create();
        $toArray = $user->toArray();
        $toJson = $user->toJson();

        $this->assertArrayNotHasKey('password', $toArray);
        $this->assertStringNotContainsString('"password":', $toJson);
    }

    /**
     * Test for unique email constraint.
     */
    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'unique@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'unique@example.com']);
    }

    /**
     * Test for unique username constraint.
     */
    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'uniqueuser']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['username' => 'uniqueuser']);
    }
}