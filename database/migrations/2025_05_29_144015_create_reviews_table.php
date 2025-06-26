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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Relation polymorphique pour pouvoir lier des avis aux boîtes et aux abonnements
            $table->morphs('reviewable'); // Crée reviewable_id et reviewable_type

            $table->decimal('rating', 2, 1)->unsigned(); // 0.5 à 5.0
            $table->text('comment')->nullable();
            $table->timestamps();

            // Un utilisateur ne peut laisser qu'un seul avis par élément (boîte ou abonnement)
            $table->unique(['user_id', 'reviewable_id', 'reviewable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
