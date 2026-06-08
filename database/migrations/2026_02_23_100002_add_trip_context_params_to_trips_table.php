<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah parameter konteks perjalanan untuk kalkulasi service score yang akurat.
     * Kolom auto-detect diisi otomatis dari GPS, bisa di-override user (kalibrasi).
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // ─── Sumber data ────────────────────────────────────────────
            $table->enum('source', ['gps', 'manual'])
                  ->default('gps')
                  ->after('notes')
                  ->comment('Asal data perjalanan: GPS otomatis atau input manual');

            // ─── Parameter konteks (auto-detect dari GPS, bisa dikalibrasi) ──
            $table->enum('kondisi_lalu_lintas', ['macet', 'sedang', 'lancar'])
                  ->nullable()
                  ->after('source')
                  ->comment('Kondisi lalu lintas: auto dari avg_speed, bisa di-override');

            $table->enum('medan', ['datar', 'berbukit', 'campuran'])
                  ->nullable()
                  ->after('kondisi_lalu_lintas')
                  ->comment('Medan jalan: auto dari elevation_gain, bisa di-override');

            $table->enum('gaya_berkendara', ['pelan', 'normal', 'agresif'])
                  ->nullable()
                  ->after('medan')
                  ->comment('Gaya berkendara: auto dari avg_speed, bisa di-override');

            // ─── Parameter manual (diisi user, opsional) ─────────────
            $table->enum('beban', ['ringan', 'sedang', 'berat'])
                  ->nullable()
                  ->after('gaya_berkendara')
                  ->comment('Beban bawaan saat perjalanan (input user)');

            $table->boolean('ada_penumpang')
                  ->nullable()
                  ->after('beban')
                  ->comment('Apakah membawa penumpang saat perjalanan');

            // ─── Data GPS tambahan (otomatis dari tracking) ───────────
            $table->integer('elevation_gain')
                  ->unsigned()
                  ->nullable()
                  ->after('ada_penumpang')
                  ->comment('Total kenaikan elevasi dalam meter (dari GPS/barometer)');

            $table->integer('idle_time_minutes')
                  ->unsigned()
                  ->nullable()
                  ->after('elevation_gain')
                  ->comment('Total waktu berhenti (speed=0) dalam menit');

            $table->integer('rough_road_count')
                  ->unsigned()
                  ->default(0)
                  ->after('idle_time_minutes')
                  ->comment('Jumlah deteksi jalan rusak/berlubang dari accelerometer');

            $table->integer('hard_acceleration_count')
                  ->unsigned()
                  ->default(0)
                  ->after('rough_road_count')
                  ->comment('Jumlah deteksi akselerasi kasar dari accelerometer');

            $table->integer('hard_braking_count')
                  ->unsigned()
                  ->default(0)
                  ->after('hard_acceleration_count')
                  ->comment('Jumlah deteksi pengereman keras dari accelerometer');

            // ─── Kalibrasi ────────────────────────────────────────────
            $table->boolean('is_calibrated')
                  ->default(false)
                  ->after('hard_braking_count')
                  ->comment('True jika user sudah mengedit kondisi perjalanan secara manual');

            // ─── Service score ─────────────────────────────────────────
            $table->decimal('service_score_factor', 5, 2)
                  ->default(1.00)
                  ->after('is_calibrated')
                  ->comment('Bobot akhir perjalanan ini terhadap kalkulasi jadwal service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'kondisi_lalu_lintas',
                'medan',
                'gaya_berkendara',
                'beban',
                'ada_penumpang',
                'elevation_gain',
                'idle_time_minutes',
                'rough_road_count',
                'hard_acceleration_count',
                'hard_braking_count',
                'is_calibrated',
                'service_score_factor',
            ]);
        });
    }
};
