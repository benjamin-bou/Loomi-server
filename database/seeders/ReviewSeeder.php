<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Box;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer tous les avis existants pour éviter les doublons
        Review::query()->delete();
        
        $boxes = Box::all();
        $users = User::all();

        if ($boxes->isEmpty() || $users->isEmpty()) {
            $this->command->info('Aucune boîte ou utilisateur trouvé. Créez-en d\'abord.');
            return;
        }

        // Avis prédéfinis en français pour différents types de boîtes
        $reviewsData = [
            // Avis positifs (4-5 étoiles)
            [
                'rating' => 5,
                'comment' => 'Absolument parfait ! Cette box m\'a permis de découvrir une nouvelle passion. Les matériaux sont de très bonne qualité et les instructions sont claires.',
            ],
            [
                'rating' => 5,
                'comment' => 'Je recommande vivement ! Un moment de détente assuré, parfait pour déconnecter du quotidien. J\'ai adoré créer avec mes mains.',
            ],
            [
                'rating' => 4,
                'comment' => 'Très satisfaite de mon achat. L\'activité était relaxante et le résultat final est magnifique. Je vais certainement recommander.',
            ],
            [
                'rating' => 5,
                'comment' => 'Une box créative géniale ! Tout est fourni, les explications sont parfaites. C\'est devenu mon moment bien-être du week-end.',
            ],
            [
                'rating' => 4,
                'comment' => 'Super concept, j\'ai passé un excellent moment. La qualité des matériaux est au rendez-vous. Seul bémol : j\'aurais aimé plus de variété.',
            ],
            [
                'rating' => 5,
                'comment' => 'Parfait pour s\'initier ! Même en tant que débutante, j\'ai réussi à créer quelque chose de beau. Les tutos vidéo sont un plus.',
            ],

            // Avis moyens (3 étoiles)
            [
                'rating' => 3,
                'comment' => 'Correct dans l\'ensemble. L\'activité était sympa mais j\'attendais un peu plus de complexité. Rapport qualité-prix correct.',
            ],
            [
                'rating' => 3,
                'comment' => 'Bien sans plus. Les matériaux sont de bonne qualité mais l\'activité était plus simple que prévu. Ça reste un bon moment.',
            ],

            // Avis mitigés (2-3 étoiles)
            [
                'rating' => 2,
                'comment' => 'Déçue de cette box. Les instructions manquaient de clarté et le résultat final ne ressemble pas aux photos. Dommage.',
            ],
            [
                'rating' => 3,
                'comment' => 'Moyen. L\'idée est bonne mais l\'exécution pourrait être améliorée. Les matériaux sont corrects mais sans plus.',
            ],
        ];

        foreach ($boxes as $box) {
            // Chaque boîte a entre 0 et 3 avis
            $numberOfReviews = rand(0, 3);
            
            // Copie des utilisateurs pour cette boîte (pour éviter les doublons)
            $availableUsers = $users->pluck('id')->toArray();
            
            // Mélanger les utilisateurs pour une sélection aléatoire
            shuffle($availableUsers);

            for ($i = 0; $i < $numberOfReviews && $i < count($availableUsers); $i++) {
                $userId = $availableUsers[$i]; // Prendre le premier utilisateur disponible
                $randomReview = $reviewsData[array_rand($reviewsData)];

                Review::create([
                    'user_id' => $userId,
                    'box_id' => $box->id,
                    'rating' => $randomReview['rating'],
                    'comment' => $randomReview['comment'],
                    'created_at' => now()->subDays(rand(1, 30)), // Avis créés dans les 30 derniers jours
                    'updated_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $totalReviews = Review::count();
        $this->command->info("$totalReviews avis créés avec succès pour les boîtes !");
    }
}
