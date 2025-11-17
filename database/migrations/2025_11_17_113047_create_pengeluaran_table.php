<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id('id_pengeluaran');

            // Foreign keys
            $table->unsignedBigInteger('id_admin');
            $table->unsignedBigInteger('id_sales');

            $table->string('nama_pengeluaran');
            $table->dateTime('tanggal_pengajuan');
            $table->integer('nominal'); // typo diperbaiki

            $table->text('catatan')->nullable();

            // file bukti → gunakan string (nama file), bukan file()
            $table->string('bukti_file')->nullable();

            // enum harus punya opsi / pilihan
            $table->enum('status_approve', ['pending', 'approved', 'rejected'])
                  ->default('pending');

            // tanggal approve → seharusnya nullable, karena awalnya belum approve
            $table->dateTime('tanggal_approve')->nullable();

            $table->timestamps();

            // Tambahkan foreign key relasi
            $table->foreign('id_admin')
                  ->references('id_admin')->on('admins')
                  ->onDelete('cascade');

            $table->foreign('id_sales')
                  ->references('id_sales')->on('sales')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
