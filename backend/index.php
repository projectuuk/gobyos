<?php
// Simple router for API endpoints
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$base_path = '/calista_express_website/backend';
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

// Route to appropriate API endpoint
switch ($path) {
    case '/api/pages':
    case '/api/pages/':
        include 'api/pages.php';
        break;
        
    case '/api/posts':
    case '/api/posts/':
        include 'api/posts.php';
        break;
        
    case '/api/bookings':
    case '/api/bookings/':
        include 'api/bookings.php';
        break;
        
    case '/':
    case '':
        // API documentation or status
        echo json_encode([
            'message' => 'Calista Express API',
            'version' => '1.0',
            'endpoints' => [
                '/api/pages' => 'Pages management',
                '/api/posts' => 'Blog posts management',
                '/api/bookings' => 'Booking management'
            ]
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found']);
        break;
}
?>

