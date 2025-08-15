<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Use mock data for now to bypass database issues
include_once __DIR__ . '/../config/db_config_simple.php';

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        if (isset($_GET["id"])) {
            // Get single post by ID
            $posts = MockDatabase::getPosts();
            $post = null;
            foreach ($posts as $p) {
                if ($p["id"] == $_GET["id"]) {
                    $post = $p;
                    break;
                }
            }
        } else if (isset($_GET["slug"])) {
            // Get single post by slug
            $posts = MockDatabase::getPosts();
            $post = null;
            foreach ($posts as $p) {
                if ($p["slug"] == $_GET["slug"]) {
                    $post = $p;
                    break;
                }
            }
            if ($post) {
                echo json_encode($post);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Post not found."));
            }
        } else {
            // Get all posts
            $posts = MockDatabase::getPosts();
            echo json_encode(array("records" => $posts));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->title)) {
            // Simulate creating a new post
            $new_post = array(
                "id" => rand(100, 999),
                "title" => $data->title,
                "slug" => isset($data->slug) ? $data->slug : strtolower(str_replace(' ', '-', $data->title)),
                "content" => isset($data->content) ? $data->content : '',
                "excerpt" => isset($data->excerpt) ? $data->excerpt : '',
                "featured_image" => isset($data->featured_image) ? $data->featured_image : '',
                "category_id" => isset($data->category_id) ? $data->category_id : 1,
                "category_name" => "Tips Pengiriman",
                "status" => isset($data->status) ? $data->status : 'draft',
                "meta_title" => isset($data->meta_title) ? $data->meta_title : $data->title,
                "meta_description" => isset($data->meta_description) ? $data->meta_description : '',
                "views" => 0,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            );
            
            http_response_code(201);
            echo json_encode(array("message" => "Post was created.", "post" => $new_post));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create post. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id) && !empty($data->title)) {
            // Simulate updating a post
            http_response_code(200);
            echo json_encode(array("message" => "Post was updated."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update post. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id)) {
            // Simulate deleting a post
            http_response_code(200);
            echo json_encode(array("message" => "Post was deleted."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete post. Data is incomplete."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>

