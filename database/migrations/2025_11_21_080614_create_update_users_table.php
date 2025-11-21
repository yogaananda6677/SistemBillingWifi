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
        Schema::table('sales', function (Blueprint $table) {
            // Tambah kolom id_area jika belum ada
            if (!Schema::hasColumn('sales', 'id_area')) {
                $table->unsignedBigInteger('id_area')->after('id_sales');

                // Foreign key ke tabel area
                $table->foreign('id_area')
                    ->references('id_area')
                    ->on('area')
                    ->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Hapus foreign key
            if (Schema::hasColumn('sales', 'id_area')) {
                $table->dropForeign(['id_area']);
                $table->dropColumn('id_area');
            }
        });
    }


};
