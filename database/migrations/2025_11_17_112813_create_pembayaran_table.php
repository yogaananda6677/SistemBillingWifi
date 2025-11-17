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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');

            $table->unsignedBigInteger('id_pelanggan')->nullable();
            $table->unsignedBigInteger('id_sales')->nullable();

            $table->dateTime('tanggal_bayar');

            // nominal pembayaran → decimal (uang)
            $table->decimal('nominal', 15, 2);

            // nomor pembayaran → lebih baik snake_case & unique
            $table->string('no_pembayaran')->unique();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')->on('pelanggan')
                ->onDelete('set null');

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
        Schema::dropIfExists('pembayaran');
    }
};
