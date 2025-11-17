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
        Schema::create('transaksi_komisi', function (Blueprint $table) {
            $table->id('id_komisi');

            $table->unsignedBigInteger('id_pembayaran')->nullable();
            $table->unsignedBigInteger('id_sales')->nullable();

            // Perbaikan typo
            $table->integer('nominal_komisi');
            $table->integer('jumlah_komisi');

            $table->timestamps();

            // Foreign keys
            $table->foreign('id_sales')
                  ->references('id_sales')->on('sales')
                  ->onDelete('set null');

            $table->foreign('id_pembayaran')
                  ->references('id_pembayaran')->on('pembayaran')
                  ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_komisi');
    }
};
