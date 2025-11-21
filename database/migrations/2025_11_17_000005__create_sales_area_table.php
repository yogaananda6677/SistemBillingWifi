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
    Schema::create('sales_area', function (Blueprint $table) {
        $table->id('id_sales_area');
        $table->unsignedBigInteger('id_sales');
        $table->unsignedBigInteger('id_area');

        $table->unique(['id_sales', 'id_area']);

        $table->foreign('id_sales')->references('id_sales')->on('sales')->cascadeOnDelete();
        $table->foreign('id_area')->references('id_area')->on('area')->cascadeOnDelete();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_area');
    }
};
