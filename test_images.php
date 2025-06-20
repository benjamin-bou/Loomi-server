<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Box;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test des images des boîtes ===\n\n";

// Récupérer toutes les boîtes avec leurs images
$boxes = Box::with(['category', 'images'])->get();

foreach ($boxes as $box) {
    echo "📦 Boîte: {$box->name}\n";
    echo "   Catégorie: {$box->category->short_name}\n";
    echo "   Prix: {$box->base_price}€\n";
    echo "   Images:\n";

    foreach ($box->images as $image) {
        $imagePath = public_path($image->link);
        $exists = file_exists($imagePath) ? "✅" : "❌";
        echo "     {$exists} {$image->link} - {$image->alt}\n";
    }
    echo "\n";
}

echo "=== Résumé ===\n";
echo "Nombre de boîtes: " . $boxes->count() . "\n";
echo "Nombre total d'images: " . $boxes->sum(function ($box) {
    return $box->images->count();
}) . "\n";

// Vérifier les fichiers d'images
$imageFiles = scandir(public_path('images/boxes'));
$imageFiles = array_filter($imageFiles, function ($file) {
    return !in_array($file, ['.', '..']);
});

echo "Fichiers d'images disponibles: " . count($imageFiles) . "\n";
foreach ($imageFiles as $file) {
    echo "  - {$file}\n";
}
