<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('carts', function (Blueprint $table) {

            if (!Schema::hasColumn('carts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }

            if (Schema::hasColumn('carts', 'total')) {
                $table->dropColumn('total');
            }
        });
    }

    public function down()
    {
        Schema::table('carts', function (Blueprint $table) {
            //
        });
    }
};
