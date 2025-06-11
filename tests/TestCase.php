<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurer l'application pour les tests
        $this->artisan('migrate:fresh');
    }

    /**
     * Créer un utilisateur de test
     */
    protected function createUser($attributes = [])
    {
        return \App\Models\User::factory()->create($attributes);
    }

    /**
     * Créer un utilisateur admin de test
     */
    protected function createAdmin($attributes = [])
    {
        return $this->createUser(array_merge([
            'role' => 'admin'
        ], $attributes));
    }

    /**
     * Authentifier un utilisateur pour les tests
     */
    protected function actingAsUser($user = null)
    {
        $user = $user ?: $this->createUser();
        return $this->actingAs($user, 'api');
    }

    /**
     * Authentifier un admin pour les tests
     */
    protected function actingAsAdmin($admin = null)
    {
        $admin = $admin ?: $this->createAdmin();
        return $this->actingAs($admin, 'api');
    }

    /**
     * Obtenir un token JWT pour un utilisateur
     */
    protected function getJWTToken($user = null)
    {
        $user = $user ?: $this->createUser();
        return \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
    }
}
