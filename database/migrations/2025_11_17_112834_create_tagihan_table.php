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
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id('id_tagihan');

            // FK ke tabel langganan
            $table->unsignedBigInteger('id_langganan');

            // Detail tagihan
            $table->integer('bulan');
            $table->integer('tahun');
            $table->integer('harga_dasar');
            $table->integer('ppn_nominal');
            $table->integer('total_tagihan');

            $table->enum('status_tagihan', ['lunas', 'belum lunas'])
                  ->default('belum lunas');

            $table->dateTime('jatuh_tempo');

            $table->timestamps();

            // Foreign Key
            $table->foreign('id_langganan')
                  ->references('id_langganan')->on('langganan')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
