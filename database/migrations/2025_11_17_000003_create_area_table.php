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
        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');

            // Relasi ke sales (nullable)
            $table->unsignedBigInteger('id_sales')->nullable();

            $table->string('nama_area');

            $table->timestamps();

            // Foreign key (cara lama)
            $table->foreign('id_sales')
                  ->references('id_sales')->on('sales')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area');
    }
};