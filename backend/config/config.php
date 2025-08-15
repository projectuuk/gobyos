<?php
// Configuration file for Fio Trans Cargo Website

// Check if configuration file exists (created by installer)
if (file_exists(__DIR__ . '/db_config.php')) {
    include_once __DIR__ . '/db_config.php';
} else {
    // Default configuration (fallback)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'fio_trans_cargo');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Site configuration
define('SITE_URL', 'http://localhost');
define('SITE_NAME', 'Fio Trans Cargo');
define('SITE_DESCRIPTION', 'Your trusted shipping partner for all of Indonesia');

// SEO configuration
define('DEFAULT_META_TITLE', 'Fio Trans Cargo - Jasa Pengiriman Barang Terpercaya');
define('DEFAULT_META_DESCRIPTION', 'Fio Trans Cargo menyediakan layanan pengiriman barang ke seluruh Indonesia dengan aman, cepat, dan terpercaya.');
define('DEFAULT_META_KEYWORDS', 'jasa pengiriman, ekspedisi, cargo, pengiriman barang, logistik, trucking, kapal roro, container');

// Analytics configuration
define('ENABLE_ANALYTICS', true);
define('ANALYTICS_TRACK_ADMIN', false); // Don't track admin visits

// Security configuration
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);

// File upload configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', array('jpg', 'jpeg', 'png', 'gif', 'webp'));
define('UPLOAD_PATH', '../uploads/');

// Pagination configuration
define('POSTS_PER_PAGE', 10);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Email configuration (for contact forms, notifications)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@fiotranscargo.com');
define('FROM_NAME', 'Fio Trans Cargo');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

