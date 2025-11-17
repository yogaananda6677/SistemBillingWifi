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
        Schema::create('langganan', function (Blueprint $table) {
            $table->id('id_langganan');
            $table->unsignedBigInteger('id_paket');
            $table->unsignedBigInteger('id_pelanggan');
            $table->date('tanggal_mulai');
            $table->string('status_aktif');
            $table->timestamps();

            $table->foreign('id_paket')->references('id_paket')->on('paket')->onDelete('restrict');
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('langganan');
    }
};
