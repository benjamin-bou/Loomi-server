<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'order_number' => $this->faker->unique()->numerify('ORD-#####'),
            'total_amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => 'pending', // Utiliser le défaut pour les tests
            'delivery_date' => $this->faker->optional()->dateTimeBetween('-1 month', '+1 month'),
            'active' => true,
        ];
    }

    /**
     * Générer une commande avec un statut spécifique
     */
    public function withStatus($status)
    {
        return $this->state(['status' => $status]);
    }

    /**
     * Générer une commande livrée
     */
    public function delivered()
    {
        return $this->state(['status' => 'delivered']);
    }

    /**
     * Générer une commande terminée
     */
    public function completed()
    {
        return $this->state(['status' => 'completed']);
    }
}
