<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_category_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('store_category_id')->after('id');
            $table->string('locale', 5)->after('store_category_id');
            $table->string('store_category')->after('locale');

            $table->foreign('store_category_id')
                ->references('id')
                ->on('store_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('store_category_translations', function (Blueprint $table) {
            $table->dropForeign(['store_category_id']);
            $table->dropColumn(['store_category_id', 'locale', 'store_category']);
        });
    }
};
