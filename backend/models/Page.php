<?php
class Page {
    private $conn;
    private $table_name = "pages";

    public $id;
    public $title;
    public $slug;
    public $content;
    public $meta_title;
    public $meta_description;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all pages
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'published' ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single page by slug
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = ? AND status = 'published' LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->slug);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->slug = $row['slug'];
            $this->content = $row['content'];
            $this->meta_title = $row['meta_title'];
            $this->meta_description = $row['meta_description'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }

    // Create page
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, slug=:slug, content=:content, 
                      meta_title=:meta_title, meta_description=:meta_description, 
                      status=:status, created_at=NOW(), updated_at=NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->content = htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'); // Sanitize HTML content to prevent SQL errors
        $this->meta_title = htmlspecialchars(strip_tags($this->meta_title));
        $this->meta_description = htmlspecialchars(strip_tags($this->meta_description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":meta_title", $this->meta_title);
        $stmt->bindParam(":meta_description", $this->meta_description);
        $stmt->bindParam(":status", $this->status);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Update page
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, slug=:slug, content=:content, 
                      meta_title=:meta_title, meta_description=:meta_description, 
                      status=:status, updated_at=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->content = htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'); // Sanitize HTML content to prevent SQL errors
        $this->meta_title = htmlspecialchars(strip_tags($this->meta_title));
        $this->meta_description = htmlspecialchars(strip_tags($this->meta_description));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":meta_title", $this->meta_title);
        $stmt->bindParam(":meta_description", $this->meta_description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete page
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

