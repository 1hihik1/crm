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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('part_id')->nullable()->constrained('parts');
            $table->foreignId('service_id')->nullable()->constrained();
            $table->string('type'); // 'part' или 'service'
            $table->foreignId('employee_id')->nullable()->constrained('users'); //исполнитель (механик)
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2); //цена на момент заказа
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
