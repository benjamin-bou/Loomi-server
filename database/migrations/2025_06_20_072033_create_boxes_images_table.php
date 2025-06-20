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
        Schema::create('boxes_images', function (Blueprint $table) {
            $table->id();
            $table->string('boxes_images_id', 50)->unique(); // Identifiant unique pour l'image
            $table->dateTime('publication_date'); // Date de publication
            $table->string('link', 250); // Lien vers l'image
            $table->string('alt', 250)->nullable(); // Texte alternatif
            $table->foreignId('box_id')->constrained('boxes')->onDelete('cascade'); // Relation avec la table boxes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes_images');
    }
};
