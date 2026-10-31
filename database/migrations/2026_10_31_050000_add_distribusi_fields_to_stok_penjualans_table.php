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
            if (!Schema::hasColumn('stok_penjualans', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('warung_id')->constrained('products')->onDelete('set null');
            }
            if (!Schema::hasColumn('stok_penjualans', 'old_stock_withdrawn')) {
                $table->integer('old_stock_withdrawn')->default(0)->after('jumlah_titip');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stok_penjualans', function (Blueprint $table) {
            if (Schema::hasColumn('stok_penjualans', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('stok_penjualans', 'old_stock_withdrawn')) {
                $table->dropColumn('old_stock_withdrawn');
            }
        });
    }
};
