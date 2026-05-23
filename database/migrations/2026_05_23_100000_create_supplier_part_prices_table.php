<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_part_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->decimal('purchase_price', 10, 2);
            $table->timestamps();

            $table->unique(['supplier_id', 'part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_part_prices');
    }
};
