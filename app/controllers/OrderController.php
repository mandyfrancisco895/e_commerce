<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';

class OrderController {
    private $orderModel;
    private $productModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->orderModel   = new Order($db);
        $this->productModel = new Product($db);
    }

    public function placeOrder() {
        session_start();
    
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Please login to place an order'
            ]);
            return;
        }
    
        $user_id          = $_SESSION['user_id'];
        $total_amount     = floatval($_POST['total_amount'] ?? 0);
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $payment_method   = trim($_POST['payment_method'] ?? '');
        $cart             = json_decode($_POST['cart'] ?? '[]', true);
    
        if ($total_amount <= 0 || empty($shipping_address) || empty($payment_method) || empty($cart)) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing or invalid order data'
            ]);
            return;
        }
    
        try {
            $this->orderModel->beginTransaction();
    
            $order_id = $this->orderModel->createOrder(
                $user_id,
                $total_amount,
                'pending',
                $shipping_address,
                $payment_method
            );
    
            if (!$order_id) {
                throw new Exception("Failed to create order");
            }
    
            $updatedProducts = [];
    
            foreach ($cart as $item) {
                $product_id   = intval($item['id'] ?? 0);
                $product_name = $item['name'] ?? '';
                $price        = floatval($item['price'] ?? 0);
                $quantity     = intval($item['quantity'] ?? 0);
                $size         = $item['size'] ?? null;
                $subtotal     = $price * $quantity;
    
                // ❌ REMOVE THIS LINE - total_amount is already calculated!
                // $total_amount += $subtotal;
    
                if ($product_id <= 0 || $quantity <= 0) {
                    throw new Exception("Invalid product data for: $product_name");
                }
    
                $success = $this->orderModel->addOrderItem(
                    $order_id,
                    $product_id,
                    $product_name,
                    $price,
                    $quantity,
                    $size,
                    $subtotal
                );
    
                if (!$success) {
                    throw new Exception("Failed to add order item: $product_name");
                }
    
                $updatedProducts[] = [
                    'product_id' => $product_id,
                    'quantity'   => $quantity
                ];
            }
    
            $this->orderModel->commit();
    
            echo json_encode([
                'success'  => true,
                'message'  => 'Order placed successfully! Stock updated.',
                'order_id' => $order_id,
                'updated_products' => $updatedProducts
            ]);
    
        } catch (Exception $e) {
            $this->orderModel->rollback();
    
            echo json_encode([
                'success' => false,
                'message' => 'Order failed: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelOrder() {
        session_start();
    
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Please login to cancel order'
            ]);
            return;
        }
    
        $user_id = $_SESSION['user_id'];
        
        // Handle both POST JSON data and form data
        $input = json_decode(file_get_contents('php://input'), true);
        $order_id = intval($input['order_id'] ?? $_POST['order_id'] ?? 0);
    
        if ($order_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid order ID'
            ]);
            return;
        }
    
        try {
            $result = $this->orderModel->cancelOrder($order_id, $user_id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error cancelling order: ' . $e->getMessage()
            ]);
        }
    }

    public function getUserOrders() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Please login to view orders'
            ]);
            return;
        }

        try {
            $orders = $this->orderModel->getOrdersByUser($_SESSION['user_id']);
            echo json_encode([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ]);
        }
    }

    public function getOrderDetails() {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Please login to view order details'
            ]);
            return;
        }

        $order_id = intval($_GET['order_id'] ?? 0);

        if ($order_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid order ID'
            ]);
            return;
        }

        try {
            $items = $this->orderModel->getOrderItems($order_id);
            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching order details: ' . $e->getMessage()
            ]);
        }
    }

    public function getRecentOrders() {
        session_start();
        
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not logged in'
                ]);
                return;
            }

            $user_id = $_SESSION['user_id'];

            // Fetch recent orders (last 30 days) with item counts
            $stmt = $this->db->prepare("
                SELECT 
                    o.*,
                    COUNT(oi.id) as item_count,
                    CASE 
                        WHEN o.updated_at IS NULL OR o.updated_at = '0000-00-00 00:00:00' 
                        THEN o.created_at 
                        ELSE o.updated_at 
                    END as last_update
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = :user_id 
                    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY o.id
                ORDER BY last_update DESC
                LIMIT 10
            ");
            
            $stmt->execute(['user_id' => $user_id]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format the orders for notifications
            $formattedOrders = [];
            foreach ($orders as $order) {
                // Generate order number if not exists
                if (empty($order['order_number'])) {
                    $order['order_number'] = 'ORD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
                }
                
                // Ensure numeric values are properly formatted
                $order['total_amount'] = number_format((float)$order['total_amount'], 2, '.', '');
                $order['item_count'] = (int)$order['item_count'];
                
                // Use last_update for the notification timestamp
                $order['updated_at'] = $order['last_update'];
                
                $formattedOrders[] = $order;
            }

            echo json_encode([
                'success' => true,
                'orders' => $formattedOrders,
                'count' => count($formattedOrders)
            ]);

        } catch (PDOException $e) {
            error_log("Error fetching recent orders: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Database error occurred'
            ]);
        } catch (Exception $e) {
            error_log("Error in getRecentOrders: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while fetching recent orders'
            ]);
        }
    }


    public function getPendingOrdersCount() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'count' => 0]);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        
        try {
            // Count orders that are pending or processing
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM orders 
                WHERE user_id = :user_id 
                AND status IN ('pending', 'processing')
            ");
            
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => (int)$result['count']
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting pending orders count: " . $e->getMessage());
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }
    
    public function getUnreadNotificationsCount() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'count' => 0]);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        
        try {
            // Count recent orders (last 7 days) as notifications
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM orders 
                WHERE user_id = :user_id 
                AND (
                    updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    OR (updated_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))
                )
            ");
            
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => (int)$result['count']
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting notifications count: " . $e->getMessage());
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }
    
    public function markNotificationsRead() {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            return;
        }
        
        // This is a placeholder - you can implement actual notification tracking later
        // For now, we'll just return success
        echo json_encode(['success' => true]);
    }

    

    public function getNotificationCount() {
        session_start();
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'count' => 0]);
                return;
            }

            $user_id = $_SESSION['user_id'];

            // Count notifications (orders updated in last 7 days)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as notification_count
                FROM orders 
                WHERE user_id = :user_id 
                    AND (
                        updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        OR (updated_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))
                    )
            ");
            
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'count' => (int)$result['notification_count']
            ]);

        } catch (Exception $e) {
            error_log("Error getting notification count: " . $e->getMessage());
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }


