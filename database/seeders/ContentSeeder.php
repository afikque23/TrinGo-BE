<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up legacy duplicates (FAQ used to be seeded with different slugs).
        // This system expects exactly one record per static content type.
        Content::query()
            ->where('type', 'faq')
            ->where('slug', '!=', 'faq')
            ->delete();

        $contents = [
            [
                'title' => 'Syarat dan Ketentuan',
                'slug' => 'syarat-dan-ketentuan',
                'type' => 'terms',
                'body' => json_encode([
                    [
                        'section' => 'Syarat & Ketentuan',
                        'content' => 'Berlaku sejak: 30 Januari 2026'
                    ],
                    [
                        'section' => 'Selamat Datang di TringGo',
                        'content' => 'Dengan menggunakan aplikasi TringGo, Anda menyetujui syarat dan ketentuan berikut. Harap baca dengan seksama sebelum menggunakan layanan kami.'
                    ],
                    [
                        'section' => '1. Penerimaan Ketentuan',
                        'content' => 'Dengan membuat akun atau menggunakan TringGo, Anda menyatakan bahwa:',
                        'items' => [
                            'Anda berusia minimal 17 tahun atau memiliki izin dari orang tua/wali',
                            'Anda memiliki kapasitas hukum untuk menyetujui perjanjian ini',
                            'Informasi yang Anda berikan adalah akurat dan terkini',
                            'Anda akan mematuhi semua hukum dan peraturan yang berlaku'
                        ]
                    ],
                    [
                        'section' => '2. Akun Pengguna — Tanggung Jawab Akun',
                        'content' => '',
                        'items' => [
                            'Anda bertanggung jawab penuh atas keamanan akun dan password Anda',
                            'Jangan bagikan kredensial login Anda kepada siapapun',
                            'Segera laporkan aktivitas mencurigakan atau akses tidak sah',
                            'TringGo tidak bertanggung jawab atas kerugian akibat kelalaian keamanan akun'
                        ]
                    ],
                    [
                        'section' => '2. Akun Pengguna — Akurasi Data',
                        'content' => 'Anda setuju untuk menyediakan informasi yang akurat dan lengkap tentang kendaraan, jarak tempuh, dan riwayat perawatan. Data yang tidak akurat dapat mempengaruhi prediksi maintenance dan rekomendasi sistem.'
                    ],
                    [
                        'section' => '3. Penggunaan Layanan — Penggunaan yang Diizinkan',
                        'content' => '',
                        'items' => [
                            'Tracking dan manajemen kendaraan pribadi Anda',
                            'Recording perjalanan untuk keperluan personal',
                            'Mengatur jadwal maintenance dan service',
                            'Berbagi data dengan persetujuan eksplisit Anda'
                        ]
                    ],
                    [
                        'section' => '3. Penggunaan Layanan — Batasan Layanan',
                        'content' => 'Aplikasi ini disediakan "sebagaimana adanya". TringGo tidak menjamin:',
                        'items' => [
                            'Akurasi 100% prediksi maintenance (gunakan sebagai referensi)',
                            'GPS tracking selalu presisi (bergantung pada sinyal dan hardware)',
                            'Layanan tanpa gangguan atau error-free',
                            'Kompatibilitas dengan semua perangkat dan browser'
                        ]
                    ],
                    [
                        'section' => '4. Aktivitas yang Dilarang',
                        'content' => 'Anda DILARANG untuk:',
                        'items' => [
                            'Menggunakan aplikasi untuk tujuan ilegal atau melanggar hukum',
                            'Reverse engineering, decompile, atau menduplikasi kode aplikasi',
                            'Menggunakan bot, scraper, atau otomasi tanpa izin',
                            'Upload malware, virus, atau konten berbahaya',
                            'Menyalahgunakan data pengguna lain atau melanggar privasi',
                            'Membuat akun palsu atau menyamar sebagai orang/entitas lain',
                            'Menggunakan aplikasi untuk commercial tracking tanpa lisensi'
                        ]
                    ],
                    [
                        'section' => '5. GPS & Data Lokasi',
                        'content' => '',
                        'items' => [
                            'GPS tracking hanya aktif saat Anda memulai trip secara manual',
                            'Data lokasi disimpan lokal di perangkat Anda',
                            'Anda dapat menghapus riwayat lokasi kapan saja',
                            'Kami tidak tracking lokasi Anda di background tanpa izin',
                            'Gunakan GPS dengan bertanggung jawab dan patuhi aturan lalu lintas'
                        ]
                    ],
                    [
                        'section' => '6. Hak Kekayaan Intelektual',
                        'content' => 'Semua konten, fitur, dan fungsionalitas aplikasi TringGo (termasuk tetapi tidak terbatas pada desain, logo, teks, grafik, dan kode) adalah milik eksklusif TringGo dan dilindungi oleh hak cipta internasional.',
                        'items' => [
                            'Data Anda adalah milik Anda. Kami tidak mengklaim kepemilikan atas konten yang Anda upload atau buat di aplikasi.'
                        ]
                    ],
                    [
                        'section' => '7. Batasan Tanggung Jawab',
                        'content' => 'TringGo tidak bertanggung jawab atas:',
                        'items' => [
                            'Kerusakan kendaraan akibat kelalaian maintenance (gunakan rekomendasi kami sebagai panduan)',
                            'Kehilangan data akibat penghapusan browser cache atau factory reset',
                            'Kerugian finansial atau indirect damages dari penggunaan aplikasi',
                            'Kesalahan informasi bengkel atau workshop yang terdaftar',
                            'Gangguan layanan akibat force majeure (bencana alam, perang, dll)'
                        ]
                    ],
                    [
                        'section' => '8. Penangguhan & Penghentian',
                        'content' => 'Kami berhak untuk menangguhkan atau menghentikan akses Anda jika:',
                        'items' => [
                            'Anda melanggar syarat dan ketentuan ini',
                            'Kami mencurigai aktivitas fraud atau penyalahgunaan',
                            'Diwajibkan oleh hukum atau otoritas pemerintah',
                            'Anda tidak aktif selama lebih dari 2 tahun (dengan pemberitahuan)'
                        ]
                    ],
                    [
                        'section' => '9. Perubahan Ketentuan',
                        'content' => 'Kami dapat memperbarui syarat dan ketentuan ini sewaktu-waktu. Perubahan signifikan akan diberitahukan melalui email atau notifikasi in-app. Dengan terus menggunakan aplikasi setelah perubahan berlaku, Anda menyetujui ketentuan yang telah diperbarui.'
                    ],
                    [
                        'section' => '📧 Pertanyaan Legal',
                        'content' => 'Untuk pertanyaan terkait syarat dan ketentuan, hubungi kami di: legal@tringgo.id'
                    ],
                    [
                        'section' => 'Footer',
                        'content' => '© 2026 TringGo. All Rights Reserved. Dengan menggunakan aplikasi ini, Anda menyetujui syarat dan ketentuan di atas.'
                    ]
                ]),
                'status' => 'published',
                'order' => 1
            ],
            [
                'title' => 'Kebijakan Privasi',
                'slug' => 'kebijakan-privasi',
                'type' => 'privacy',
                'body' => json_encode([
                    [
                        'section' => 'Kebijakan Privasi',
                        'content' => 'Terakhir diperbarui: 30 Januari 2026'
                    ],
                    [
                        'section' => 'Komitmen Privasi Kami',
                        'content' => 'Di TringGo, kami sangat menghargai privasi Anda. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.'
                    ],
                    [
                        'section' => '1. Informasi yang Kami Kumpulkan — Informasi Akun',
                        'content' => '',
                        'items' => [
                            'Nama lengkap dan email saat registrasi',
                            'Password terenkripsi untuk keamanan akun',
                            'Nomor telepon (opsional) untuk verifikasi'
                        ]
                    ],
                    [
                        'section' => '1. Informasi yang Kami Kumpulkan — Data Kendaraan',
                        'content' => '',
                        'items' => [
                            'Informasi motor: merek, model, tahun, nomor plat',
                            'Odometer dan riwayat jarak tempuh',
                            'Foto kendaraan yang Anda upload'
                        ]
                    ],
                    [
                        'section' => '1. Informasi yang Kami Kumpulkan — Data Perjalanan & Lokasi',
                        'content' => '',
                        'items' => [
                            'Koordinat GPS saat tracking perjalanan (dengan izin Anda)',
                            'Rute, jarak, durasi, dan kecepatan perjalanan',
                            'Lokasi hanya diakses saat aplikasi aktif dan Anda mulai trip'
                        ]
                    ],
                    [
                        'section' => '1. Informasi yang Kami Kumpulkan — Riwayat Perawatan',
                        'content' => '',
                        'items' => [
                            'Tanggal dan jenis service yang dilakukan',
                            'Biaya perawatan dan catatan teknisi',
                            'Foto nota/bukti service (opsional)'
                        ]
                    ],
                    [
                        'section' => '2. Penggunaan Informasi',
                        'content' => '',
                        'items' => [
                            'Personalisasi Layanan: Menyesuaikan dashboard dan rekomendasi maintenance berdasarkan data motor Anda.',
                            'Prediksi Maintenance: Menggunakan AI untuk memprediksi jadwal service optimal berdasarkan pola penggunaan.',
                            'Notifikasi & Alert: Mengirim pengingat service, update aplikasi, dan informasi penting lainnya.',
                            'Peningkatan Layanan: Analisis data anonim untuk mengembangkan fitur baru dan memperbaiki performa aplikasi.',
                            'Keamanan: Mendeteksi aktivitas mencurigakan dan melindungi akun Anda dari akses tidak sah.'
                        ]
                    ],
                    [
                        'section' => '3. Keamanan Data',
                        'content' => '',
                        'items' => [
                            'Enkripsi End-to-End: Semua data sensitif dienkripsi saat transit dan penyimpanan.',
                            'Penyimpanan Lokal: Data disimpan di browser Anda. Anda memiliki kontrol penuh untuk menghapus kapan saja.',
                            'Akses Terbatas: Hanya tim engineering tertentu yang memiliki akses untuk maintenance sistem.',
                            'Audit Berkala: Kami melakukan security audit rutin untuk memastikan data Anda terlindungi.'
                        ]
                    ],
                    [
                        'section' => '4. Pembagian Data',
                        'content' => 'Kami TIDAK akan membagikan data Anda kepada pihak ketiga untuk tujuan marketing tanpa persetujuan eksplisit.',
                        'items' => [
                            'Jika diwajibkan oleh hukum atau perintah pengadilan',
                            'Dengan vendor analytics (data anonim untuk statistik)',
                            'Dengan persetujuan eksplisit Anda untuk integrasi pihak ketiga'
                        ]
                    ],
                    [
                        'section' => '5. Hak Anda',
                        'content' => '',
                        'items' => [
                            'Akses Data: Anda dapat mengunduh semua data Anda kapan saja melalui menu Privasi & Keamanan.',
                            'Koreksi: Update atau perbaiki informasi pribadi Anda langsung dari aplikasi.',
                            'Penghapusan: Hapus akun dan semua data terkait secara permanen melalui pengaturan.',
                            'Opt-out: Nonaktifkan tracking, analytics, atau notifikasi kapan saja sesuai preferensi Anda.'
                        ]
                    ],
                    [
                        'section' => '📧 Hubungi Kami',
                        'content' => 'Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami di: privacy@tringgo.id'
                    ],
                    [
                        'section' => 'Footer',
                        'content' => '© 2026 TringGo. Kebijakan ini dapat diperbarui sewaktu-waktu. Anda akan diberi tahu melalui email jika ada perubahan signifikan.'
                    ]
                ]),
                'status' => 'published',
                'order' => 2
            ],
            [
                'title' => 'Panduan Pengguna',
                'slug' => 'panduan-pengguna',
                'type' => 'guide',
                'body' => json_encode([
                    [
                        'section' => '📖 Selamat Datang!',
                        'content' => 'Panduan lengkap untuk memaksimalkan pengalaman Anda dengan TringGo. Pilih topik di bawah untuk mempelajari fitur-fitur aplikasi.'
                    ],
                    [
                        'section' => 'Memulai dengan TringGo',
                        'content' => '',
                        'items' => [
                            'Membuat Akun — Daftar dengan email dan password. Verifikasi email Anda melalui kode OTP yang dikirimkan. Setelah verifikasi, lengkapi profil Anda dengan informasi dasar.',
                            'Menambahkan Motor Pertama — Dari Dashboard, tap tombol "Tambah Motor". Isi detail seperti merek, model, tahun produksi, nomor plat, dan warna. Upload foto motor untuk personalisasi.',
                            'Navigasi Dashboard — Dashboard menampilkan ringkasan motor aktif, jarak tempuh, status maintenance, dan insights. Gunakan menu bawah untuk navigasi cepat antar fitur utama.'
                        ]
                    ],
                    [
                        'section' => 'Mengelola Kendaraan',
                        'content' => '',
                        'items' => [
                            'Multi Kendaraan — Tambahkan hingga 10 motor dalam satu akun. Switch antar kendaraan dengan tap pada nama motor di Dashboard atau menu Kendaraan.',
                            'Edit Informasi Motor — Buka detail kendaraan, tap ikon edit (pensil). Update informasi seperti kilometer, foto, atau spesifikasi teknis kapan saja.',
                            'Hapus Kendaraan — Di halaman detail kendaraan, scroll ke bawah dan tap "Hapus Kendaraan". Konfirmasi penghapusan - data tidak dapat dipulihkan.'
                        ]
                    ],
                    [
                        'section' => 'GPS Tracking & Perjalanan',
                        'content' => '',
                        'items' => [
                            'Memulai Trip Tracking — Pastikan GPS aktif. Tap "Mulai Perjalanan" di Dashboard atau buka menu GPS Tracking. Tap tombol "Start" untuk mulai merekam rute dan jarak.',
                            'Selama Perjalanan — Aplikasi akan tracking lokasi, jarak, waktu, dan kecepatan secara real-time. Anda bisa melihat statistik langsung di layar tracking.',
                            'Mengakhiri Trip — Tap "Stop" untuk mengakhiri perjalanan. Review ringkasan trip termasuk jarak, durasi, dan rute. Data otomatis tersimpan ke riwayat.',
                            'Melihat Riwayat Trip — Buka menu Trip History untuk melihat semua perjalanan. Filter berdasarkan tanggal, jarak, atau motor. Tap trip untuk detail lengkap dan peta rute.'
                        ]
                    ],
                    [
                        'section' => 'Maintenance & Service',
                        'content' => '',
                        'items' => [
                            'Smart Maintenance Tracking — Sistem otomatis menghitung interval service berdasarkan kilometer dan waktu. Notifikasi muncul saat mendekati jadwal maintenance.',
                            'Menambah Riwayat Service — Tap "Tambah Service" di menu Maintenance. Isi tanggal, jenis service, biaya, dan catatan. Upload bukti nota untuk dokumentasi.',
                            'Jadwal Service Berkala — Atur pengingat untuk ganti oli, kampas rem, ban, dll. Set interval berdasarkan kilometer atau bulan. Sistem akan alert sebelum jatuh tempo.',
                            'Workshop Locator — Cari bengkel terdekat dengan GPS. Lihat rating, kontak, jam operasional. Tap untuk call langsung atau buka di maps.'
                        ]
                    ],
                    [
                        'section' => 'Notifikasi & Pengingat',
                        'content' => '',
                        'items' => [
                            'Mengatur Notifikasi — Buka Profil > Notifikasi & Peringatan. Aktifkan/nonaktifkan notifikasi per kategori: Perawatan, Perjalanan, Kendaraan, Sistem.',
                            'Jenis Notifikasi — Service reminder (H-7, H-3, H-1), trip summary (harian/mingguan), vehicle status, dan system updates. Customize sesuai kebutuhan.'
                        ]
                    ],
                    [
                        'section' => 'Pengaturan & Personalisasi',
                        'content' => '',
                        'items' => [
                            'Preferensi Aplikasi — Pilih satuan jarak (km/miles), bahan bakar (liter/gallon). Atur GPS auto-start dan battery optimization untuk efisiensi.',
                            'Privasi & Keamanan — Aktifkan 2FA untuk keamanan ekstra. Kelola sesi aktif, ubah password, dan kontrol sharing data. Download atau hapus data kapan saja.',
                            'Backup Data — Export data Anda secara berkala. Download dalam format JSON untuk backup lokal. Cloud sync segera hadir.'
                        ]
                    ],
                    [
                        'section' => '💡 Tips & Trik',
                        'content' => '',
                        'items' => [
                            'Update kilometer secara rutin untuk prediksi maintenance yang akurat',
                            'Aktifkan GPS tracking untuk analisis perjalanan yang detail',
                            'Set reminder service H-7 agar tidak terlewat jadwal perawatan',
                            'Backup data Anda secara berkala untuk keamanan',
                            'Gunakan fitur multi-kendaraan untuk track semua motor Anda'
                        ]
                    ],
                    [
                        'section' => 'Bantuan',
                        'content' => 'Masih ada pertanyaan? Hubungi kami di support@tringgo.id'
                    ]
                ]),
                'status' => 'published',
                'order' => 3
            ],
            [
                'title' => 'Tentang TringGo',
                'slug' => 'tentang-tringgo',
                'type' => 'about',
                'body' => json_encode([
                    [
                        'section' => 'TringGo',
                        'content' => 'Your Smart Motorcycle Companion<br />Version 1.0.0 • Build 2026.01.30'
                    ],
                    [
                        'section' => 'Tentang Aplikasi',
                        'content' => 'TringGo adalah aplikasi manajemen sepeda motor yang lengkap, dirancang khusus untuk pengendara motor di Indonesia. Dengan fitur GPS tracking, smart maintenance scheduling, dan predictive analytics, kami membantu Anda menjaga motor tetap dalam kondisi prima.'
                    ]
                ]),
                'status' => 'published',
                'order' => 4
            ],
            [
                'title' => 'Pertanyaan yang Sering Diajukan',
                'slug' => 'faq',
                'type' => 'faq',
                'body' => json_encode([
                    [
                        'question' => 'Bagaimana cara menambahkan motor baru?',
                        'answer' => 'Buka halaman Kendaraan dari menu bawah, lalu tap tombol "+" di pojok kanan atas. Isi detail motor Anda seperti merek, model, tahun, dan nomor plat.'
                    ],
                    [
                        'question' => 'Bagaimana cara memulai tracking perjalanan?',
                        'answer' => 'Dari Dashboard, tap tombol "Mulai Perjalanan" atau buka menu GPS Tracking. Pastikan GPS dan izin lokasi sudah aktif. Tap "Start" untuk memulai recording.'
                    ],
                    [
                        'question' => 'Kenapa notifikasi servis tidak muncul?',
                        'answer' => 'Periksa pengaturan notifikasi di menu Profil > Notifikasi & Peringatan. Pastikan "Aktifkan Semua Notifikasi" dan "Jadwal Servis" dalam keadaan ON.'
                    ],
                    [
                        'question' => 'Bagaimana cara mengubah motor aktif?',
                        'answer' => 'Dari Dashboard, tap nama motor di bagian atas atau buka menu Kendaraan lalu pilih "Ganti Kendaraan Aktif". Pilih motor yang ingin Anda aktifkan.'
                    ],
                    [
                        'question' => 'Data saya hilang setelah update aplikasi?',
                        'answer' => 'Data disimpan di browser Anda secara lokal. Pastikan Anda tidak menghapus cache browser. Untuk keamanan data, kami sarankan rutin export data atau gunakan fitur backup cloud (segera hadir).'
                    ],
                    [
                        'question' => 'Bagaimana cara menghubungi bengkel?',
                        'answer' => 'Buka menu Workshop/Bengkel, pilih bengkel yang ingin Anda hubungi, lalu tap ikon telepon atau email untuk menghubungi mereka langsung.'
                    ]
                ]),
                'status' => 'published',
                'order' => 5
            ],
            [
                'title' => 'Cara Sistem Bekerja',
                'slug' => 'cara-sistem-bekerja',
                'type' => 'system_info',
                'body' => json_encode([
                    [
                        'section' => 'Bagaimana Sistem Bekerja?',
                        'content' => 'Transparansi algoritma dan logika sistem'
                    ],
                    [
                        'section' => 'Sistem Adaptif Berbasis Data',
                        'content' => 'Aplikasi ini menggunakan pendekatan analisis berbasis aturan (rule-based system) untuk mengoptimalkan jadwal perawatan kendaraan Anda. Sistem tidak menggunakan prediksi acak, melainkan perhitungan matematis berdasarkan data aktual.'
                    ],
                    [
                        'section' => 'Data yang Dianalisis Sistem',
                        'content' => '',
                        'items' => [
                            'Jarak Tempuh — Total kilometer yang telah ditempuh dan pola perjalanan harian/mingguan',
                            'Waktu Servis Terakhir — Tanggal dan odometer saat perawatan terakhir dilakukan',
                            'Frekuensi Penggunaan — Seberapa sering kendaraan digunakan dalam periode tertentu',
                            'Riwayat Perawatan — Data historis servis dan penggantian komponen'
                        ]
                    ],
                    [
                        'section' => 'Cara Kerja Sistem',
                        'content' => ''
                    ],
                    [
                        'section' => '1. Klasifikasi Pola Penggunaan',
                        'content' => 'Sistem menghitung rata-rata jarak tempuh harian dan frekuensi perjalanan untuk mengklasifikasikan penggunaan: Ringan (<15km/hari), Normal (15-50km/hari), atau Berat (>50km/hari).'
                    ],
                    [
                        'section' => '2. Penyesuaian Interval Perawatan',
                        'content' => 'Interval standar (contoh: ganti oli setiap 3000km) disesuaikan berdasarkan klasifikasi. Penggunaan berat menurunkan interval, penggunaan ringan dapat memperpanjang dengan batas aman.'
                    ],
                    [
                        'section' => '3. Deteksi Tren dan Anomali',
                        'content' => 'Sistem memantau perubahan pola mingguan untuk mendeteksi lonjakan atau penurunan aktivitas yang signifikan, dan memberikan rekomendasi proaktif.'
                    ],
                    [
                        'section' => '4. Rekomendasi Kontekstual',
                        'content' => 'Insight yang ditampilkan dipilih berdasarkan relevansi saat ini: jarak sejak servis, kondisi penggunaan, dan waktu yang telah berlalu sejak perawatan terakhir.'
                    ],
                    [
                        'section' => 'Catatan Teknis',
                        'content' => 'Sistem ini menggunakan pendekatan deterministik dengan threshold yang dapat disesuaikan. Semua perhitungan bersifat transparan dan dapat diverifikasi. Data tidak keluar dari perangkat Anda dan tidak dibagikan ke pihak ketiga.'
                    ],
                    [
                        'section' => 'Referensi Algoritma',
                        'content' => 'Adaptive Maintenance Scheduling based on Usage Pattern Classification with Rule-Based Decision System'
                    ]
                ]),
                'status' => 'published',
                'order' => 6
            ],
            [
                'title' => 'Bantuan & Dukungan',
                'slug' => 'bantuan-dukungan',
                'type' => 'support',
                'body' => json_encode([
                    [
                        'section' => 'Bantuan & Dukungan',
                        'content' => 'Kami siap membantu Anda'
                    ],
                    [
                        'section' => 'Hubungi Kami',
                        'content' => '',
                        'items' => [
                            'Live Chat — Respon instan dari tim support (Tersedia)',
                            'Email Support — support@tringgo.id (Tersedia)',
                            'Telepon — +62 812-3456-7890 (Segera)'
                        ]
                    ],
                    [
                        'section' => 'Sumber Daya',
                        'content' => '',
                        'items' => [
                            'Panduan Pengguna — Tutorial lengkap menggunakan TringGo',
                            'Kebijakan Privasi — Cara kami melindungi data Anda',
                            'Syarat & Ketentuan — Aturan penggunaan aplikasi'
                        ]
                    ],
                    [
                        'section' => '⏰ Jam Operasional Support',
                        'content' => '',
                        'items' => [
                            'Senin - Jumat: 09.00 - 18.00 WIB',
                            'Sabtu: 09.00 - 15.00 WIB',
                            'Minggu & Hari Libur: Tutup'
                        ]
                    ]
                ]),
                'status' => 'published',
                'order' => 7
            ]
        ];

        foreach ($contents as $content) {
            Content::updateOrCreate(
                ['slug' => $content['slug']],
                $content
            );
        }
    }
}
