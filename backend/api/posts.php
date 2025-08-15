<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . "/../models/Post.php";

$database = new Database();
$db = $database->getConnection();

$post = new Post($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        if(!empty($_GET["slug"])) {
            // Get single post by slug
            $post->slug = $_GET["slug"];
            if($post->readOne()) {
                $post_arr = array(
                    "id" => $post->id,
                    "title" => $post->title,
                    "slug" => $post->slug,
                    "content" => $post->content,
                    "excerpt" => $post->excerpt,
                    "featured_image" => $post->featured_image,
                    "meta_title" => $post->meta_title,
                    "meta_description" => $post->meta_description,
                    "category_id" => $post->category_id,
                    "status" => $post->status,
                    "created_at" => $post->created_at,
                    "updated_at" => $post->updated_at
                );
                http_response_code(200);
                echo json_encode($post_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Post not found."));
            }
        } else {
            // Get all posts
            $stmt = $post->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $posts_arr = array();
                $posts_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $post_item = array(
                        "id" => $id,
                        "title" => $title,
                        "slug" => $slug,
                        "excerpt" => $excerpt,
                        "featured_image" => $featured_image,
                        "meta_title" => $meta_title,
                        "meta_description" => $meta_description,
                        "category_id" => $category_id,
                        "category_name" => $category_name,
                        "status" => $status,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($posts_arr["records"], $post_item);
                }
                
                http_response_code(200);
                echo json_encode($posts_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No posts found."));
            }
        }
        break;
        
    case 'POST':
        // Create post
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->title) && !empty($data->slug) && !empty($data->content)) {
            $post->title = $data->title;
            $post->slug = $data->slug;
            $post->content = $data->content;
            $post->excerpt = $data->excerpt ?? '';
            $post->featured_image = $data->featured_image ?? '';
            $post->meta_title = $data->meta_title ?? $data->title;
            $post->meta_description = $data->meta_description ?? '';
            $post->category_id = $data->category_id ?? null;
            $post->status = $data->status ?? 'published';
            
            if($post->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Post was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create post."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create post. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        // Update post
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->title) && !empty($data->slug) && !empty($data->content)) {
            $post->id = $data->id;
            $post->title = $data->title;
            $post->slug = $data->slug;
            $post->content = $data->content;
            $post->excerpt = $data->excerpt ?? '';
            $post->featured_image = $data->featured_image ?? '';
            $post->meta_title = $data->meta_title ?? $data->title;
            $post->meta_description = $data->meta_description ?? '';
            $post->category_id = $data->category_id ?? null;
            $post->status = $data->status ?? 'published';
            
            if($post->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Post was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update post."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update post. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        // Delete post
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $post->id = $data->id;
            
            if($post->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Post was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete post."));
            }
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

