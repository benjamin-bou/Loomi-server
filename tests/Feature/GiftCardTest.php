<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\GiftCard;
use App\Models\GiftCardType;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class GiftCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->giftCardType = GiftCardType::factory()->create([
            'name' => 'Carte Cadeau 50€',
            'base_price' => 50.00,
            'active' => true
        ]);
    }

    #[Test]
    public function user_can_get_list_of_active_gift_card_types()
    {
        // Créer un type inactif
        $inactiveType = GiftCardType::factory()->create([
            'active' => false
        ]);

        $response = $this->getJson('/api/gift-cards');

        $response->assertStatus(200)
            ->assertJsonCount(1) // Seulement le type actif
            ->assertJsonFragment(['id' => $this->giftCardType->id])
            ->assertJsonMissing(['id' => $inactiveType->id]);
    }

    #[Test]
    public function authenticated_user_can_activate_gift_card()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une carte cadeau avec un code
        $giftCard = GiftCard::factory()->create([
            'code' => 'GIFT2024ABC',
            'gift_card_type_id' => $this->giftCardType->id,
            'used_at' => null,
            'activated_by' => null
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => 'GIFT2024ABC'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'gift_card' => [
                    'id',
                    'code',
                    'gift_card_type' => [
                        'name',
                        'base_price'
                    ]
                ]
            ]);

        // Vérifier que la carte a été activée
        $this->assertDatabaseHas('gift_cards', [
            'id' => $giftCard->id,
            'activated_by' => $user->id
        ]);

        $giftCard->refresh();
        $this->assertNotNull($giftCard->used_at);
    }

    #[Test]
    public function user_cannot_activate_invalid_gift_card_code()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => 'INVALIDCODE'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Code de carte cadeau invalide'
            ]);
    }

    #[Test]
    public function user_cannot_activate_already_used_gift_card()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une carte cadeau déjà utilisée
        $giftCard = GiftCard::factory()->create([
            'code' => 'USEDGIFT123',
            'gift_card_type_id' => $this->giftCardType->id,
            'used_at' => now(),
            'activated_by' => $otherUser->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => 'USEDGIFT123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cette carte cadeau a déjà été utilisée'
            ]);
    }

    #[Test]
    public function user_cannot_activate_expired_gift_card()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer une carte cadeau expirée
        $giftCard = GiftCard::factory()->create([
            'code' => 'EXPIREDGIFT',
            'gift_card_type_id' => $this->giftCardType->id,
            'expiration_date' => now()->subDays(1), // Expirée hier
            'used_at' => null,
            'activated_by' => null
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => 'EXPIREDGIFT'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cette carte cadeau a expiré'
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_activate_gift_card()
    {
        $giftCard = GiftCard::factory()->create([
            'code' => 'TESTGIFT123',
            'gift_card_type_id' => $this->giftCardType->id
        ]);

        $response = $this->postJson('/api/gift-cards/activate', [
            'code' => 'TESTGIFT123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Vous devez être connecté pour activer une carte cadeau'
            ]);
    }

    #[Test]
    public function user_can_get_their_gift_cards()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $token = $this->getJWTToken($user);

        // Créer des cartes cadeaux pour l'utilisateur
        $userGiftCards = GiftCard::factory()->count(2)->create([
            'gift_card_type_id' => $this->giftCardType->id,
            'activated_by' => $user->id,
            'used_at' => now()
        ]);

        // Créer une carte cadeau pour un autre utilisateur
        $otherGiftCard = GiftCard::factory()->create([
            'gift_card_type_id' => $this->giftCardType->id,
            'activated_by' => $otherUser->id,
            'used_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-gift-cards');

        $response->assertStatus(200)
            ->assertJsonCount(2) // Seulement les cartes de l'utilisateur
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'code',
                    'used_at',
                    'expiration_date',
                    'gift_card_type' => [
                        'name',
                        'base_price'
                    ]
                ]
            ]);

        // Vérifier que la carte de l'autre utilisateur n'est pas présente
        $response->assertJsonMissing(['id' => $otherGiftCard->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_get_gift_cards()
    {
        $response = $this->getJson('/api/my-gift-cards');

        $response->assertStatus(401);
    }

    #[Test]
    public function gift_card_code_validation_requires_valid_format()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        // Test avec un code vide
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // Test avec un code trop long
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => Str::random(25) // Plus de 20 caractères
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function gift_card_codes_are_case_insensitive()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $giftCard = GiftCard::factory()->create([
            'code' => 'TESTCODE123',
            'gift_card_type_id' => $this->giftCardType->id,
            'used_at' => null,
            'activated_by' => null
        ]);

        // Tester avec un code en minuscules
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/gift-cards/activate', [
            'code' => 'testcode123'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('gift_cards', [
            'id' => $giftCard->id,
            'activated_by' => $user->id
        ]);
    }

    #[Test]
    public function user_can_view_gift_card_with_expiration_date()
    {
        $user = $this->createUser();
        $token = $this->getJWTToken($user);

        $giftCard = GiftCard::factory()->create([
            'gift_card_type_id' => $this->giftCardType->id,
            'activated_by' => $user->id,
            'used_at' => now(),
            'expiration_date' => now()->addYear()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/my-gift-cards');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $giftCard->id,
                'expiration_date' => $giftCard->expiration_date->toISOString()
            ]);
    }
}
