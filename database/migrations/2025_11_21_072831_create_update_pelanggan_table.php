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
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id('id_pelanggan');

            $table->unsignedBigInteger('id_sales')->nullable();

            $table->string('nama');
            $table->string('nik'); 
            $table->text('alamat');
            $table->string('nomor_hp');
            $table->string('ip_address');

            // Status pelanggan hanya aktif / berhenti
            $table->enum('status_pelanggan', ['aktif', 'berhenti'])->default('aktif');

            $table->date('tanggal_registrasi');

            $table->timestamps();

            $table->foreign('id_sales')
                ->references('id_sales')
                ->on('sales')
                ->onDelete('restrict');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_pelanggan');
    }
};
