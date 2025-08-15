<?php
// Database configuration for Fio Trans Cargo Website
// This file is created by the installer or manually for testing

define('DB_HOST', 'localhost');
define('DB_NAME', 'fio_trans_cargo_test');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database connection test
try {
    $test_conn = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
    $test_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $test_conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Select the database
    $test_conn->exec("USE " . DB_NAME);
    
    // Create basic tables if they don't exist
    $test_conn->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    $test_conn->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT,
            excerpt TEXT,
            featured_image VARCHAR(500),
            category_id INT,
            status ENUM('draft', 'published', 'pending') DEFAULT 'draft',
            meta_title VARCHAR(255),
            meta_description TEXT,
            views INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");
    
    // Insert sample categories if table is empty
    $stmt = $test_conn->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        $test_conn->exec("
            INSERT INTO categories (name, slug, description) VALUES
            ('Tips Pengiriman', 'tips-pengiriman', 'Tips dan trik untuk pengiriman yang aman dan efisien'),
            ('Berita Logistik', 'berita-logistik', 'Berita terkini seputar industri logistik dan pengiriman'),
            ('Tutorial', 'tutorial', 'Panduan dan tutorial penggunaan layanan'),
            ('Informasi Umum', 'informasi-umum', 'Informasi umum seputar layanan dan perusahaan')
        ");
    }
    
    // Insert sample posts if table is empty
    $stmt = $test_conn->query("SELECT COUNT(*) FROM posts");
    if ($stmt->fetchColumn() == 0) {
        $test_conn->exec("
            INSERT INTO posts (title, slug, content, excerpt, category_id, status, meta_title, meta_description, views) VALUES
            ('Tips Mengemas Barang Pecah Belah untuk Pengiriman Aman', 'tips-mengemas-barang-pecah-belah', 
             '<p>Mengemas barang pecah belah memerlukan perhatian khusus untuk memastikan keamanan selama proses pengiriman. Berikut adalah panduan lengkap untuk mengemas barang pecah belah dengan aman:</p><h2>1. Persiapan Bahan Kemasan</h2><p>Siapkan bahan-bahan kemasan yang berkualitas seperti bubble wrap, kertas koran, kardus yang kuat, dan lakban berkualitas tinggi.</p><h2>2. Teknik Pembungkusan</h2><p>Bungkus setiap item secara individual dengan bubble wrap, pastikan tidak ada bagian yang terekspos.</p>', 
             'Panduan lengkap cara mengemas barang pecah belah agar aman selama proses pengiriman dengan tips dan trik dari ahli logistik.', 
             1, 'published', 'Tips Mengemas Barang Pecah Belah - Fio Trans Cargo', 'Pelajari cara mengemas barang pecah belah dengan aman untuk pengiriman. Tips dari ahli logistik Fio Trans Cargo.', 1250),
            ('Perkembangan Industri Logistik di Indonesia 2024', 'perkembangan-industri-logistik-indonesia-2024', 
             '<p>Industri logistik Indonesia mengalami pertumbuhan yang signifikan di tahun 2024. Berbagai faktor mendorong perkembangan ini, mulai dari digitalisasi hingga infrastruktur yang semakin baik.</p><h2>Tren Digitalisasi</h2><p>Penggunaan teknologi digital dalam industri logistik semakin masif, mulai dari sistem tracking hingga otomasi gudang.</p><h2>Infrastruktur Pendukung</h2><p>Pembangunan infrastruktur seperti jalan tol dan pelabuhan baru mendukung efisiensi distribusi barang.</p>', 
             'Analisis mendalam tentang tren dan perkembangan industri logistik Indonesia di tahun 2024 dengan berbagai inovasi terbaru.', 
             2, 'published', 'Perkembangan Industri Logistik Indonesia 2024 - Fio Trans Cargo', 'Analisis tren industri logistik Indonesia 2024, digitalisasi, dan infrastruktur pendukung dari Fio Trans Cargo.', 890),
            ('Cara Melacak Paket dengan Mudah', 'cara-melacak-paket-dengan-mudah', 
             '<p>Melacak paket kini menjadi lebih mudah dengan berbagai teknologi yang tersedia. Berikut adalah panduan lengkap untuk melacak paket Anda:</p><h2>1. Menggunakan Nomor Resi</h2><p>Setiap paket memiliki nomor resi unik yang dapat digunakan untuk tracking.</p><h2>2. Platform Online</h2><p>Gunakan website atau aplikasi mobile untuk tracking real-time.</p>', 
             'Tutorial step-by-step untuk melacak status pengiriman paket Anda dengan mudah dan akurat menggunakan berbagai metode.', 
             3, 'draft', 'Cara Melacak Paket - Tutorial Lengkap', 'Pelajari cara melacak paket dengan mudah menggunakan nomor resi dan platform online Fio Trans Cargo.', 0)
        ");
    }
    
    $test_conn = null;
    
} catch(PDOException $e) {
    error_log("Database setup error: " . $e->getMessage());
}
?>

