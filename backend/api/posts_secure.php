<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../models/Post.php';
require_once '../security/InputValidator.php';
require_once '../security/DatabaseSecurity.php';

// Rate limiting
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!InputValidator::checkRateLimit($clientIP, 30, 60)) { // 30 requests per minute
    http_response_code(429);
    echo json_encode(['message' => 'Rate limit exceeded']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$post = new Post($db);
$dbSecurity = new DatabaseSecurity($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['slug'])) {
            $slug = InputValidator::validateSlug($_GET['slug']);
            if ($slug === false) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid slug format']);
                break;
            }
            
            $post->slug = $slug;
            if ($post->readOne()) {
                $result = [
                    'id' => $post->id,
                    'title' => InputValidator::sanitizeString($post->title),
                    'slug' => $post->slug,
                    'content' => InputValidator::sanitizeString($post->content, true),
                    'excerpt' => InputValidator::sanitizeString($post->excerpt),
                    'featured_image' => $post->featured_image,
                    'meta_title' => InputValidator::sanitizeString($post->meta_title),
                    'meta_description' => InputValidator::sanitizeString($post->meta_description),
                    'category_id' => $post->category_id,
                    'status' => $post->status,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at
                ];
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Post not found']);
            }
        } else {
            // Handle search and filtering with validation
            $search = isset($_GET['search']) ? InputValidator::sanitizeString($_GET['search']) : '';
            $category = isset($_GET['category']) ? InputValidator::validateInteger($_GET['category'], 1) : null;
            $status = isset($_GET['status']) ? InputValidator::sanitizeString($_GET['status']) : '';
            $limit = isset($_GET['limit']) ? InputValidator::validateInteger($_GET['limit'], 1, 100) : 10;
            $offset = isset($_GET['offset']) ? InputValidator::validateInteger($_GET['offset'], 0) : 0;
            
            $stmt = $post->read();
            $posts = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = [
                    'id' => $row['id'],
                    'title' => InputValidator::sanitizeString($row['title']),
                    'slug' => $row['slug'],
                    'excerpt' => InputValidator::sanitizeString($row['excerpt']),
                    'featured_image' => $row['featured_image'],
                    'meta_title' => InputValidator::sanitizeString($row['meta_title']),
                    'meta_description' => InputValidator::sanitizeString($row['meta_description']),
                    'category_id' => $row['category_id'],
                    'category_name' => InputValidator::sanitizeString($row['category_name'] ?? ''),
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            
            echo json_encode(['records' => $posts]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON data']);
            break;
        }
        
        // Validate required fields
        $title = isset($data['title']) ? InputValidator::sanitizeString($data['title']) : '';
        $content = isset($data['content']) ? InputValidator::validateTextarea($data['content'], 50000, true) : '';
        
        if (empty($title) || empty($content)) {
            http_response_code(400);
            echo json_encode(['message' => 'Title and content are required']);
            break;
        }
        
        // Validate optional fields
        $slug = isset($data['slug']) ? InputValidator::validateSlug($data['slug']) : '';
        if (empty($slug)) {
            // Generate slug from title if not provided
            $slug = InputValidator::validateSlug(strtolower(str_replace(' ', '-', $title)));
        }
        
        $excerpt = isset($data['excerpt']) ? InputValidator::sanitizeString($data['excerpt']) : '';
        $featuredImage = isset($data['featured_image']) ? InputValidator::validateUrl($data['featured_image']) : '';
        $categoryId = isset($data['category_id']) ? InputValidator::validateInteger($data['category_id'], 1) : null;
        $status = isset($data['status']) ? InputValidator::sanitizeString($data['status']) : 'published';
        $metaTitle = isset($data['meta_title']) ? InputValidator::sanitizeString($data['meta_title']) : $title;
        $metaDescription = isset($data['meta_description']) ? InputValidator::sanitizeString($data['meta_description']) : '';
        
        // Validate status
        if (!in_array($status, ['draft', 'published', 'pending'])) {
            $status = 'published';
        }
        
        $post->title = $title;
        $post->slug = $slug;
        $post->content = $content;
        $post->excerpt = $excerpt;
        $post->featured_image = $featuredImage ?: '';
        $post->category_id = $categoryId;
        $post->status = $status;
        $post->meta_title = $metaTitle;
        $post->meta_description = $metaDescription;
        
        if ($post->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'Post created successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to create post']);
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON data']);
            break;
        }
        
        // Validate ID
        $postId = isset($data['id']) ? InputValidator::validateInteger($data['id'], 1) : false;
        if ($postId === false) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid post ID']);
            break;
        }
        
        // Validate required fields
        $title = isset($data['title']) ? InputValidator::sanitizeString($data['title']) : '';
        $content = isset($data['content']) ? InputValidator::validateTextarea($data['content'], 50000, true) : '';
        $slug = isset($data['slug']) ? InputValidator::validateSlug($data['slug']) : '';
        
        if (empty($title) || empty($content) || empty($slug)) {
            http_response_code(400);
            echo json_encode(['message' => 'Title, slug, and content are required']);
            break;
        }
        
        // Validate optional fields
        $excerpt = isset($data['excerpt']) ? InputValidator::sanitizeString($data['excerpt']) : '';
        $featuredImage = isset($data['featured_image']) ? InputValidator::validateUrl($data['featured_image']) : '';
        $categoryId = isset($data['category_id']) ? InputValidator::validateInteger($data['category_id'], 1) : null;
        $status = isset($data['status']) ? InputValidator::sanitizeString($data['status']) : 'published';
        $metaTitle = isset($data['meta_title']) ? InputValidator::sanitizeString($data['meta_title']) : $title;
        $metaDescription = isset($data['meta_description']) ? InputValidator::sanitizeString($data['meta_description']) : '';
        
        // Validate status
        if (!in_array($status, ['draft', 'published', 'pending'])) {
            $status = 'published';
        }
        
        $post->id = $postId;
        $post->title = $title;
        $post->slug = $slug;
        $post->content = $content;
        $post->excerpt = $excerpt;
        $post->featured_image = $featuredImage ?: '';
        $post->category_id = $categoryId;
        $post->status = $status;
        $post->meta_title = $metaTitle;
        $post->meta_description = $metaDescription;
        
        if ($post->update()) {
            http_response_code(200);
            echo json_encode(['message' => 'Post updated successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to update post']);
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON data']);
            break;
        }
        
        $postId = isset($data['id']) ? InputValidator::validateInteger($data['id'], 1) : false;
        if ($postId === false) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid post ID']);
            break;
        }
        
        $post->id = $postId;
        
        if ($post->delete()) {
            http_response_code(200);
            echo json_encode(['message' => 'Post deleted successfully']);
        } else {
            http_response_code(503);
            echo json_encode(['message' => 'Unable to delete post']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}
?>

