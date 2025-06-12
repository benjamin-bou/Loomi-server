<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'role'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'first_name' => $userData['firstName'],
            'last_name' => $userData['lastName'],
        ]);
    }

    #[Test]
    public function user_cannot_register_with_invalid_email()
    {
        $userData = [
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_register_with_existing_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'jean.dupont@example.com'
        ]);

        $userData = [
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'jean.dupont@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'jean.dupont@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user'
            ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'jean.dupont@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'jean.dupont@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid credentials']);
    }

    #[Test]
    public function user_can_get_profile_when_authenticated()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ]);
    }

    #[Test]
    public function user_cannot_access_profile_without_authentication()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    #[Test]
    public function user_can_update_profile()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $updateData = [
            'address' => '123 Rue de la Paix',
            'city' => 'Paris',
            'zipcode' => '75001',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/profile', $updateData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Profile updated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'address' => $updateData['address'],
            'city' => $updateData['city'],
            'zipcode' => $updateData['zipcode'],
        ]);
    }

    #[Test]
    public function user_can_refresh_token()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type'
            ]);
    }
}
