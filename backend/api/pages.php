<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Page.php';

$database = new Database();
$db = $database->getConnection();

$page = new Page($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        if(!empty($_GET["slug"])) {
            // Get single page by slug
            $page->slug = $_GET["slug"];
            if($page->readOne()) {
                $page_arr = array(
                    "id" => $page->id,
                    "title" => $page->title,
                    "slug" => $page->slug,
                    "content" => $page->content,
                    "meta_title" => $page->meta_title,
                    "meta_description" => $page->meta_description,
                    "status" => $page->status,
                    "created_at" => $page->created_at,
                    "updated_at" => $page->updated_at
                );
                http_response_code(200);
                echo json_encode($page_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Page not found."));
            }
        } else {
            // Get all pages
            $stmt = $page->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $pages_arr = array();
                $pages_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $page_item = array(
                        "id" => $id,
                        "title" => $title,
                        "slug" => $slug,
                        "content" => substr($content, 0, 200) . "...", // Excerpt
                        "meta_title" => $meta_title,
                        "meta_description" => $meta_description,
                        "status" => $status,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($pages_arr["records"], $page_item);
                }
                
                http_response_code(200);
                echo json_encode($pages_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No pages found."));
            }
        }
        break;
        
    case 'POST':
        // Create page
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->title) && !empty($data->slug) && !empty($data->content)) {
            $page->title = $data->title;
            $page->slug = $data->slug;
            $page->content = $data->content;
            $page->meta_title = $data->meta_title ?? $data->title;
            $page->meta_description = $data->meta_description ?? '';
            $page->status = $data->status ?? 'published';
            
            if($page->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Page was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create page."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create page. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        // Update page
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->title) && !empty($data->slug) && !empty($data->content)) {
            $page->id = $data->id;
            $page->title = $data->title;
            $page->slug = $data->slug;
            $page->content = $data->content;
            $page->meta_title = $data->meta_title ?? $data->title;
            $page->meta_description = $data->meta_description ?? '';
            $page->status = $data->status ?? 'published';
            
            if($page->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Page was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update page."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update page. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        // Delete page
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $page->id = $data->id;
            
            if($page->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Page was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete page."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete page. Data is incomplete."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>

