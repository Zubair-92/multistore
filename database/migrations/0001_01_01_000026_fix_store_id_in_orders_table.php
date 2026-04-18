<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {

            // If column does NOT exist → create it as nullable
            if (!Schema::hasColumn('orders', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('user_id');
            } else {
                // If exists but not nullable → make nullable
                DB::statement('ALTER TABLE orders MODIFY store_id BIGINT UNSIGNED NULL;');
            }
        });

        // Make all existing rows null (safe now)
        DB::table('orders')->update(['store_id' => null]);

        // Now add foreign key
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('store_id')
                ->references('id')->on('stores')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
