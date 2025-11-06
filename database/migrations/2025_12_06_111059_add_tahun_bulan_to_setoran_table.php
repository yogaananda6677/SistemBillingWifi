<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('setoran', function (Blueprint $table) {
        $table->integer('tahun')->nullable()->after('id_area');
        $table->tinyInteger('bulan')->nullable()->after('tahun');
    });
}

public function down(): void
{
    Schema::table('setoran', function (Blueprint $table) {
        $table->dropColumn(['tahun', 'bulan']);
    });
}

};
