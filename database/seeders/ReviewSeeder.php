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
        // Supprimer tous les avis existants
        Review::query()->delete();

        // Récupérer toutes les boîtes et tous les utilisateurs
        $boxes = Box::all();
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur trouvé. Création d\'avis impossible.');
            return;
        }

        // Avis prédéfinis par type de boîte
        $reviewsData = [
            'Box couture' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Excellente box ! Tout le matériel nécessaire était inclus et la qualité est au rendez-vous. Les patrons sont clairs et parfaits pour débuter. Je recommande vivement !',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'Très contente de cette box couture. Les tissus sont jolis et de bonne qualité. Le guide est bien expliqué, même pour une débutante comme moi. Juste un petit bémol sur le choix des couleurs.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Bonne initiative, j\'ai appris beaucoup de choses. Le contenu est complet mais j\'aurais aimé avoir plus de variété dans les projets proposés.',
                ],
            ],
            'Box création savon' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Parfait pour débuter la savonnerie ! Les ingrédients sont de qualité bio et les instructions très claires. Mes premiers savons ont été un succès total.',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'Super box créative ! J\'ai adoré créer mes propres savons parfumés. Les moules sont pratiques et les huiles essentielles sentent divinement bon.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Très bien pour découvrir cette activité. Le résultat est satisfaisant même si j\'aurais aimé plus de choix dans les parfums.',
                ],
            ],
            'Box mystère' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Quelle belle surprise ! J\'ai découvert de nouvelles activités créatives que je n\'aurais jamais pensé à essayer. Parfait pour sortir de sa zone de confort.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Concept original et matériaux variés. C\'est amusant de ne pas savoir à l\'avance ce qu\'on va créer. Quelques activités m\'ont moins plu mais globalement satisfaisant.',
                ],
                [
                    'rating' => 3.5,
                    'comment' => 'L\'idée est sympa mais le contenu ne correspondait pas vraiment à mes goûts. Peut-être plus de chance la prochaine fois !',
                ],
            ],
            'Box peinture' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Matériel de qualité professionnelle ! Les pinceaux sont parfaits et les peintures ont de belles couleurs vives. Le livret technique est très instructif.',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'Excellente box pour s\'initier à la peinture acrylique. J\'ai réalisé mes premiers tableaux avec fierté grâce aux exercices progressifs proposés.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Bon contenu dans l\'ensemble. Les toiles sont de bonne taille et le matériel complet. J\'aurais juste aimé plus de couleurs dans la palette.',
                ],
            ],
            'Box création bougie' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Activité très relaxante ! Les bougies obtenues sont magnifiques et sentent délicieusement bon. La cire de soja est de très bonne qualité.',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'Super moment créatif ! J\'ai adoré personnaliser mes bougies avec différents parfums. Les contenants en verre sont élégants.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Bonne box créative, facile à réaliser. Le rendu final est satisfaisant même si le temps de séchage est un peu long.',
                ],
            ],
            'Box tricot' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Parfait pour débuter le tricot ! Les laines sont douces et les aiguilles de bonne qualité. Le manuel explique très bien les points de base.',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'Très bonne box tricot. J\'ai réussi à tricoter mon premier projet grâce aux explications détaillées. Les patrons sont adaptés aux débutants.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Contenu complet pour s\'initier. Les laines ont de jolies textures mais j\'aurais aimé plus de choix dans les modèles proposés.',
                ],
            ],
        ];

        foreach ($boxes as $box) {
            // Nombre aléatoire d'avis entre 0 et 3
            $numberOfReviews = rand(0, 3);

            if ($numberOfReviews > 0 && isset($reviewsData[$box->name])) {
                // Sélectionner des utilisateurs aléatoires
                $selectedUsers = $users->random(min($numberOfReviews, $users->count()));

                // Mélanger les avis disponibles pour cette boîte
                $availableReviews = collect($reviewsData[$box->name])->shuffle();

                foreach ($selectedUsers as $index => $user) {
                    if ($index < $availableReviews->count()) {
                        $reviewData = $availableReviews[$index];

                        Review::create([
                            'user_id' => $user->id,
                            'box_id' => $box->id,
                            'rating' => $reviewData['rating'],
                            'comment' => $reviewData['comment'],
                            'created_at' => now()->subDays(rand(1, 60)), // Avis créés dans les 60 derniers jours
                            'updated_at' => now()->subDays(rand(1, 60)),
                        ]);
                    }
                }
            }
        }

        $totalReviews = Review::count();
        $this->command->info("$totalReviews avis créés avec succès pour les boîtes.");
    }
}
