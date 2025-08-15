<?php
class Post {
    private $conn;
    private $table_name = "posts";

    public $id;
    public $title;
    public $slug;
    public $content;
    public $excerpt;
    public $featured_image;
    public $meta_title;
    public $meta_description;
    public $category_id;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all posts
    public function read() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'published' 
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get single post by slug
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.slug = ? AND p.status = 'published' 
                  LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->slug);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->slug = $row['slug'];
            $this->content = $row['content'];
            $this->excerpt = $row['excerpt'];
            $this->featured_image = $row['featured_image'];
            $this->meta_title = $row['meta_title'];
            $this->meta_description = $row['meta_description'];
            $this->category_id = $row['category_id'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }

    // Create post
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, slug=:slug, content=:content, excerpt=:excerpt,
                      featured_image=:featured_image, meta_title=:meta_title, 
                      meta_description=:meta_description, category_id=:category_id,
                      status=:status, created_at=NOW(), updated_at=NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->content = htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'); // Sanitize HTML content to prevent SQL errors
        $this->excerpt = htmlspecialchars(strip_tags($this->excerpt));
        $this->featured_image = htmlspecialchars(strip_tags($this->featured_image));
        $this->meta_title = htmlspecialchars(strip_tags($this->meta_title));
        $this->meta_description = htmlspecialchars(strip_tags($this->meta_description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":excerpt", $this->excerpt);
        $stmt->bindParam(":featured_image", $this->featured_image);
        $stmt->bindParam(":meta_title", $this->meta_title);
        $stmt->bindParam(":meta_description", $this->meta_description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":status", $this->status);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Update post
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, slug=:slug, content=:content, excerpt=:excerpt,
                      featured_image=:featured_image, meta_title=:meta_title, 
                      meta_description=:meta_description, category_id=:category_id,
                      status=:status, updated_at=NOW()
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->slug = htmlspecialchars(strip_tags($this->slug));
        $this->content = htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8'); // Sanitize HTML content to prevent SQL errors
        $this->excerpt = htmlspecialchars(strip_tags($this->excerpt));
        $this->featured_image = htmlspecialchars(strip_tags($this->featured_image));
        $this->meta_title = htmlspecialchars(strip_tags($this->meta_title));
        $this->meta_description = htmlspecialchars(strip_tags($this->meta_description));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":excerpt", $this->excerpt);
        $stmt->bindParam(":featured_image", $this->featured_image);
        $stmt->bindParam(":meta_title", $this->meta_title);
        $stmt->bindParam(":meta_description", $this->meta_description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Delete post
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

