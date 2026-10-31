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
        Schema::table('stok_penjualans', function (Blueprint $table) {
            $table->integer('sisa_fisik')->nullable()->after('stok_sebelumnya');
            $table->text('keputusan_stok')->nullable()->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_penjualans', function (Blueprint $table) {
            $table->dropColumn(['sisa_fisik', 'keputusan_stok']);
        });
    }
};
