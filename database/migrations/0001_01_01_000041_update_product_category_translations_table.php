<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_category_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('product_category_id')->after('id');
            $table->string('locale', 5)->after('product_category_id');
            $table->string('product_category')->after('locale');

            $table->foreign('product_category_id')
                ->references('id')
                ->on('product_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_category_translations', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropColumn(['product_category_id', 'locale', 'product_category']);
        });
    }
};
