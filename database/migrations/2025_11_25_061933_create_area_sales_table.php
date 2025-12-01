<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('area_sales', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_area');
            $table->unsignedBigInteger('id_sales');

            $table->timestamps();

            // Tabel area kamu bernama "area" (bukan "areas")
            $table->foreign('id_area')
                ->references('id_area')
                ->on('area')
                ->onDelete('cascade');

            $table->foreign('id_sales')
                ->references('id_sales')
                ->on('sales')
                ->onDelete('cascade');

            $table->unique(['id_area', 'id_sales']); // biar tidak dobel
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_sales');
    }
};
