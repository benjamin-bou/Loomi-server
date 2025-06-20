<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Box;
use App\Models\BoxImage;

class BoxImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer toutes les boîtes existantes
        $boxes = Box::all();

        // Images prédéfinies pour les 6 boîtes spécifiques (utilisant les vraies images)
        $predefinedImages = [
            [
                'boxes_images_id' => 'box_couture_001',
                'link' => '/images/boxes/box_couture_001.jpg',
                'alt' => 'Box couture avec matériel de couture professionnel',
            ],
            [
                'boxes_images_id' => 'box_savon_001',
                'link' => '/images/boxes/box_savon_001.png',
                'alt' => 'Box création savon avec ingrédients naturels',
            ],
            [
                'boxes_images_id' => 'box_mystere_001',
                'link' => '/images/boxes/box_mystere_001.png',
                'alt' => 'Box mystère avec activités manuelles surprises',
            ],
            [
                'boxes_images_id' => 'box_peinture_001',
                'link' => '/images/boxes/box_peinture_001.png',
                'alt' => 'Box peinture avec pinceaux et toiles',
            ],
            [
                'boxes_images_id' => 'box_bougie_001',
                'link' => '/images/boxes/box_bougie_001.png',
                'alt' => 'Box création bougie avec cires parfumées',
            ],
            [
                'boxes_images_id' => 'box_tricot_001',
                'link' => '/images/boxes/box_tricot_001.png',
                'alt' => 'Box tricot avec laines et aiguilles de qualité',
            ],
        ];

        // Images secondaires optionnelles (utilisant d'autres images disponibles)
        $secondaryImages = [
            [
                'boxes_images_id' => 'box_couture_002',
                'link' => '/images/boxes/box_poterie_001.jpg',
                'alt' => 'Activité créative complémentaire',
            ],
            [
                'boxes_images_id' => 'box_savon_002',
                'link' => '/images/boxes/box_lessive_001.jpg',
                'alt' => 'Produits naturels fait maison',
            ],
            [
                'boxes_images_id' => 'box_mystere_002',
                'link' => '/images/boxes/box_mystere_orange.jpg',
                'alt' => 'Version alternative de la box mystère',
            ],
            [
                'boxes_images_id' => 'box_peinture_002',
                'link' => '/images/boxes/box_dessin_001.png',
                'alt' => 'Activités de dessin et arts plastiques',
            ],
            [
                'boxes_images_id' => 'box_bougie_002',
                'link' => '/images/boxes/box_parfum_001.png',
                'alt' => 'Créations parfumées et aromatiques',
            ],
            [
                'boxes_images_id' => 'box_tricot_002',
                'link' => '/images/boxes/box_plante_001.png',
                'alt' => 'Activités créatives et nature',
            ],
        ];

        // Supprimer toutes les images existantes
        BoxImage::query()->delete();

        // Assigner les images aux boîtes
        foreach ($boxes as $index => $box) {
            // Image principale
            if (isset($predefinedImages[$index])) {
                $imageData = $predefinedImages[$index];
                BoxImage::create([
                    'boxes_images_id' => $imageData['boxes_images_id'],
                    'publication_date' => now(),
                    'link' => $imageData['link'],
                    'alt' => $imageData['alt'],
                    'box_id' => $box->id,
                ]);
            }

            // Image secondaire
            if (isset($secondaryImages[$index])) {
                $imageData = $secondaryImages[$index];
                BoxImage::create([
                    'boxes_images_id' => $imageData['boxes_images_id'],
                    'publication_date' => now()->addDays(1),
                    'link' => $imageData['link'],
                    'alt' => $imageData['alt'],
                    'box_id' => $box->id,
                ]);
            }
        }

        $this->command->info('Images des 6 boîtes spécifiques créées avec succès.');
    }
}
