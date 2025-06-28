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
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('description')->nullable();
            $table->string('name');
            $table->string('original_name');
            $table->string('filepath');
            $table->string('extension', 10);
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->uuid('user_id')->nullable();
            // Relasi kategori
            $table->foreignId('categories_id')->constrained()->onDelete('cascade');

            // Relasi user
         
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->boolean('is_public')->default(true);
           
            $table->unsignedInteger('downloads')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('file_uploads');
    }
};
