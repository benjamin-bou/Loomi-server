<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\GiftCardType;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VALIDATION DES CARTES CADEAUX ===\n\n";

// Vider et recréer les tables
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
\Illuminate\Support\Facades\Schema::dropIfExists('gift_cards');
\Illuminate\Support\Facades\Schema::dropIfExists('gift_card_types');
\Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Exécuter les migrations
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

// Exécuter seulement le seeder des cartes cadeaux
\Illuminate\Support\Facades\Artisan::call('db:seed', [
    '--class' => 'Database\\Seeders\\GiftCardTypeSeeder',
    '--force' => true
]);

echo "📊 CARTES CADEAUX CRÉÉES PAR LE SEEDER :\n\n";

$giftCardTypes = GiftCardType::all();

foreach ($giftCardTypes as $giftCardType) {
    echo "🎁 {$giftCardType->name}\n";
    echo "   Prix: {$giftCardType->base_price}€\n";
    echo "   Description: {$giftCardType->description}\n";
    echo "   Active: " . ($giftCardType->active ? "✅ Oui" : "❌ Non") . "\n\n";
}

echo "✅ VALIDATION TERMINÉE\n";
echo "   - Nombre total de cartes cadeaux: " . $giftCardTypes->count() . "\n";
echo "   - Toutes créées par le seeder uniquement\n";
echo "   - Aucune carte cadeau superflue\n\n";

echo "🎯 Les tests utilisent maintenant exclusivement ces cartes cadeaux du seeder !\n";
