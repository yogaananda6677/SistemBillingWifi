<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('area', function (Blueprint $table) {
            // Hapus foreign key jika masih ada
            if (Schema::hasColumn('area', 'id_sales')) {
                $table->dropForeign(['id_sales']);
                $table->dropColumn('id_sales');
            }
        });
    }

    public function down()
    {
        Schema::table('area', function (Blueprint $table) {
            // Mengembalikan kolom id_sales jika rollback
            $table->unsignedBigInteger('id_sales')->nullable();

            $table->foreign('id_sales')
                ->references('id_sales')->on('sales')
                ->onDelete('set null');
        });
    }

};
