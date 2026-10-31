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
        // 1. Modifikasi tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('supplier')->after('password');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        // 2. Tabel suppliers
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('business_name');
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        // 3. Modifikasi / lengkapi tabel warungs
        Schema::table('warungs', function (Blueprint $table) {
            if (!Schema::hasColumn('warungs', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade')->after('id');
            }
            if (!Schema::hasColumn('warungs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('supplier_id');
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

        // 4. Tabel products
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('product_code')->default('KRP-ORI');
                $table->string('name')->default('Keripik Pisang Original');
                $table->string('unit')->default('bungkus');
                $table->decimal('agreed_price', 12, 2)->default(15000);
                $table->decimal('cost_price', 12, 2)->default(12000);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. Tabel stock_confirmations (Konfirmasi Stok via WhatsApp H-2 sebelum kunjungan)
        if (!Schema::hasTable('stock_confirmations')) {
            Schema::create('stock_confirmations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->date('confirmation_date');
                $table->integer('reported_remaining_stock')->default(0);
                $table->integer('physically_checked_stock')->nullable();
                $table->string('data_status')->default('menunggu_pemeriksaan'); // menunggu_pemeriksaan, sesuai, selisih
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 6. Tabel sales_records (Rekam Penjualan Aktual per Periode Kunjungan)
        if (!Schema::hasTable('sales_records')) {
            Schema::create('sales_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->integer('distributed_quantity')->default(0);
                $table->integer('opening_stock')->default(0);
                $table->integer('remaining_stock')->default(0);
                $table->integer('sold_quantity')->default(0);
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

        // 7. Tabel restocks (Pencatatan Distribusi Fisik & Penarikan Stok Lama)
        if (!Schema::hasTable('restocks')) {
            Schema::create('restocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->date('distribution_date');
                $table->integer('quantity_delivered')->default(0);
                $table->integer('old_stock_withdrawn')->default(0);
                $table->integer('approved_quantity')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 8. Tabel stock_requests (Permintaan Stok Tambahan dari Warung / PO)
        if (!Schema::hasTable('stock_requests')) {
            Schema::create('stock_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->integer('requested_quantity');
                $table->integer('approved_quantity')->nullable();
                $table->text('reason')->nullable();
                $table->string('status')->default('menunggu'); // menunggu, disetujui, ditolak, dipenuhi
                $table->timestamp('requested_at')->useCurrent();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->timestamps();
            });
        }

        // 9. Tabel stock_recommendations (Rekomendasi WMA & Mode Konsinyasi)
        if (!Schema::hasTable('stock_recommendations')) {
            Schema::create('stock_recommendations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->decimal('forecast_quantity', 8, 2)->default(0);
                $table->integer('recommended_quantity')->default(0);
                $table->integer('approved_quantity')->nullable();
                $table->string('consignment_mode')->default('A'); // A: Stok lama ditarik & diganti, B: Stok lama tetap dijual
                $table->json('source_periods')->nullable();
                $table->json('weights_used')->nullable();
                $table->foreignId('stock_confirmation_id')->nullable()->constrained('stock_confirmations')->onDelete('set null');
                $table->date('calculation_date');
                $table->string('data_status')->default('cukup'); // cukup, belum_cukup, perlu_konfirmasi
                $table->text('adjustment_reason')->nullable();
                $table->timestamps();
            });
        }

        // 10. Tabel crm_alerts (Aturan CRM: Data Mining, Churn Prevention, Reorder Recommendation)
        if (!Schema::hasTable('crm_alerts')) {
            Schema::create('crm_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->string('category'); // data_mining, churn_prevention, reorder_recommendation
                $table->string('trigger_type'); // penurunan_penjualan_3_periode, tanpa_restock_2_siklus, tren_positif, stok_kritis
                $table->string('trigger_period')->nullable();
                $table->text('description');
                $table->json('evidence_data')->nullable();
                $table->string('status')->default('perlu_ditinjau'); // perlu_ditinjau, perlu_dihubungi, sudah_dihubungi, dalam_tindak_lanjut, selesai
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // 11. Tabel crm_follow_ups (Catatan Tindak Lanjut CRM oleh Supplier)
        if (!Schema::hasTable('crm_follow_ups')) {
            Schema::create('crm_follow_ups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('crm_alert_id')->constrained('crm_alerts')->onDelete('cascade');
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('warung_id')->constrained('warungs')->onDelete('cascade');
                $table->date('follow_up_date');
                $table->string('contact_method')->default('whatsapp'); // whatsapp, telepon, kunjungan_langsung
                $table->text('result_notes');
                $table->text('next_action')->nullable();
                $table->string('status')->default('selesai'); // selesai, dalam_proses, tinjau_ulang
                $table->timestamps();
            });
        }

        // 12. Tabel system_settings (Konfigurasi Bobot WMA, Ambang Churn, Mode Konsinyasi)
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade');
                $table->string('setting_key')->unique();
                $table->text('setting_value');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('crm_follow_ups');
        Schema::dropIfExists('crm_alerts');
        Schema::dropIfExists('stock_recommendations');
        Schema::dropIfExists('stock_requests');
        Schema::dropIfExists('restocks');
        Schema::dropIfExists('sales_records');
        Schema::dropIfExists('stock_confirmations');
        Schema::dropIfExists('products');
        
        Schema::table('warungs', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'supplier_id',
                'user_id',
                'name',
                'owner_name',
                'phone',
                'partnership_start_date',
                'delivery_interval_days',
                'partnership_status',
            ]);
        });

        Schema::dropIfExists('suppliers');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
