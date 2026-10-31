<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai arsitektur 5 Tabel Utama B2B PartnerStock (3NF Non-Redundant):
     * 1. users
     * 2. warungs
     * 3. products
     * 4. stok_penjualans
     * 5. pengajuan_stoks
     */
    public function up(): void
    {
        // 1. Modifikasi tabel users (Multi-role authentication)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('supplier')->after('password');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        // 2. Master Tabel products (Keripik Pisang Original)
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('product_code')->default('KRP-ORI');
                $table->string('name')->default('Keripik Pisang Original');
                $table->string('unit')->default('bungkus');
                $table->decimal('agreed_price', 12, 2)->default(15000);
                $table->decimal('cost_price', 12, 2)->default(12000);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Modifikasi tabel warungs (Kelengkapan data mitra konsinyasi)
        Schema::table('warungs', function (Blueprint $table) {
            if (!Schema::hasColumn('warungs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('id');
            }
            if (!Schema::hasColumn('warungs', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('warungs', 'owner_name')) {
                $table->string('owner_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('warungs', 'phone')) {
                $table->string('phone')->nullable()->after('owner_name');
            }
            if (!Schema::hasColumn('warungs', 'partnership_start_date')) {
                $table->date('partnership_start_date')->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('warungs', 'delivery_interval_days')) {
                $table->integer('delivery_interval_days')->default(14)->after('partnership_start_date');
            }
            if (!Schema::hasColumn('warungs', 'partnership_status')) {
                $table->string('partnership_status')->default('aktif')->after('delivery_interval_days');
            }
            if (!Schema::hasColumn('warungs', 'deleted_at')) {
                $table->softDeletes()->after('catatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');

        Schema::table('warungs', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'user_id',
                'name',
                'owner_name',
                'phone',
                'partnership_start_date',
                'delivery_interval_days',
                'partnership_status',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
