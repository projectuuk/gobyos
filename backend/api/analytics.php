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
include_once '../models/Analytics.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$analytics = new Analytics($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'summary':
                    // Get analytics summary
                    $summary = $analytics->getSummary();
                    http_response_code(200);
                    echo json_encode($summary);
                    break;
                    
                case 'daily':
                    // Get daily visits for the last 30 days
                    $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
                    $daily_data = $analytics->getDailyVisits($days);
                    http_response_code(200);
                    echo json_encode($daily_data);
                    break;
                    
                case 'popular_pages':
                    // Get most popular pages
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                    $popular_pages = $analytics->getPopularPages($limit);
                    http_response_code(200);
                    echo json_encode($popular_pages);
                    break;
                    
                case 'referrers':
                    // Get top referrers
                    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                    $referrers = $analytics->getTopReferrers($limit);
                    http_response_code(200);
                    echo json_encode($referrers);
                    break;
                    
                case 'browsers':
                    // Get browser statistics
                    $browsers = $analytics->getBrowserStats();
                    http_response_code(200);
                    echo json_encode($browsers);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Invalid action."));
                    break;
            }
        } else {
            // Get all visits
            $stmt = $analytics->read();
            $num = $stmt->rowCount();
            
            if ($num > 0) {
                $visits_arr = array();
                $visits_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($visits_arr["records"], $row);
                }
                
                http_response_code(200);
                echo json_encode($visits_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No visits found."));
            }
        }
        break;
        
    case 'POST':
        // Track a new visit
        $data = json_decode(file_get_contents("php://input"));
        
        // Get visitor information
        $analytics->ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $analytics->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $analytics->page_url = $data->page_url ?? '';
        $analytics->page_title = $data->page_title ?? '';
        $analytics->referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $analytics->session_id = $data->session_id ?? session_id();
        
        // Parse user agent for browser and OS info
        $user_agent_info = $analytics->parseUserAgent($analytics->user_agent);
        $analytics->browser = $user_agent_info['browser'];
        $analytics->operating_system = $user_agent_info['os'];
        $analytics->device_type = $user_agent_info['device'];
        
        if ($analytics->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Visit tracked successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to track visit."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>

