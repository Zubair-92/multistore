<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            // Move logo after category_id
            $table->string('logo')->nullable()->after('category_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sub_categories', function (Blueprint $table) {
            // Move it back after sub_category (or previous position)
            $table->string('logo')->nullable()->after('sub_category')->change();
        });
    }
};
