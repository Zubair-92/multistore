<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('store_translations', function (Blueprint $table) {
            $table->id();
            
            // FK to stores table
            $table->foreignId('store_id')
                ->constrained('stores')
                ->onDelete('cascade');

            // Translatable fields
            $table->string('locale', 5); // 'en', 'ar'
            $table->string('name');
            $table->text('description')->nullable();

            $table->unique(['store_id', 'locale']); // avoid duplicates
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_translations');
    }

};
