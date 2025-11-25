<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom tanggal_isolir & tanggal_berhenti ke tabel langganan.
     */
    public function up(): void
    {
        Schema::table('langganan', function (Blueprint $table) {
            // sesuaikan posisi "after()" dengan struktur tabel kamu
            $table->date('tanggal_isolir')->nullable()->after('tanggal_mulai');
            $table->date('tanggal_berhenti')->nullable()->after('tanggal_isolir');
        });
    }

    /**
     * Rollback: hapus kolom yang baru ditambahkan.
     */
    public function down(): void
    {
        Schema::table('langganan', function (Blueprint $table) {
            $table->dropColumn('tanggal_isolir');
            $table->dropColumn('tanggal_berhenti');
        });
    }
};
