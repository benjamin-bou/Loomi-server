<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Box;
use App\Models\SubscriptionType;
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

        // Récupérer toutes les boîtes, types d'abonnement et utilisateurs
        $boxes = Box::all();
        $subscriptionTypes = SubscriptionType::all();
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur trouvé. Création d\'avis impossible.');
            return;
        }

        // Avis prédéfinis par type de boîte
        $boxReviewsData = [
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

        // Avis prédéfinis pour les types d'abonnement
        $subscriptionReviewsData = [
            'Abonnement mensuel' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Parfait ! Je reçois ma box chaque mois à date fixe et la qualité est toujours au rendez-vous. Le service client est très réactif en cas de besoin.',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'Très satisfaite de cet abonnement. Les box arrivent toujours en parfait état et à temps. J\'adore découvrir de nouveaux projets créatifs chaque mois.',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Bon concept d\'abonnement, livraison fiable. Parfois j\'aimerais pouvoir choisir le thème du mois mais dans l\'ensemble c\'est une belle découverte.',
                ],
            ],
            'Abonnement mystère' => [
                [
                    'rating' => 5.0,
                    'comment' => 'Quelle surprise à chaque fois ! J\'adore ne pas savoir ce que je vais recevoir. Les box mystères contiennent toujours des projets originaux et uniques.',
                ],
                [
                    'rating' => 4.5,
                    'comment' => 'L\'effet surprise est génial et le contenu est toujours de qualité. Parfait pour sortir de sa zone de confort créative !',
                ],
                [
                    'rating' => 4.0,
                    'comment' => 'Concept original qui me fait découvrir de nouvelles techniques. Seul bémol : pas de choix possible si on n\'aime pas le thème.',
                ],
            ],
        ];

        // Créer des avis pour les boîtes
        foreach ($boxes as $box) {
            // Nombre aléatoire d'avis entre 0 et 3
            $numberOfReviews = rand(0, 3);

            if ($numberOfReviews > 0 && isset($boxReviewsData[$box->name])) {
                // Sélectionner des utilisateurs aléatoires
                $selectedUsers = $users->random(min($numberOfReviews, $users->count()));

                // Mélanger les avis disponibles pour cette boîte
                $availableReviews = collect($boxReviewsData[$box->name])->shuffle();

                foreach ($selectedUsers as $index => $user) {
                    if ($index < $availableReviews->count()) {
                        $reviewData = $availableReviews[$index];

                        Review::create([
                            'user_id' => $user->id,
                            'reviewable_id' => $box->id,
                            'reviewable_type' => Box::class,
                            'rating' => $reviewData['rating'],
                            'comment' => $reviewData['comment'],
                            'created_at' => now()->subDays(rand(1, 60)), // Avis créés dans les 60 derniers jours
                            'updated_at' => now()->subDays(rand(1, 60)),
                        ]);
                    }
                }
            }
        }

        // Créer des avis pour les types d'abonnement
        foreach ($subscriptionTypes as $subscriptionType) {
            // Nombre aléatoire d'avis entre 1 et 3 pour les abonnements
            $numberOfReviews = rand(1, 3);

            if (isset($subscriptionReviewsData[$subscriptionType->label])) {
                // Sélectionner des utilisateurs aléatoires (différents de ceux des boîtes)
                $availableUsers = $users->shuffle();
                $selectedUsers = $availableUsers->take(min($numberOfReviews, $users->count()));

                // Mélanger les avis disponibles pour ce type d'abonnement
                $availableReviews = collect($subscriptionReviewsData[$subscriptionType->label])->shuffle();

                foreach ($selectedUsers as $index => $user) {
                    if ($index < $availableReviews->count()) {
                        $reviewData = $availableReviews[$index];

                        Review::create([
                            'user_id' => $user->id,
                            'reviewable_id' => $subscriptionType->id,
                            'reviewable_type' => SubscriptionType::class,
                            'rating' => $reviewData['rating'],
                            'comment' => $reviewData['comment'],
                            'created_at' => now()->subDays(rand(1, 90)), // Avis créés dans les 90 derniers jours
                            'updated_at' => now()->subDays(rand(1, 90)),
                        ]);
                    }
                }
            }
        }

        $totalBoxReviews = Review::where('reviewable_type', Box::class)->count();
        $totalSubscriptionReviews = Review::where('reviewable_type', SubscriptionType::class)->count();
        $this->command->info("$totalBoxReviews avis créés pour les boîtes et $totalSubscriptionReviews avis créés pour les abonnements.");
    }
}
