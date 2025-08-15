<?php

$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// Define routes
$routes = [
    // Frontend routes
    '/' => 'frontend/blog.html',
    '/blog' => 'frontend/blog.html',
    '/artikel' => 'frontend/article.html',
    '/admin' => 'admin/dashboard.html',
    '/admin/posts' => 'admin/posts.html',
    
    // Backend API routes
    '/backend/api/posts' => 'backend/api/posts.php',
    '/backend/api/posts_mock' => 'backend/api/posts_mock.php',
    '/backend/api/categories' => 'backend/api/categories.php',
    '/backend/api/seo' => 'backend/api/seo.php',
    '/backend/api/analytics' => 'backend/api/analytics.php',
    '/backend/api/auth' => 'backend/api/auth.php',
    '/backend/api/pages' => 'backend/api/pages.php',
    '/backend/api/bookings' => 'backend/api/bookings.php',
    
    // Assets
    '/frontend/assets/css/style.css' => 'frontend/assets/css/style.css',
    '/frontend/assets/js/main.js' => 'frontend/assets/js/main.js',
    '/frontend/assets/js/blog.js' => 'frontend/assets/js/blog.js',
    '/frontend/assets/js/article.js' => 'frontend/assets/js/article.js',
    '/frontend/assets/js/analytics.js' => 'frontend/assets/js/analytics.js',
    '/frontend/assets/js/admin-posts.js' => 'frontend/assets/js/admin-posts.js',
    '/frontend/assets/images/logo.png' => 'frontend/assets/images/logo.png',
    '/frontend/assets/images/blog-og.jpg' => 'frontend/assets/images/blog-og.jpg',
    
    // Installer
    '/install.php' => 'install.php',
    '/README.md' => 'README.md',
];

// Simple routing logic
if (array_key_exists($request_uri, $routes)) {
    $file = __DIR__ . '/' . $routes[$request_uri];
    if (file_exists($file)) {
        if (strpos($file, '.php') !== false) {
            require $file;
        } else {
            readfile($file);
        }
    } else {
        http_response_code(404);
        echo "404 Not Found: File not found for route.";
    }
} else if (preg_match('/^\/([a-zA-Z0-9-]+)$/', $request_uri, $matches)) {
    // Handle /{slug} permalink (simple postname format)
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/frontend/article.html'; // This will be handled by JS
} else if (preg_match('/^\/backend\/api\/(posts|categories|seo|analytics|auth|pages|bookings)(\.php)?$/', $request_uri, $matches)) {
    // Handle API calls directly
    require __DIR__ . '/backend/api/' . $matches[1] . '.php';
} else {
    http_response_code(404);
    echo "404 Not Found: No route defined for this URL.";
}

?>

