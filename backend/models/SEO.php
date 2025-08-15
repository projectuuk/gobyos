<?php
class SEO {
    private $conn;
    private $table_name = "seo_meta";

    public $id;
    public $page_type;
    public $page_id;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;
    public $og_title;
    public $og_description;
    public $og_image;
    public $canonical_url;
    public $robots;
    public $schema_markup;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readByPage() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE page_type = ? AND page_id = ? 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->page_type);
        $stmt->bindParam(2, $this->page_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->page_type = $row['page_type'];
            $this->page_id = $row['page_id'];
            $this->meta_title = $row['meta_title'];
            $this->meta_description = $row['meta_description'];
            $this->meta_keywords = $row['meta_keywords'];
            $this->og_title = $row['og_title'];
            $this->og_description = $row['og_description'];
            $this->og_image = $row['og_image'];
            $this->canonical_url = $row['canonical_url'];
            $this->robots = $row['robots'];
            $this->schema_markup = $row['schema_markup'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        
        return false;
    }

    function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET page_type=:page_type, page_id=:page_id, meta_title=:meta_title, 
                      meta_description=:meta_description, meta_keywords=:meta_keywords,
                      og_title=:og_title, og_description=:og_description, og_image=:og_image,
                      canonical_url=:canonical_url, robots=:robots, schema_markup=:schema_markup";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->page_type = htmlspecialchars(strip_tags($this->page_type));
        $this->page_id = htmlspecialchars(strip_tags($this->page_id));
        $this->meta_title = htmlspecialchars(strip_tags($this->meta_title));
        $this->meta_description = htmlspecialchars(strip_tags($this->meta_description));
        $this->meta_keywords = htmlspecialchars(strip_tags($this->meta_keywords));
        $this->og_title = htmlspecialchars(strip_tags($this->og_title));
        $this->og_description = htmlspecialchars(strip_tags($this->og_description));
        $this->og_image = htmlspecialchars(strip_tags($this->og_image));
        $this->canonical_url = htmlspecialchars(strip_tags($this->canonical_url));
        $this->robots = htmlspecialchars(strip_tags($this->robots));
        $this->schema_markup = $this->schema_markup; // Keep JSON as is

        // Bind values
        $stmt->bindParam(":page_type", $this->page_type);
        $stmt->bindParam(":page_id", $this->page_id);
        $stmt->bindParam(":meta_title", $this->meta_title);
        $stmt->bindParam(":meta_description", $this->meta_description);
        $stmt->bindParam(":meta_keywords", $this->meta_keywords);
        $stmt->bindParam(":og_title", $this->og_title);
        $stmt->bindParam(":og_description", $this->og_description);
        $stmt->bindParam(":og_image", $this->og_image);
        $stmt->bindParam(":canonical_url", $this->canonical_url);
        $stmt->bindParam(":robots", $this->robots);
        $stmt->bindParam(":schema_markup", $this->schema_markup);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET page_type=:page_type, page_id=:page_id, meta_title=:meta_title,
                      meta_description=:meta_description, meta_keywords=:meta_keywords,
                      og_title=:og_title, og_description=:og_description, og_image=:og_image,
                      canonical_url=:canonical_url, robots=:robots, schema_markup=:schema_markup
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->page_type = htmlspecialchars(strip_tags($this->page_type));
        $this->page_id = htmlspecialchars(strip_tags($this->page_id));
        $this->meta_title = htmlspecialchars(strip_tags($this->meta_title));
        $this->meta_description = htmlspecialchars(strip_tags($this->meta_description));
        $this->meta_keywords = htmlspecialchars(strip_tags($this->meta_keywords));
        $this->og_title = htmlspecialchars(strip_tags($this->og_title));
        $this->og_description = htmlspecialchars(strip_tags($this->og_description));
        $this->og_image = htmlspecialchars(strip_tags($this->og_image));
        $this->canonical_url = htmlspecialchars(strip_tags($this->canonical_url));
        $this->robots = htmlspecialchars(strip_tags($this->robots));
        $this->schema_markup = $this->schema_markup; // Keep JSON as is
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":page_type", $this->page_type);
        $stmt->bindParam(":page_id", $this->page_id);
        $stmt->bindParam(":meta_title", $this->meta_title);
        $stmt->bindParam(":meta_description", $this->meta_description);
        $stmt->bindParam(":meta_keywords", $this->meta_keywords);
        $stmt->bindParam(":og_title", $this->og_title);
        $stmt->bindParam(":og_description", $this->og_description);
        $stmt->bindParam(":og_image", $this->og_image);
        $stmt->bindParam(":canonical_url", $this->canonical_url);
        $stmt->bindParam(":robots", $this->robots);
        $stmt->bindParam(":schema_markup", $this->schema_markup);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>

