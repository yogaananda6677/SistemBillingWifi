<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah id_area ke setoran
        Schema::table('setoran', function (Blueprint $table) {
            if (!Schema::hasColumn('setoran', 'id_area')) {
                $table->unsignedBigInteger('id_area')->nullable()->after('id_sales');

                $table->foreign('id_area')
                    ->references('id_area')
                    ->on('area')
                    ->onDelete('set null');
            }
        });

        // OPTIONAL: kalau mau pengeluaran juga per-area
        if (Schema::hasTable('pengeluaran') && !Schema::hasColumn('pengeluaran', 'id_area')) {
            Schema::table('pengeluaran', function (Blueprint $table) {
                $table->unsignedBigInteger('id_area')->nullable()->after('id_sales');

                $table->foreign('id_area')
                    ->references('id_area')
                    ->on('area')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            if (Schema::hasColumn('setoran', 'id_area')) {
                $table->dropForeign(['id_area']);
                $table->dropColumn('id_area');
            }
        });

        if (Schema::hasTable('pengeluaran') && Schema::hasColumn('pengeluaran', 'id_area')) {
            Schema::table('pengeluaran', function (Blueprint $table) {
                $table->dropForeign(['id_area']);
                $table->dropColumn('id_area');
            });
        }
    }
};
