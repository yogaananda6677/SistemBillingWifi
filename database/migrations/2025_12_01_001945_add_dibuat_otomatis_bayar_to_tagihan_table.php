<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            // sesuaikan posisi "after" dengan kolom yang ada di tabelmu
            $table->boolean('dibuat_otomatis_bayar')
                ->default(false)
                ->after('status_tagihan');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            $table->dropColumn('dibuat_otomatis_bayar');
        });
    }
};
