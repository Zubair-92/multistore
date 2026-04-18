<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('payment_method');
            }

            if (! Schema::hasColumn('orders', 'delivery_name')) {
                $table->string('delivery_name')->nullable()->after('transaction_id');
            }

            if (! Schema::hasColumn('orders', 'delivery_email')) {
                $table->string('delivery_email')->nullable()->after('delivery_name');
            }

            if (! Schema::hasColumn('orders', 'delivery_phone')) {
                $table->string('delivery_phone')->nullable()->after('delivery_email');
            }

            if (! Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('delivery_phone');
            }

            if (! Schema::hasColumn('orders', 'customer_note')) {
                $table->text('customer_note')->nullable()->after('delivery_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'payment_status',
                'delivery_name',
                'delivery_email',
                'delivery_phone',
                'delivery_address',
                'customer_note',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
