<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Only move the column, do NOT drop/create
            $table->string('logo')->nullable()->after('id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Move it back after name (or wherever it was before)
            $table->string('logo')->nullable()->after('name')->change();
        });
    }
};
