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
        Schema::table('pelanggan', function (Blueprint $table) {
            // Hapus kolom enum lama
            $table->dropColumn('status_pelanggan');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            // Buat enum baru
            $table->enum('status_pelanggan', ['aktif', 'berhenti'])
                ->default('aktif')
                ->after('ip_address');
        });
    }

    public function down()
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Rollback: remove enum baru
            $table->dropColumn('status_pelanggan');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            // Rollback ke enum lama
            $table->enum('status_pelanggan', ['baru', 'aktif', 'berhenti'])
                ->after('ip_address');
        });
    }

};
