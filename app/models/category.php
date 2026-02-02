<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create category
    public function create($name, $description, $status) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, description, status) 
                 VALUES (:name, :description, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":status", $status);
        
        return $stmt->execute();
    }

    // Read all categories
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Read single category
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update category
    public function update($id, $name, $description, $status) {
        $query = "UPDATE " . $this->table_name . " 
                 SET name = :name, description = :description, status = :status 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":status", $status);
        
        return $stmt->execute();
    }

    // Delete category
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Count only ACTIVE products in category
    public function countProducts($category_id) {
        $query = "SELECT COUNT(*) as count FROM products 
                  WHERE category_id = :category_id AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Count ALL products in category (both active and inactive) - optional
    public function countAllProducts($category_id) {
        $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
?>