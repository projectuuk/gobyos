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
        if (isset($_GET['id'])) {
            // Get single category
            $categories = MockDatabase::getCategories();
            $category = null;
            foreach ($categories as $c) {
                if ($c['id'] == $_GET['id']) {
                    $category = $c;
                    break;
                }
            }
            
            if ($category) {
                echo json_encode($category);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Category not found."));
            }
        } else {
            // Get all categories
            $categories = MockDatabase::getCategories();
            echo json_encode(array("records" => $categories));
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->name)) {
            // Simulate creating a new category
            $new_category = array(
                "id" => rand(100, 999),
                "name" => $data->name,
                "slug" => isset($data->slug) ? $data->slug : strtolower(str_replace(' ', '-', $data->name)),
                "description" => isset($data->description) ? $data->description : ''
            );
            
            http_response_code(201);
            echo json_encode(array("message" => "Category was created.", "category" => $new_category));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create category. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id) && !empty($data->name)) {
            // Simulate updating a category
            http_response_code(200);
            echo json_encode(array("message" => "Category was updated."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update category. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id)) {
            // Simulate deleting a category
            http_response_code(200);
            echo json_encode(array("message" => "Category was deleted."));
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete category. Data is incomplete."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>

