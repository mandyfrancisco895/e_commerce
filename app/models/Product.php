<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/dbcon.php';


class Product {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Get all products - include category status
    public function readAll() {
        $sql = "SELECT p.*, c.name AS category_name, c.status AS category_status
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // ✅ Get only active products that belong to active categories
    public function readActiveProducts() {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' 
                AND c.status = 'active'
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // ✅ Get product by ID - include category status
    public function getById($id) {
        $sql = "SELECT p.*, c.name AS category_name, c.status AS category_status
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $description, $price, $image, $category_id, $stock, $status, $sizes = null) {
        // Check category status
        $category_check = "SELECT status FROM categories WHERE id = :category_id";
        $stmt_check = $this->conn->prepare($category_check);
        $stmt_check->bindParam(":category_id", $category_id);
        $stmt_check->execute();
        $category = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // ✅ Stock rule
        if ($stock <= 0) {
            $status = 'inactive';
        }

        // ✅ Category override (highest priority)
        if ($category && $category['status'] === 'inactive') {
            $status = 'pending';
        }

        // Handle null sizes
        if ($sizes === null) {
            $sizes = 'S, M, L, XL'; // Default sizes
        }

        $sql = "INSERT INTO products (name, description, price, image, category_id, stock, sizes, status) 
                VALUES (:name, :description, :price, :image, :category_id, :stock, :sizes, :status)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":image", $image);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":stock", $stock);
        $stmt->bindParam(":sizes", $sizes);
        $stmt->bindParam(":status", $status);

        return $stmt->execute();
    }

    public function update($id, $name, $description, $price, $image, $category_id, $stock, $status, $sizes) {
        try {
            // Check category status
            $category_check = "SELECT status FROM categories WHERE id = :category_id";
            $stmt_check = $this->conn->prepare($category_check);
            $stmt_check->bindParam(":category_id", $category_id);
            $stmt_check->execute();
            $category = $stmt_check->fetch(PDO::FETCH_ASSOC);

            // ✅ Stock rule
            if ($stock <= 0) {
                $status = 'inactive';
            }

            // ✅ Category override (highest priority)
            if ($category && $category['status'] === 'inactive') {
                $status = 'pending';
            }

            $sql = "UPDATE products 
                    SET name = :name,
                        description = :description,
                        price = :price,
                        image = :image,
                        category_id = :category_id,
                        stock = :stock,
                        status = :status,
                        sizes = :sizes
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':sizes', $sizes);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }

    // ✅ Update product status only (for bulk updates when category status changes)
    public function updateStatus($id, $status) {
        $query = "UPDATE products SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);
        return $stmt->execute();
    }

    // ✅ Get products by category ID
    public function getProductsByCategory($category_id) {
        $sql = "SELECT * FROM products WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Delete product
    public function delete($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // ✅ Get all categories
    public function getCategories() {
        $sql = "SELECT * FROM categories";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // In your Product model (Product.php)
    public function updateStock(int $productId, int $newStock): bool {
        try {
            $stmt = $this->conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
            return $stmt->execute([$newStock, $productId]);
        } catch (Exception $e) {
            error_log("updateStock error: " . $e->getMessage());
            return false;
        }
    }
    
    public function logStockMovement($product_id, $movement_type, $quantity, $reason, $notes, $user_id) {
        $stmt = $this->conn->prepare("INSERT INTO stock_movements 
            (product_id, movement_type, quantity, reason, notes, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$product_id, $movement_type, $quantity, $reason, $notes, $user_id]);
    }
    
    
  

   // ✅ Decrease stock - REMOVE size-based handling
   public function decreaseStock($product_id, $quantity) {
    try {
        // Get current product
        $product = $this->getById($product_id);
        if (!$product) {
            return false;
        }

        $currentStock = $product['stock'];
        $newStock = $currentStock - $quantity;
        
        if ($newStock < 0) {
            throw new Exception("Insufficient stock");
        }
        
        // FIX: Make sure this query only executes ONCE
        $sql = "UPDATE products SET stock = :stock WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':stock', $newStock, PDO::PARAM_INT);
        $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
        
        return $stmt->execute();
        
    } catch (Exception $e) {
        error_log("decreaseStock error: " . $e->getMessage());
        return false;
    }
}
}
?>
