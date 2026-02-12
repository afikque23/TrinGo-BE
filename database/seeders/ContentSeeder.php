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
        $contents = [
            [
                'title' => 'Syarat dan Ketentuan',
                'slug' => 'syarat-dan-ketentuan',
                'type' => 'terms',
                'body' => json_encode([
                    [
                        'section' => 'Selamat Datang di MotoTracker',
                        'content' => 'Dengan menggunakan aplikasi MotoTracker, Anda menyatakan bahwa Anda telah membaca, memahami, dan menyetujui untuk terikat oleh Syarat dan Ketentuan ini.'
                    ],
                    [
                        'section' => '1. Penerimaan Ketentuan',
                        'content' => 'Dengan membuat akun atau menggunakan MotoTracker, Anda menyatakan bahwa:',
                        'items' => [
                            'Anda memiliki hak penuh untuk memiliki dan menggunakan kendaraan yang terdaftar',
                            'Anda memiliki kapasitas hukum untuk menyetujui perjanjian yang mengikat',
                            'Informasi yang Anda berikan adalah akurat dan lengkap',
                            'Anda akan mematuhi semua hukum dan peraturan yang berlaku'
                        ]
                    ],
                    [
                        'section' => '2. Akun Pengguna',
                        'content' => '',
                        'items' => [
                            'Anda bertanggung jawab atas keamanan akun dan password Anda',
                            'Jangan bagikan kredensial login Anda kepada siapapun',
                            'Segera laporkan jika mencurigai akses tidak sah',
                            'MotoTracker tidak bertanggung jawab atas kerugian akibat kelalaian menjaga keamanan akun'
                        ]
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
                        'section' => 'Pendahuluan',
                        'content' => 'MotoTracker menghormati privasi Anda dan berkomitmen untuk melindungi data pribadi yang Anda berikan.'
                    ],
                    [
                        'section' => 'Data yang Kami Kumpulkan',
                        'items' => [
                            'Informasi akun: email, nama, nomor telepon',
                            'Data kendaraan: merek, model, tahun, plat nomor',
                            'Data perjalanan: rute, jarak, waktu',
                            'Data perawatan: riwayat servis, biaya, komponen'
                        ]
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
                        'section' => 'Memulai dengan MotoTracker',
                        'content' => 'Ikuti langkah-langkah berikut untuk memaksimalkan penggunaan aplikasi.'
                    ],
                    [
                        'section' => 'Langkah 1: Buat Akun',
                        'items' => [
                            'Buka aplikasi MotoTracker',
                            'Klik tombol "Daftar"',
                            'Masukkan email dan password',
                            'Verifikasi email Anda'
                        ]
                    ]
                ]),
                'status' => 'published',
                'order' => 3
            ],
            [
                'title' => 'Tentang MotoTracker',
                'slug' => 'tentang-mototracker',
                'type' => 'about',
                'body' => json_encode([
                    [
                        'section' => 'Tentang Aplikasi',
                        'content' => 'MotoTracker adalah aplikasi manajemen kendaraan bermotor yang membantu Anda melacak perjalanan, mengatur jadwal perawatan, dan memantau kondisi kendaraan.'
                    ],
                    [
                        'section' => 'Fitur Utama',
                        'items' => [
                            'Tracking perjalanan real-time',
                            'Manajemen jadwal servis',
                            'Reminder otomatis perawatan',
                            'Analisis biaya operasional'
                        ]
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
                        'question' => 'Bagaimana cara menambahkan kendaraan baru?',
                        'answer' => 'Buka menu Kendaraan, klik tombol "+", lalu isi informasi kendaraan Anda.'
                    ],
                    [
                        'question' => 'Apakah data saya aman?',
                        'answer' => 'Ya, kami menggunakan enkripsi end-to-end dan tidak membagikan data Anda kepada pihak ketiga.'
                    ],
                    [
                        'question' => 'Bagaimana cara mengatur reminder servis?',
                        'answer' => 'Buka menu Jadwal Servis, pilih kendaraan, lalu atur interval perawatan berdasarkan kilometer atau waktu.'
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
                        'section' => 'Sistem Adaptif Berbasis Data',
                        'content' => 'Aplikasi ini menggunakan pendekatan analisis berbasis data untuk memberikan rekomendasi perawatan yang disesuaikan dengan pola penggunaan kendaraan Anda.'
                    ],
                    [
                        'section' => 'Data yang Dianalisis Sistem',
                        'items' => [
                            'Jarak Tempuh - Total kilometer yang telah ditempuh dan pola perjalanan',
                            'Waktu Servis Terakhir - Tanggal dan odometer saat perawatan terakhir',
                            'Frekuensi Penggunaan - Seberapa sering kendaraan digunakan',
                            'Riwayat Perawatan - Data historis servis dan komponen'
                        ]
                    ],
                    [
                        'section' => '1. Klasifikasi Pola Penggunaan',
                        'content' => 'Sistem menghitung rata-rata jarak tempuh harian dan mengklasifikasikan: Ringan (<15km/hari), Normal (15-50km/hari), atau Berat (>50km/hari).'
                    ],
                    [
                        'section' => '2. Penyesuaian Interval Perawatan',
                        'content' => 'Interval standar disesuaikan dengan kategori penggunaan—penggunaan berat mendapat interval lebih pendek.'
                    ]
                ]),
                'status' => 'published',
                'order' => 6
            ]
        ];

        foreach ($contents as $content) {
            Content::create($content);
        }
    }
}
