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
        ]);
        SubscriptionType::create([
            'label' => 'Abonnement mystère',
            'description' => "Recevoir une boîte mystère tous les 3 mois (cette boîte ne figurera aux autres box découvertes ensuite.)\n\nAimez les surprises et l'inattendu ? Découvrez notre Box Mystère : une expérience unique à chaque envoi.\n\nRecevez une surprise supplémentaire chaque mois — laissez la curiosité faire le premier pas.",
            'price' => 29.99,
            'recurrence' => 'quarterly',
        ]);
    }
}
