<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionType;

class SubscriptionTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Textes communs pour tous les abonnements
        $commonDetails = "Votre abonnement inclut une sélection personnalisée d'activités créatives, tous les matériaux nécessaires, des guides détaillés et l'accès à notre communauté en ligne. Vous pouvez suspendre ou annuler votre abonnement à tout moment depuis votre espace client.";
        $commonDelivery = "Livraison gratuite en France métropolitaine. Expédition automatique selon la fréquence choisie. Emballage éco-responsable. Possibilité de modifier l'adresse de livraison et les dates d'expédition depuis votre compte. Service client dédié aux abonnés disponible 7j/7.";

        SubscriptionType::create([
            'label' => 'Abonnement mensuel',
            'description' => "Vous souhaitez profiter de Loomi chaque mois, en toute simplicité ?\n\nOptez pour notre abonnement mensuel.\n\nRecevez votre box directement chez vous, sans engagement, tous les mois, à la façon des chaque mois une nouvelle découverte.",
            'price' => 40.00,
            'recurrence' => 'monthly',
            'details' => $commonDetails,
            'delivery' => $commonDelivery,
        ]);
        SubscriptionType::create([
            'label' => 'Abonnement mystère',
            'description' => "Recevoir une boîte mystère tous les 3 mois (cette boîte ne figurera aux autres box découvertes ensuite.)\n\nAimez les surprises et l'inattendu ? Découvrez notre Box Mystère : une expérience unique à chaque envoi.\n\nRecevez une surprise supplémentaire chaque mois — laissez la curiosité faire le premier pas.",
            'price' => 33.21,
            'recurrence' => 'quarterly',
            'details' => $commonDetails,
            'delivery' => $commonDelivery,
        ]);
    }
}
