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
    Schema::create('facturas', function (Blueprint $table) {
        $table->id();
        $table->string('documento'); 
        $table->string('nombre_cliente');
        $table->decimal('valor', 10, 2);
        $table->boolean('pagada')->default(false);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
