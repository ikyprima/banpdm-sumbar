<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('tb_penugasan', function (Blueprint $table) {
            $table->decimal('latitude_awal', 10, 7)->nullable();
            $table->decimal('longitude_akhir', 10, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('tb_penugasan', function (Blueprint $table) {
            $table->dropColumn('latitude_awal');
            $table->dropColumn('longitude_akhir');
        });
    }
};
