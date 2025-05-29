<?php

namespace Database\Seeders;

use App\Models\SubscriptionDelivery;
use App\Models\Subscription;
use App\Models\Box;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubscriptionDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer des abonnements et des boîtes existants
        $subscriptions = Subscription::all();
        $boxes = Box::all();

        if ($subscriptions->isEmpty() || $boxes->isEmpty()) {
            $this->command->info('Aucun abonnement ou boîte trouvé. Créez-en d\'abord.');
            return;
        }

        // Créer des livraisons de test pour chaque abonnement
        foreach ($subscriptions as $subscription) {
            // Créer 2-3 livraisons passées pour chaque abonnement
            $numberOfDeliveries = rand(2, 3);
            
            for ($i = 0; $i < $numberOfDeliveries; $i++) {
                $deliveryDate = Carbon::now()->subMonths($numberOfDeliveries - $i);
                $randomBox = $boxes->random();

                SubscriptionDelivery::create([
                    'subscription_id' => $subscription->id,
                    'box_id' => $randomBox->id,
                    'delivered_at' => $deliveryDate,
                ]);
            }
        }

        $this->command->info('Livraisons d\'abonnement de test créées avec succès.');
    }
}
