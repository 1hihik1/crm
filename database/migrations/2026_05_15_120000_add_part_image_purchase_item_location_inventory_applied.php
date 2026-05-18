<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('retail_price');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('storage_location_id')->nullable()->after('part_id')->constrained()->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('inventory_applied_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('inventory_applied_at');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('storage_location_id');
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
