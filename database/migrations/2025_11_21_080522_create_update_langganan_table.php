<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('langganan', function (Blueprint $table) {
            // Hapus kolom status lama
            if (Schema::hasColumn('langganan', 'status_aktif')) {
                $table->dropColumn('status_aktif');
            }
        });

        Schema::table('langganan', function (Blueprint $table) {
            // Tambah kolom enum baru
            $table->enum('status_langganan', ['aktif', 'isolir'])
                ->default('aktif')
                ->after('tanggal_mulai');
        });
    }

    public function down()
    {
        schema::table('langganan', function (Blueprint $table) {
            // Hapus enum baru
            if (Schema::hasColumn('langganan', 'status_langganan')) {
                $table->dropColumn('status_langganan');
            }
        });

        Schema::table('langganan', function (Blueprint $table) {
            // Kembalikan kolom lama
            $table->string('status_aktif')->after('tanggal_mulai');
        });
    }

};
