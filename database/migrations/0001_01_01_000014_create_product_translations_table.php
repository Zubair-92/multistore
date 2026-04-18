<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();

            // Foreign Key to products table
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');

            // Translatable columns
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();

            $table->timestamps();

            // Optional: Prevent duplicate translations
            $table->unique(['product_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};
