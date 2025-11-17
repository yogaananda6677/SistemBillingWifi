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
        Schema::create('payment_item', function (Blueprint $table) {
            $table->id('id_payment_item');

            $table->unsignedBigInteger('id_pembayaran');
            $table->unsignedBigInteger('id_tagihan');

            // Nominal pembayaran per item (boleh decimal)
            $table->decimal('nominal_bayar', 15, 2);

            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pembayaran')
                  ->references('id_pembayaran')
                  ->on('pembayaran')
                  ->onDelete('cascade');          // kalau pembayaran dihapus → item ikut terhapus

            $table->foreign('id_tagihan')
                  ->references('id_tagihan')
                  ->on('tagihan')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_item');
    }
};
