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
       Schema::create('tb_asesor', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_induk')->unique();  // Nomor Induk Asesor
            $table->string('nama');                   // Nama lengkap asesor
            $table->string('email')->nullable();      // Opsional email
            $table->string('no_hp')->nullable();      // Nomor HP
            $table->text('alamat')->nullable();       // Alamat lengkap
            $table->decimal('latitude', 10, 7)->nullable();  // Latitude
            $table->decimal('longitude', 10, 7)->nullable(); // Longitude
            $table->string('instansi')->nullable();   // Asal instansi/organisasi
            $table->string('foto')->nullable();       // URL/path ke foto asesor
            $table->string('id_wilayah')->nullable();   // URL/path ke foto KTP
            $table->timestamps();                     // created_at & updated_at
            $table->softDeletes();                    // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tb_asesor');
    }
};
