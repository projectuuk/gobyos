<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $tracking_number;
    public $sender_name;
    public $sender_phone;
    public $sender_address;
    public $receiver_name;
    public $receiver_phone;
    public $receiver_address;
    public $service_type;
    public $item_description;
    public $weight;
    public $dimensions;
    public $estimated_cost;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all bookings
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single booking by tracking number
    public function readByTrackingNumber() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE tracking_number = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tracking_number);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->tracking_number = $row['tracking_number'];
            $this->sender_name = $row['sender_name'];
            $this->sender_phone = $row['sender_phone'];
            $this->sender_address = $row['sender_address'];
            $this->receiver_name = $row['receiver_name'];
            $this->receiver_phone = $row['receiver_phone'];
            $this->receiver_address = $row['receiver_address'];
            $this->service_type = $row['service_type'];
            $this->item_description = $row['item_description'];
            $this->weight = $row['weight'];
            $this->dimensions = $row['dimensions'];
            $this->estimated_cost = $row['estimated_cost'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }

    // Create booking
    public function create() {
        // Generate tracking number
        $this->tracking_number = $this->generateTrackingNumber();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  SET tracking_number=:tracking_number, sender_name=:sender_name, 
                      sender_phone=:sender_phone, sender_address=:sender_address,
                      receiver_name=:receiver_name, receiver_phone=:receiver_phone,
                      receiver_address=:receiver_address, service_type=:service_type,
                      item_description=:item_description, weight=:weight,
                      dimensions=:dimensions, estimated_cost=:estimated_cost,
                      status=:status, notes=:notes, created_at=NOW(), updated_at=NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->sender_name = htmlspecialchars(strip_tags($this->sender_name));
        $this->sender_phone = htmlspecialchars(strip_tags($this->sender_phone));
        $this->sender_address = htmlspecialchars(strip_tags($this->sender_address));
        $this->receiver_name = htmlspecialchars(strip_tags($this->receiver_name));
        $this->receiver_phone = htmlspecialchars(strip_tags($this->receiver_phone));
        $this->receiver_address = htmlspecialchars(strip_tags($this->receiver_address));
        $this->service_type = htmlspecialchars(strip_tags($this->service_type));
        $this->item_description = htmlspecialchars(strip_tags($this->item_description));
        $this->weight = htmlspecialchars(strip_tags($this->weight));
        $this->dimensions = htmlspecialchars(strip_tags($this->dimensions));
        $this->estimated_cost = htmlspecialchars(strip_tags($this->estimated_cost));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Bind values
        $stmt->bindParam(":tracking_number", $this->tracking_number);
        $stmt->bindParam(":sender_name", $this->sender_name);
        $stmt->bindParam(":sender_phone", $this->sender_phone);
        $stmt->bindParam(":sender_address", $this->sender_address);
        $stmt->bindParam(":receiver_name", $this->receiver_name);
        $stmt->bindParam(":receiver_phone", $this->receiver_phone);
        $stmt->bindParam(":receiver_address", $this->receiver_address);
        $stmt->bindParam(":service_type", $this->service_type);
        $stmt->bindParam(":item_description", $this->item_description);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":dimensions", $this->dimensions);
        $stmt->bindParam(":estimated_cost", $this->estimated_cost);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Update booking status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET status=:status, notes=:notes, updated_at=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Generate unique tracking number
    private function generateTrackingNumber() {
        $prefix = "CE"; // Calista Express
        $timestamp = date('ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return $prefix . $timestamp . $random;
    }

    // Delete booking
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>

