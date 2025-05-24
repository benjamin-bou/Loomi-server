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
            $table->unsignedBigInteger('box_category_id')->nullable();
            $table->foreign('box_category_id')->references('id')->on('box_categories')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
