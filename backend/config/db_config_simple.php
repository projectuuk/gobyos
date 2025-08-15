<?php
// Simple database configuration with mock data for testing
// This bypasses MySQL connection issues

define('DB_HOST', 'localhost');
define('DB_NAME', 'fio_trans_cargo_test');
define('DB_USER', 'root');
define('DB_PASS', '');

// Mock data for testing
class MockDatabase {
    public static function getCategories() {
        return [
            ['id' => 1, 'name' => 'Tips Pengiriman', 'slug' => 'tips-pengiriman', 'description' => 'Tips dan trik untuk pengiriman yang aman dan efisien'],
            ['id' => 2, 'name' => 'Berita Logistik', 'slug' => 'berita-logistik', 'description' => 'Berita terkini seputar industri logistik dan pengiriman'],
            ['id' => 3, 'name' => 'Tutorial', 'slug' => 'tutorial', 'description' => 'Panduan dan tutorial penggunaan layanan'],
            ['id' => 4, 'name' => 'Informasi Umum', 'slug' => 'informasi-umum', 'description' => 'Informasi umum seputar layanan dan perusahaan']
        ];
    }
    
    public static function getPosts() {
        return [
            [
                'id' => 1,
                'title' => 'Tips Mengemas Barang Pecah Belah untuk Pengiriman Aman',
                'slug' => 'tips-mengemas-barang-pecah-belah',
                'content' => '<p>Mengemas barang pecah belah memerlukan perhatian khusus untuk memastikan keamanan selama proses pengiriman. Berikut adalah panduan lengkap untuk mengemas barang pecah belah dengan aman:</p><h2>1. Persiapan Bahan Kemasan</h2><p>Siapkan bahan-bahan kemasan yang berkualitas seperti bubble wrap, kertas koran, kardus yang kuat, dan lakban berkualitas tinggi.</p><h2>2. Teknik Pembungkusan</h2><p>Bungkus setiap item secara individual dengan bubble wrap, pastikan tidak ada bagian yang terekspos.</p>',
                'excerpt' => 'Panduan lengkap cara mengemas barang pecah belah agar aman selama proses pengiriman dengan tips dan trik dari ahli logistik.',
                'featured_image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
                'category_id' => 1,
                'category_name' => 'Tips Pengiriman',
                'status' => 'published',
                'meta_title' => 'Tips Mengemas Barang Pecah Belah - Fio Trans Cargo',
                'meta_description' => 'Pelajari cara mengemas barang pecah belah dengan aman untuk pengiriman. Tips dari ahli logistik Fio Trans Cargo.',
                'views' => 1250,
                'created_at' => '2024-01-15 10:30:00',
                'updated_at' => '2024-01-15 10:30:00'
            ],
            [
                'id' => 2,
                'title' => 'Perkembangan Industri Logistik di Indonesia 2024',
                'slug' => 'perkembangan-industri-logistik-indonesia-2024',
                'content' => '<p>Industri logistik Indonesia mengalami pertumbuhan yang signifikan di tahun 2024. Berbagai faktor mendorong perkembangan ini, mulai dari digitalisasi hingga infrastruktur yang semakin baik.</p><h2>Tren Digitalisasi</h2><p>Penggunaan teknologi digital dalam industri logistik semakin masif, mulai dari sistem tracking hingga otomasi gudang.</p><h2>Infrastruktur Pendukung</h2><p>Pembangunan infrastruktur seperti jalan tol dan pelabuhan baru mendukung efisiensi distribusi barang.</p>',
                'excerpt' => 'Analisis mendalam tentang tren dan perkembangan industri logistik Indonesia di tahun 2024 dengan berbagai inovasi terbaru.',
                'featured_image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=800',
                'category_id' => 2,
                'category_name' => 'Berita Logistik',
                'status' => 'published',
                'meta_title' => 'Perkembangan Industri Logistik Indonesia 2024 - Fio Trans Cargo',
                'meta_description' => 'Analisis tren industri logistik Indonesia 2024, digitalisasi, dan infrastruktur pendukung dari Fio Trans Cargo.',
                'views' => 890,
                'created_at' => '2024-01-14 14:20:00',
                'updated_at' => '2024-01-14 14:20:00'
            ],
            [
                'id' => 3,
                'title' => 'Cara Melacak Paket dengan Mudah',
                'slug' => 'cara-melacak-paket-dengan-mudah',
                'content' => '<p>Melacak paket kini menjadi lebih mudah dengan berbagai teknologi yang tersedia. Berikut adalah panduan lengkap untuk melacak paket Anda:</p><h2>1. Menggunakan Nomor Resi</h2><p>Setiap paket memiliki nomor resi unik yang dapat digunakan untuk tracking.</p><h2>2. Platform Online</h2><p>Gunakan website atau aplikasi mobile untuk tracking real-time.</p>',
                'excerpt' => 'Tutorial step-by-step untuk melacak status pengiriman paket Anda dengan mudah dan akurat menggunakan berbagai metode.',
                'featured_image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
                'category_id' => 3,
                'category_name' => 'Tutorial',
                'status' => 'draft',
                'meta_title' => 'Cara Melacak Paket - Tutorial Lengkap',
                'meta_description' => 'Pelajari cara melacak paket dengan mudah menggunakan nomor resi dan platform online Fio Trans Cargo.',
                'views' => 0,
                'created_at' => '2024-01-13 09:15:00',
                'updated_at' => '2024-01-13 09:15:00'
            ],
            [
                'id' => 4,
                'title' => 'Panduan Lengkap Pengiriman Barang ke Luar Negeri',
                'slug' => 'panduan-lengkap-pengiriman-barang-ke-luar-negeri',
                'content' => '<p>Ini adalah konten lengkap untuk artikel Panduan Lengkap Pengiriman Barang ke Luar Negeri.</p>',
                'excerpt' => 'Panduan komprehensif untuk mengirim barang ke luar negeri, mencakup tips, regulasi, dan proses pengiriman yang aman dan efisien.',
                'featured_image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=800',
                'category_id' => 1,
                'category_name' => 'Tips Pengiriman',
                'status' => 'published',
                'meta_title' => 'Panduan Lengkap Pengiriman Barang ke Luar Negeri - Fio Trans Cargo',
                'meta_description' => 'Pelajari panduan lengkap pengiriman barang ke luar negeri dengan Fio Trans Cargo. Tips, regulasi, dan proses pengiriman.',
                'views' => 50,
                'created_at' => '2024-08-13 10:00:00',
                'updated_at' => '2024-08-13 10:00:00'
            ]
        ];
    }
}
?>

