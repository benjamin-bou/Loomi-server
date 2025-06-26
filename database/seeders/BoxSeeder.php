<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Box;
use App\Models\BoxCategory;

class BoxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les catégories pour les associer aux boîtes
        $diyCategory = BoxCategory::where('short_name', 'DIY')->first();
        $activiteCategory = BoxCategory::where('short_name', 'Activité manuelle')->first();

        $controlledBoxes = [
            [
                'name' => 'Box couture',
                'description' => 'Découvrez l\'art de la couture avec nos projets créatifs et nos outils de qualité professionnelle.',
                'base_price' => 36.90,
                'active' => true,
                'quantity' => 45,
                'available_from' => now(),
                'box_category_id' => $activiteCategory?->id,
                'delivery' => 'Livraison gratuite en France métropolitaine sous 3-5 jours ouvrés. Expédition par Colissimo ou en point relais selon votre choix.',
                'return_policy' => 'Retours acceptés sous 14 jours. Produit non ouvert et en parfait état. Frais de retour à votre charge.',
            ],
            [
                'name' => 'Box création savon',
                'description' => 'Créez vos propres savons naturels avec nos ingrédients bio et nos moules originaux.',
                'base_price' => 36.90,
                'active' => true,
                'quantity' => 60,
                'available_from' => now(),
                'box_category_id' => $diyCategory?->id,
                'delivery' => 'Livraison gratuite en France métropolitaine sous 3-5 jours ouvrés. Expédition par Colissimo ou en point relais selon votre choix.',
                'return_policy' => 'Retours acceptés sous 14 jours. Produit non ouvert et en parfait état. Frais de retour à votre charge.',
            ],
            [
                'name' => 'Box mystère',
                'description' => 'Une sélection surprise d\'activités manuelles pour stimuler votre créativité et vous faire découvrir de nouveaux hobbies.',
                'base_price' => 36.90,
                'active' => true,
                'quantity' => 30,
                'available_from' => now(),
                'box_category_id' => $activiteCategory?->id,
                'delivery' => 'Livraison gratuite en France métropolitaine sous 3-5 jours ouvrés. Expédition par Colissimo ou en point relais selon votre choix.',
                'return_policy' => 'Retours acceptés sous 14 jours. Produit non ouvert et en parfait état. Frais de retour à votre charge.',
            ],
            [
                'name' => 'Box peinture',
                'description' => 'Explorez votre talent artistique avec nos pinceaux, peintures acryliques et toiles de qualité.',
                'base_price' => 36.90,
                'active' => true,
                'quantity' => 50,
                'available_from' => now(),
                'box_category_id' => $activiteCategory?->id,
                'delivery' => 'Livraison gratuite en France métropolitaine sous 3-5 jours ouvrés. Expédition par Colissimo ou en point relais selon votre choix.',
                'return_policy' => 'Retours acceptés sous 14 jours. Produit non ouvert et en parfait état. Frais de retour à votre charge.',
            ],
            [
                'name' => 'Box création bougie',
                'description' => 'Illuminez votre intérieur avec vos propres bougies parfumées faites maison.',
                'base_price' => 36.90,
                'active' => true,
                'quantity' => 70,
                'available_from' => now(),
                'box_category_id' => $diyCategory?->id,
                'delivery' => 'Livraison gratuite en France métropolitaine sous 3-5 jours ouvrés. Expédition par Colissimo ou en point relais selon votre choix.',
                'return_policy' => 'Retours acceptés sous 14 jours. Produit non ouvert et en parfait état. Frais de retour à votre charge.',
            ],
            [
                'name' => 'Box tricot',
                'description' => 'Apprenez ou perfectionnez l\'art du tricot avec nos laines douces et nos aiguilles de qualité.',
                'base_price' => 36.90,
                'active' => true,
                'quantity' => 40,
                'available_from' => now(),
                'box_category_id' => $activiteCategory?->id,
                'delivery' => 'Livraison gratuite en France métropolitaine sous 3-5 jours ouvrés. Expédition par Colissimo ou en point relais selon votre choix.',
                'return_policy' => 'Retours acceptés sous 14 jours. Produit non ouvert et en parfait état. Frais de retour à votre charge.',
            ],
        ];

        // Supprimer toutes les boîtes existantes pour éviter les doublons
        Box::query()->delete();

        // Créer les boîtes avec les données contrôlées
        foreach ($controlledBoxes as $boxData) {
            Box::create($boxData);
        }

        $this->command->info('6 boîtes spécifiques créées avec succès.');
    }
}
