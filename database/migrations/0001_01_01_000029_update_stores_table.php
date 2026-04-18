<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'name_en')) {
                $table->dropColumn('name_en');
            }
            if (Schema::hasColumn('stores', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
            if (Schema::hasColumn('stores', 'address')) {
                $table->dropColumn('address');
            }

            $table->string('logo')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('name_en')->nullable();
            $table->string('name_ar')->nullable();
            $table->text('address')->nullable();
            $table->dropColumn('logo');
        });
    }
};
