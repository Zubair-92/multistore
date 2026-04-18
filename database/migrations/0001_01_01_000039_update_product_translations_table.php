<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_translations', function (Blueprint $table) {
            
            // Add columns only if they do NOT already exist
            if (!Schema::hasColumn('product_translations', 'locale')) {
                $table->string('locale', 5)->after('product_id');
            }

            if (!Schema::hasColumn('product_translations', 'name')) {
                $table->string('name')->nullable()->after('locale');
            }

            if (!Schema::hasColumn('product_translations', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_translations', function (Blueprint $table) {
            if (Schema::hasColumn('product_translations', 'locale')) {
                $table->dropColumn('locale');
            }

            if (Schema::hasColumn('product_translations', 'name')) {
                $table->dropColumn('name');
            }

            if (Schema::hasColumn('product_translations', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
