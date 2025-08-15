<?php
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

include_once '../config/database.php';
include_once '../models/SEO.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$seo = new SEO($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        if (isset($_GET['page_type']) && isset($_GET['page_id'])) {
            // Get SEO data for specific page
            $seo->page_type = $_GET['page_type'];
            $seo->page_id = $_GET['page_id'];
            $result = $seo->readByPage();
            
            if ($result) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "SEO data not found."));
            }
        } else {
            // Get all SEO data
            $stmt = $seo->read();
            $num = $stmt->rowCount();
            
            if ($num > 0) {
                $seo_arr = array();
                $seo_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $seo_item = array(
                        "id" => $id,
                        "page_type" => $page_type,
                        "page_id" => $page_id,
                        "meta_title" => $meta_title,
                        "meta_description" => $meta_description,
                        "meta_keywords" => $meta_keywords,
                        "og_title" => $og_title,
                        "og_description" => $og_description,
                        "og_image" => $og_image,
                        "canonical_url" => $canonical_url,
                        "robots" => $robots,
                        "schema_markup" => $schema_markup,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($seo_arr["records"], $seo_item);
                }
                
                http_response_code(200);
                echo json_encode($seo_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No SEO data found."));
            }
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->page_type) && !empty($data->page_id)) {
            $seo->page_type = $data->page_type;
            $seo->page_id = $data->page_id;
            $seo->meta_title = $data->meta_title ?? '';
            $seo->meta_description = $data->meta_description ?? '';
            $seo->meta_keywords = $data->meta_keywords ?? '';
            $seo->og_title = $data->og_title ?? '';
            $seo->og_description = $data->og_description ?? '';
            $seo->og_image = $data->og_image ?? '';
            $seo->canonical_url = $data->canonical_url ?? '';
            $seo->robots = $data->robots ?? 'index,follow';
            $seo->schema_markup = $data->schema_markup ?? '';
            
            if ($seo->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "SEO data was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create SEO data."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create SEO data. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id)) {
            $seo->id = $data->id;
            $seo->page_type = $data->page_type ?? '';
            $seo->page_id = $data->page_id ?? '';
            $seo->meta_title = $data->meta_title ?? '';
            $seo->meta_description = $data->meta_description ?? '';
            $seo->meta_keywords = $data->meta_keywords ?? '';
            $seo->og_title = $data->og_title ?? '';
            $seo->og_description = $data->og_description ?? '';
            $seo->og_image = $data->og_image ?? '';
            $seo->canonical_url = $data->canonical_url ?? '';
            $seo->robots = $data->robots ?? 'index,follow';
            $seo->schema_markup = $data->schema_markup ?? '';
            
            if ($seo->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "SEO data was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update SEO data."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update SEO data. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->id)) {
            $seo->id = $data->id;
            
            if ($seo->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "SEO data was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete SEO data."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete SEO data. Data is incomplete."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>

