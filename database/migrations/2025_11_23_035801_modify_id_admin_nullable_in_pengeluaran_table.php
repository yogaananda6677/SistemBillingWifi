<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengeluaran', function (Blueprint $table) {

            // 1. Ubah jadi nullable
            $table->unsignedBigInteger('id_admin')->nullable()->change();

            // 2. Tambah foreign key (karena sebelumnya belum ada)
            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admins')
                  ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('pengeluaran', function (Blueprint $table) {

            // rollback FK jika ada
            $table->dropForeign(['id_admin']);

            $table->unsignedBigInteger('id_admin')->nullable(false)->change();

            $table->foreign('id_admin')
                  ->references('id_admin')
                  ->on('admins');
        });
    }
};
