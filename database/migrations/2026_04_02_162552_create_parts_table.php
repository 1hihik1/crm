<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('type')->nullable(); //вид
            $table->json('characteristics')->nullable(); // Характеристики
            $table->string('condition')->nullable(); //состояние
            $table->decimal('retail_price', 10, 2)->default(0); //цена продажи
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
