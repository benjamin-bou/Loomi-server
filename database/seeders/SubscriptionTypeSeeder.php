<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionType;

class SubscriptionTypeSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionType::create([
            'label' => 'Abonnement mensuel',
            'description' => "Vous souhaitez profiter de Loomi chaque mois, en toute simplicité ?\n\nOptez pour notre abonnement mensuel.\n\nRecevez votre box directement chez vous, sans engagement, tous les mois, à la façon des chaque mois une nouvelle découverte.",
            'price' => 33.99,
            'recurrence' => 'monthly',
            'delivery' => 'Livraison mensuelle automatique • Expédition le 5 de chaque mois • Livraison gratuite en France métropolitaine • Suivi par email et SMS • Délai de livraison : 3-5 jours ouvrés',
            'return_policy' => 'Résiliation sans engagement • Modification ou pause possible à tout moment depuis votre espace client • Remboursement partiel si résiliation avant expédition • Service client disponible 7j/7',
        ]);

        SubscriptionType::create([
            'label' => 'Abonnement mystère',
            'description' => "Recevoir une boîte mystère tous les 3 mois (cette boîte ne figurera aux autres box découvertes ensuite.)\n\nAimez les surprises et l'inattendu ? Découvrez notre Box Mystère : une expérience unique à chaque envoi.\n\nRecevez une surprise supplémentaire chaque mois — laissez la curiosité faire le premier pas.",
            'price' => 29.99,
            'recurrence' => 'quarterly',
            'delivery' => 'Livraison trimestrielle surprise • Expédition aléatoire entre le 1er et 15 du mois • Contenu exclusif non disponible ailleurs • Livraison gratuite en France et Europe • Emballage spécial mystère',
            'return_policy' => 'Engagement minimum 3 mois • Résiliation possible après la période minimale • Pas de remboursement pour contenu mystère déjà expédié • Échange possible en cas de défaut uniquement • Support prioritaire',
        ]);
    }
}
