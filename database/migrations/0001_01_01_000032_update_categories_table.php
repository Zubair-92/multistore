<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'name_en')) {
                $table->dropColumn('name_en');
            }
            if (Schema::hasColumn('categories', 'name_ar')) {
                $table->dropColumn('name_ar');
            }

            $table->string('logo')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->dropColumn('logo');
        });
    }
};
