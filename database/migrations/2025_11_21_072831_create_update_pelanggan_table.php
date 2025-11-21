<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Hapus kolom enum lama
        Schema::table('pelanggan', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggan', 'status_pelanggan')) {
                $table->dropColumn('status_pelanggan');
            }
        });

        // 2. Tambahkan enum baru + id_area
        Schema::table('pelanggan', function (Blueprint $table) {
            // Enum baru
            $table->enum('status_pelanggan', ['aktif', 'berhenti'])
                  ->default('aktif')
                  ->after('ip_address');

            // Tambahkan id_area
            if (!Schema::hasColumn('pelanggan', 'id_area')) {
                $table->unsignedBigInteger('id_area')->nullable()->after('id_sales');
                
                // Foreign key ke area
                $table->foreign('id_area')
                    ->references('id_area')
                    ->on('area')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        // Rollback: hapus kolom baru
        Schema::table('pelanggan', function (Blueprint $table) {
            // Hapus enum baru
            if (Schema::hasColumn('pelanggan', 'status_pelanggan')) {
                $table->dropColumn('status_pelanggan');
            }

            // Hapus FK & kolom id_area
            if (Schema::hasColumn('pelanggan', 'id_area')) {
                $table->dropForeign(['id_area']);
                $table->dropColumn('id_area');
            }
        });

        // Rollback enum lama
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->enum('status_pelanggan', ['baru', 'aktif', 'berhenti'])
                  ->after('ip_address');
        });
    }
};
