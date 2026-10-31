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
        Schema::create('stok_penjualans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
            $table->string('tipe_transaksi')->default('kunjungan'); // titip_awal, kunjungan, tambah_stok
            $table->date('tanggal_titip')->nullable();
            $table->integer('jumlah_titip')->default(0);
            $table->date('tanggal_cek')->nullable();
            $table->integer('stok_sebelumnya')->default(0);
            $table->integer('jumlah_laku')->default(0);
            $table->integer('sisa_stok')->default(0);
            $table->integer('total_pendapatan')->default(0); // kalkulasi jumlah_laku * harga_titip
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_penjualans');
    }
};
