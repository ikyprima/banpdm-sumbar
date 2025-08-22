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
        Schema::create('tb_penugasan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penugasan_id')->constrained('tb_penugasan')->onDelete('cascade');
            $table->foreignId('sekolah_id')->constrained('tb_satuan_sekolah')->onDelete('cascade');
            $table->char('status', 1)->default('0');
            $table->string('keterangan')->nullable();
            $table->dateTime('tanggal_check_in')->nullable();
            $table->decimal('latitude_check_in', 10, 7)->nullable();
            $table->decimal('longitude_check_in', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_penugasan_detail');
    }
};
