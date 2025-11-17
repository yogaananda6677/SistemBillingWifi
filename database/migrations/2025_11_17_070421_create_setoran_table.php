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
        Schema::create('setoran', function (Blueprint $table) {
            $table->id('id_setoran');

            $table->unsignedBigInteger('id_sales');
            $table->unsignedBigInteger('id_admin');

            $table->dateTime('tanggal_setoran');
            $table->integer('nominal');

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->foreign('id_sales')->references('id_sales')->on('sales')->onDelete('cascade');
            $table->foreign('id_admin')->references('id_admin')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran');
    }
};
