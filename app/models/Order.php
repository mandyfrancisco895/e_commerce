<?php
// app/models/Order.php
// Debug: Track who's including this file
// ADD THIS CHECK TO PREVENT DUPLICATE CLASS DECLARATION
if (class_exists('Order')) {
    return; // Stop execution if class already exists
}

class Order {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ Transaction methods for OrderController
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollback();
    }

    public function createOrder($user_id, $total_amount, $status, $shipping_address, $payment_method) {
        try {
            // Generate a unique order number
            $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
            
            // Set payment status based on payment method FIRST
            $payment_status = ($payment_method === 'Cash on Delivery (COD)') ? 'pending' : 'paid';
            
            $sql = "INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, payment_method, payment_status) 
                    VALUES (:user_id, :order_number, :total_amount, :status, :shipping_address, :payment_method, :payment_status)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':order_number', $order_number, PDO::PARAM_STR);
            $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':shipping_address', $shipping_address, PDO::PARAM_STR);
            $stmt->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);
            $stmt->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Failed to create order: " . $errorInfo[2]);
            
        } catch (PDOException $e) {
            throw new Exception("Database error creating order: " . $e->getMessage());
        }
    }

public function addOrderItem($order_id, $product_id, $product_name, $product_price, $quantity, $size, $subtotal) {
    try {
        // First check stock availability
        $checkStock = $this->conn->prepare("SELECT stock FROM products WHERE id = :product_id");
        $checkStock->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $checkStock->execute();
        $stock = $checkStock->fetchColumn();

        if ($stock === false) {
            throw new Exception("Product not found: $product_name");
        }

        if ($stock < $quantity) {
            throw new Exception("Not enough stock for product: $product_name (Available: $stock, Requested: $quantity)");
        }

        // Insert order item
        $sql = "INSERT INTO order_items 
                   (order_id, product_id, product_name, product_price, quantity, size, subtotal) 
                VALUES 
                   (:order_id, :product_id, :product_name, :product_price, :quantity, :size, :subtotal)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_name', $product_name, PDO::PARAM_STR);
        $stmt->bindParam(':product_price', $product_price, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':size', $size, PDO::PARAM_STR);
        $stmt->bindParam(':subtotal', $subtotal, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Failed to add order item: " . $errorInfo[2]);
        }

        // Deduct stock after successful order item insertion
        $updateStock = $this->conn->prepare("UPDATE products SET stock = stock - :quantity WHERE id = :product_id");
        $updateStock->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $updateStock->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        if (!$updateStock->execute()) {
            $errorInfo = $updateStock->errorInfo();
            throw new Exception("Failed to update stock: " . $errorInfo[2]);
        }

        // ✅ NEW: Check if stock is now 0 or less and update status to inactive
        $newStock = $stock - $quantity;
        if ($newStock <= 0) {
            $updateStatus = $this->conn->prepare("UPDATE products SET status = 'inactive' WHERE id = :product_id");
            $updateStatus->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $updateStatus->execute();
        }

        return true;

    } catch (PDOException $e) {
        throw new Exception("Database error adding order item: " . $e->getMessage());
    }
}
    
    public function reduceProductStock($product_id, $quantity) {
        try {
            $sql = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id AND stock >= :quantity";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Failed to reduce product stock: " . $errorInfo[2]);
            }
            
            // Check if any rows were affected (stock was actually reduced)
            if ($stmt->rowCount() === 0) {
                throw new Exception("Insufficient stock for product ID: $product_id");
            }
            
            return true;
            
        } catch (PDOException $e) {
            throw new Exception("Database error reducing stock: " . $e->getMessage());
        }
    }

    public function getOrdersByUser($user_id) {
        try {
            $sql = "SELECT o.*, 
                           COUNT(oi.id) as item_count,
                           o.created_at as order_date
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE o.user_id = :user_id
                    GROUP BY o.id
                    ORDER BY o.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error fetching orders: " . $e->getMessage());
        }
    }

    public function cancelOrder($order_id, $user_id) {
        try {
            // First check if order belongs to user and can be cancelled
            $checkSql = "SELECT status FROM orders WHERE id = :order_id AND user_id = :user_id";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
            $order = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }
    
            // Check if order can be cancelled (only pending or processing)
            if (!in_array($order['status'], ['pending', 'processing'])) {
                return ['success' => false, 'message' => 'Order cannot be cancelled at this stage'];
            }
    
            // Start transaction for stock restoration
            $this->beginTransaction();
    
            // 1. Get all order items to restore stock
            $itemsSql = "SELECT product_id, quantity FROM order_items WHERE order_id = :order_id";
            $itemsStmt = $this->conn->prepare($itemsSql);
            $itemsStmt->execute([':order_id' => $order_id]);
            $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
            // 2. Restore stock for each product
            foreach ($orderItems as $item) {
                $restoreSql = "UPDATE products SET stock = stock + :quantity WHERE id = :product_id";
                $restoreStmt = $this->conn->prepare($restoreSql);
                $restoreStmt->execute([
                    ':quantity' => $item['quantity'],
                    ':product_id' => $item['product_id']
                ]);
    
                // Reactivate product if it was inactive
                $reactivateSql = "UPDATE products SET status = 'active' WHERE id = :product_id AND status = 'inactive'";
                $reactivateStmt = $this->conn->prepare($reactivateSql);
                $reactivateStmt->execute([':product_id' => $item['product_id']]);
            }
    
            // 3. Update order status to cancelled
            $updateSql = "UPDATE orders SET status = 'cancelled' WHERE id = :order_id";
            $updateStmt = $this->conn->prepare($updateSql);
            $success = $updateStmt->execute([':order_id' => $order_id]);
    
            if ($success) {
                $this->commit();
                return ['success' => true, 'message' => 'Order cancelled successfully'];
            } else {
                $this->rollback();
                return ['success' => false, 'message' => 'Failed to cancel order'];
            }
    
        } catch (Exception $e) {
            $this->rollback();
            error_log("Cancel order error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while cancelling order'];
        }
    }

    public function getOrderItems($order_id) {
        try {
            $sql = "SELECT oi.*, p.image as product_image
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = :order_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Database error fetching order items: " . $e->getMessage());
        }
    }

    
}






?>