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
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('base_price', 6, 2);
            $table->boolean('active')->default(true);
            $table->integer('quantity')->default(0);
            $table->date('available_from')->nullable();
            $table->text('details')->nullable();
            $table->text('delivery')->nullable();
            $table->foreignId('box_category_id')->nullable()->constrained('box_categories')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key constraint from box_orders table before dropping boxes table
        Schema::table('box_orders', function (Blueprint $table) {
            $table->dropForeign(['box_id']);
        });

        Schema::dropIfExists('boxes');
    }
};
