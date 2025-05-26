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
        Schema::create('gift_card_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 8, 2)->default(0.00);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('gift_cards', function (Blueprint $table) {
            $table->foreignId('gift_card_type_id')->nullable()->constrained('gift_card_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropForeign(['gift_card_type_id']);
            $table->dropColumn('gift_card_type_id');
        });
        Schema::dropIfExists('gift_card_types');
    }
};
