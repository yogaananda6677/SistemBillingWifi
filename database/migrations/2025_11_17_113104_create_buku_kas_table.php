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
        Schema::create('buku_kas', function (Blueprint $table) {
            $table->id('id_buku_kas');

            // Relasi admin dan sales biasanya wajib ada
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->unsignedBigInteger('id_sales')->nullable();

            // Tiga sumber berbeda → nullable semua
            $table->unsignedBigInteger('id_pembayaran')->nullable();
            $table->unsignedBigInteger('id_setoran')->nullable();
            $table->unsignedBigInteger('id_pengeluaran')->nullable();

            // Tanggal update → gunakan dateTime
            $table->timestamp('tanggal_update')
                ->useCurrent()
                ->useCurrentOnUpdate();

            // Tipe pemasukan/pengeluaran
            $table->enum('tipe', ['pemasukan', 'pengeluaran'])->default('pemasukan');

            $table->string('sumber')->nullable();

            // Nominal decimal → precision 15, scale 2 (umum untuk uang)
            $table->decimal('nominal', 15, 2);

            $table->timestamps();

            /**
             * Foreign Keys
             */
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('set null');
            $table->foreign('id_sales')->references('id_sales')->on('sales')->onDelete('set null');
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->onDelete('set null');
            $table->foreign('id_setoran')->references('id_setoran')->on('setoran')->onDelete('set null');
            $table->foreign('id_pengeluaran')->references('id_pengeluaran')->on('pengeluaran')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_kas');
    }
};
