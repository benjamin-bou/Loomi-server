<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\BoxController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Test de l'API Boxes ===\n\n";

$controller = new BoxController();

// Test de l'endpoint index
echo "GET /api/boxes:\n";
$response = $controller->index();
$data = json_decode($response->getContent(), true);

echo "Nombre de boîtes retournées: " . count($data) . "\n\n";

// Afficher la première boîte avec ses images
if (!empty($data)) {
    $firstBox = $data[0];
    echo "Première boîte:\n";
    echo "  - Nom: {$firstBox['name']}\n";
    echo "  - Prix: {$firstBox['base_price']}€\n";
    echo "  - Catégorie: {$firstBox['category']['short_name']}\n";
    echo "  - Nombre d'images: " . count($firstBox['images']) . "\n";

    foreach ($firstBox['images'] as $image) {
        echo "    * {$image['link']} - {$image['alt']}\n";
    }
}

echo "\n=== Test de l'endpoint show ===\n";

// Test de l'endpoint show pour la première boîte
if (!empty($data)) {
    $boxId = $data[0]['id'];
    echo "GET /api/boxes/{$boxId}:\n";

    $showResponse = $controller->show($boxId);
    $showData = json_decode($showResponse->getContent(), true);

    echo "Boîte retournée: {$showData['name']}\n";
    echo "Images:\n";
    foreach ($showData['images'] as $image) {
        echo "  - {$image['link']}\n";
    }
}

echo "\n✅ Test terminé avec succès !\n";
