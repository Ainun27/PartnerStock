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
        Schema::create('warungs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_warung');
            $table->string('nama_pemilik');
            $table->text('alamat');
            $table->string('kontak');
            $table->string('kode_unik')->unique();
            $table->string('pin')->default('1234');
            $table->integer('stok_saat_ini')->default(0);
            $table->integer('harga_titip')->default(12000);
            $table->integer('harga_jual')->default(15000);
            $table->boolean('is_aktif')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warungs');
    }
};