public function getCustomerOrders() {
    // This method is for admin use - don't require user session
    // Instead, get customer_id from GET parameter
    
    $customer_id = intval($_GET['customer_id'] ?? 0);
    
    if ($customer_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid customer ID'
        ]);
        return;
    }
    
    try {
        $stmt = $this->db->prepare("
            SELECT 
                o.id as order_id,
                o.order_number,
                o.created_at as order_date,
                o.total_amount,
                o.status,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :customer_id
            GROUP BY o.id, o.order_number, o.created_at, o.total_amount, o.status
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        
        $stmt->execute(['customer_id' => $customer_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the orders data
        $formatted_orders = [];
        foreach ($orders as $order) {
            // Generate order number if not exists (fallback)
            $order_number = $order['order_number'] ?: 'ORD-' . str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
            
            $formatted_orders[] = [
                'order_id' => '#' . $order_number,
                'date' => date('M j, Y', strtotime($order['order_date'])),
                'items' => (int)$order['item_count'] . ' item' . ((int)$order['item_count'] !== 1 ? 's' : ''),
                'total' => '₱' . number_format((float)$order['total_amount'], 2),
                'status' => ucfirst($order['status']),
                'status_class' => $this->getStatusClass($order['status'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'orders' => $formatted_orders
        ]);
        
    } catch (PDOException $e) {
        error_log("Error fetching customer orders: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
    }
}

private function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'completed':
        case 'delivered':
            return 'bg-success';
        case 'pending':
            return 'bg-warning';
        case 'processing':
            return 'bg-info';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

    

    

    

    public function getOrderSummary() {
        session_start();
    
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Please login to view order summary'
            ]);
            return;
        }
    
        $order_id = intval($_GET['order_id'] ?? 0);
    
        if ($order_id <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid order ID'
            ]);
            return;
        }
    
        try {
            // Get order with user information
            $stmt = $this->db->prepare("
                SELECT o.*, u.username as user_name, u.phone as user_phone, u.address as user_address
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = :order_id AND o.user_id = :user_id
            ");
            
            $stmt->execute([
                'order_id' => $order_id,
                'user_id' => $_SESSION['user_id']
            ]);
            
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($order) {
                echo json_encode([
                    'success' => true,
                    'order' => $order
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Order not found'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching order summary: ' . $e->getMessage()
            ]);
        }
    }
}

// Dispatcher
try {
    $database = new Database();
    $db = $database->getConnection();

    $controller = new OrderController($db);

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'placeOrder':
            $controller->placeOrder();
            break;
        case 'cancelOrder':
            $controller->cancelOrder();
            break;
        case 'getUserOrders':
            $controller->getUserOrders();
            break;
        case 'getOrderDetails':
            $controller->getOrderDetails();
            break;
        case 'getOrderSummary':
            $controller->getOrderSummary();
            break;
        case 'getRecentOrders':  // NEW CASE
            $controller->getRecentOrders();
            break;
        case 'getNotificationCount':  // NEW CASE
            $controller->getNotificationCount();
            break;
        case 'getCustomerOrders':  // NEW CASE for admin customer management
            $controller->getCustomerOrders();
            break;
         case 'getPendingOrdersCount':  // NEW
            $controller->getPendingOrdersCount();
            break;
        case 'getUnreadNotificationsCount':  // NEW
            $controller->getUnreadNotificationsCount();
            break;
        case 'markNotificationsRead':  // NEW
            $controller->markNotificationsRead();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action: ' . $action
            ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage()
    ]);
}

