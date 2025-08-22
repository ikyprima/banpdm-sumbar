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
        Schema::create('tb_penugasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('tb_asesor')->onDelete('cascade');
            $table->date('tanggal_penugasan');
            $table->date('tanggal_penugasan_selesai');
            $table->string('keterangan')->nullable();
            $table->char('status', 1)->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_penugasan');
    }
};
