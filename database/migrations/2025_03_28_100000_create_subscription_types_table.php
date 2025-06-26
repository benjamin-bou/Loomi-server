<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_types', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('recurrence')->default('monthly'); // ex: 'monthly', 'quarterly', etc.

            // Nouveaux champs pour les détails comme les boîtes
            $table->text('delivery')->nullable(); // Informations de livraison
            $table->text('return_policy')->nullable(); // Politique de retour/gestion

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_types');
    }
};
