<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah semua yg 'baru' jadi 'aktif'
        DB::statement("
            UPDATE pelanggan
            SET status_pelanggan = 'aktif'
            WHERE status_pelanggan = 'baru'
        ");

        // Hapus 'baru' dari ENUM
        DB::statement("
            ALTER TABLE pelanggan
            MODIFY status_pelanggan ENUM('aktif','berhenti','isolir')
            NOT NULL
            DEFAULT 'aktif'
        ");
    }

    public function down(): void
    {
        // Balikin ENUM seperti semula (kalau di-rollback)
        DB::statement("
            ALTER TABLE pelanggan
            MODIFY status_pelanggan ENUM('baru','aktif','berhenti','isolir')
            NOT NULL
            DEFAULT 'baru'
        ");
    }
};
