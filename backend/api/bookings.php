<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        if(!empty($_GET["tracking_number"])) {
            // Get single booking by tracking number
            $booking->tracking_number = $_GET["tracking_number"];
            if($booking->readByTrackingNumber()) {
                $booking_arr = array(
                    "id" => $booking->id,
                    "tracking_number" => $booking->tracking_number,
                    "sender_name" => $booking->sender_name,
                    "sender_phone" => $booking->sender_phone,
                    "sender_address" => $booking->sender_address,
                    "receiver_name" => $booking->receiver_name,
                    "receiver_phone" => $booking->receiver_phone,
                    "receiver_address" => $booking->receiver_address,
                    "service_type" => $booking->service_type,
                    "item_description" => $booking->item_description,
                    "weight" => $booking->weight,
                    "dimensions" => $booking->dimensions,
                    "estimated_cost" => $booking->estimated_cost,
                    "status" => $booking->status,
                    "notes" => $booking->notes,
                    "created_at" => $booking->created_at,
                    "updated_at" => $booking->updated_at
                );
                http_response_code(200);
                echo json_encode($booking_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Booking not found."));
            }
        } else {
            // Get all bookings (for admin)
            $stmt = $booking->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $bookings_arr = array();
                $bookings_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $booking_item = array(
                        "id" => $id,
                        "tracking_number" => $tracking_number,
                        "sender_name" => $sender_name,
                        "sender_phone" => $sender_phone,
                        "sender_address" => $sender_address,
                        "receiver_name" => $receiver_name,
                        "receiver_phone" => $receiver_phone,
                        "receiver_address" => $receiver_address,
                        "service_type" => $service_type,
                        "item_description" => $item_description,
                        "weight" => $weight,
                        "dimensions" => $dimensions,
                        "estimated_cost" => $estimated_cost,
                        "status" => $status,
                        "notes" => $notes,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    
                    array_push($bookings_arr["records"], $booking_item);
                }
                
                http_response_code(200);
                echo json_encode($bookings_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No bookings found."));
            }
        }
        break;
        
    case 'POST':
        // Create booking
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->sender_name) && !empty($data->sender_phone) && 
           !empty($data->receiver_name) && !empty($data->receiver_phone) &&
           !empty($data->service_type)) {
            
            $booking->sender_name = $data->sender_name;
            $booking->sender_phone = $data->sender_phone;
            $booking->sender_address = $data->sender_address ?? '';
            $booking->receiver_name = $data->receiver_name;
            $booking->receiver_phone = $data->receiver_phone;
            $booking->receiver_address = $data->receiver_address ?? '';
            $booking->service_type = $data->service_type;
            $booking->item_description = $data->item_description ?? '';
            $booking->weight = $data->weight ?? '';
            $booking->dimensions = $data->dimensions ?? '';
            $booking->estimated_cost = $data->estimated_cost ?? 0;
            $booking->status = 'pending';
            $booking->notes = $data->notes ?? '';
            
            if($booking->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Booking was created.",
                    "tracking_number" => $booking->tracking_number
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create booking."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create booking. Data is incomplete."));
        }
        break;
        
    case 'PUT':
        // Update booking status
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id) && !empty($data->status)) {
            $booking->id = $data->id;
            $booking->status = $data->status;
            $booking->notes = $data->notes ?? '';
            
            if($booking->updateStatus()) {
                http_response_code(200);
                echo json_encode(array("message" => "Booking status was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update booking status."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update booking. Data is incomplete."));
        }
        break;
        
    case 'DELETE':
        // Delete booking
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $booking->id = $data->id;
            
            if($booking->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Booking was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete booking."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete booking. Data is incomplete."));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed."));
        break;
}
?>

