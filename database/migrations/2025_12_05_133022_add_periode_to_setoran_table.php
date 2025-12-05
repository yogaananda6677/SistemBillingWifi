<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->integer('periode_tahun')->nullable()->after('tanggal_setoran');
            $table->tinyInteger('periode_bulan')->nullable()->after('periode_tahun');
        });
    }

    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn(['periode_tahun', 'periode_bulan']);
        });
    }
};
