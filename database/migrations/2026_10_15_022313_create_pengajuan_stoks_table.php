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
        Schema::create('pengajuan_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
            $table->date('tanggal_pengajuan');
            $table->integer('jumlah_diminta');
            $table->string('status')->default('pending'); // pending, diproses, selesai, ditolak
            $table->text('catatan')->nullable();
            $table->text('catatan_pemasok')->nullable();
            $table->timestamp('tanggal_diproses')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_stoks');
    }
};
