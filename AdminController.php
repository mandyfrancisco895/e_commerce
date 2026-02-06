<?php

class AdminController
{
    private $db;
    private $product;
    private $user;
    private $category;
    private $order;

    public function __construct($database)
    {
        $this->db = $database->getConnection();

        // Initialize models
        $this->product = new Product($this->db);
        $this->user = new User($this->db);
        $this->category = new Category($this->db);
        $this->order = new Order($this->db);
    }

    // ============================================================================
    // PRODUCT MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get all products with enhanced display information
     */
    public function getAllProducts()
    {
        try {
            $productsResult = $this->product->readAll();
            $products = [];

            if ($productsResult) {
                while ($row = $productsResult->fetch(PDO::FETCH_ASSOC)) {
                    // Enhanced status display logic
                    if ($row['category_status'] === 'inactive' && $row['status'] === 'inactive') {
                        $row['status_display'] = 'pending';
                        $row['status_reason'] = 'Category inactive';
                    } elseif ($row['stock'] <= 0) {
                        $row['status_display'] = 'inactive';
                        $row['status_reason'] = 'Out of stock';
                    } else {
                        $row['status_display'] = $row['status'];
                        $row['status_reason'] = '';
                    }

                    $products[] = $row;
                }
            }

            return $products;
        } catch (Exception $e) {
            error_log("AdminController::getAllProducts() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get only active products (for customer-facing operations)
     */
    public function getActiveProducts()
    {
        try {
            $productsResult = $this->product->readActiveProducts();
            $products = [];

            if ($productsResult) {
                while ($row = $productsResult->fetch(PDO::FETCH_ASSOC)) {
                    $products[] = $row;
                }
            }

            return $products;
        } catch (Exception $e) {
            error_log("AdminController::getActiveProducts() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new product with business rule validation
     */
    public function createProduct($data)
    {
        try {
            // Validate required fields
            $required = ['name', 'description', 'price', 'category_id', 'stock'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required"];
                }
            }

            // Validate price and stock are numeric
            if (!is_numeric($data['price']) || $data['price'] <= 0) {
                return ['success' => false, 'message' => 'Price must be a valid positive number'];
            }

            if (!is_numeric($data['stock']) || $data['stock'] < 0) {
                return ['success' => false, 'message' => 'Stock must be a valid non-negative number'];
            }

            // Check if category exists and is active
            $category = $this->category->readOne($data['category_id']);
            if (!$category) {
                return ['success' => false, 'message' => 'Invalid category selected'];
            }

            $result = $this->product->create(
                $data['name'],
                $data['description'],
                $data['price'],
                $data['image'] ?? null,
                $data['category_id'],
                $data['stock'],
                $data['status'] ?? 'active',
                $data['sizes'] ?? null
            );

            if ($result) {
                $this->setRefreshFlag();
                return ['success' => true, 'message' => 'Product created successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to create product'];
            }

        } catch (Exception $e) {
            error_log("AdminController::createProduct() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating product'];
        }
    }

    /**
     * Update an existing product
     */
    public function updateProduct($id, $data)
    {
        try {
            // Check if product exists
            $existingProduct = $this->product->getById($id);
            if (!$existingProduct) {
                return ['success' => false, 'message' => 'Product not found'];
            }

            // Validate required fields
            $required = ['name', 'description', 'price', 'category_id', 'stock'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required"];
                }
            }

            // Validate numeric fields
            if (!is_numeric($data['price']) || $data['price'] <= 0) {
                return ['success' => false, 'message' => 'Price must be a valid positive number'];
            }

            if (!is_numeric($data['stock']) || $data['stock'] < 0) {
                return ['success' => false, 'message' => 'Stock must be a valid non-negative number'];
            }

            $result = $this->product->update(
                $id,
                $data['name'],
                $data['description'],
                $data['price'],
                $data['image'] ?? $existingProduct['image'],
                $data['category_id'],
                $data['stock'],
                $data['status'] ?? $existingProduct['status'],
                $data['sizes'] ?? $existingProduct['sizes']
            );

            if ($result) {
                $this->setRefreshFlag();
                return ['success' => true, 'message' => 'Product updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update product'];
            }

        } catch (Exception $e) {
            error_log("AdminController::updateProduct() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating product'];
        }
    }

    /**
     * Delete a product
     */
    public function deleteProduct($id)
    {
        try {
            $product = $this->product->getById($id);
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found'];
            }

            $result = $this->product->delete($id);

            if ($result) {
                $this->setRefreshFlag();
                return ['success' => true, 'message' => 'Product deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete product'];
            }

        } catch (Exception $e) {
            error_log("AdminController::deleteProduct() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting product'];
        }
    }

    // ============================================================================
    // CATEGORY MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get all categories with product count
     */
    public function getAllCategories()
    {
        try {
            $categories = $this->category->readAll();

            // Add product count to each category
            foreach ($categories as &$category) {
                $category['product_count'] = $this->category->countProducts($category['id']);
                $category['total_product_count'] = $this->category->countAllProducts($category['id']);
            }

            return $categories;
        } catch (Exception $e) {
            error_log("AdminController::getAllCategories() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get only active categories
     */
    public function getActiveCategories()
    {
        try {
            $allCategories = $this->category->readAll();
            return array_filter($allCategories, function ($cat) {
                return $cat['status'] === 'active';
            });
        } catch (Exception $e) {
            error_log("AdminController::getActiveCategories() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new category
     */
    public function createCategory($data)
    {
        try {
            // Validate required fields
            if (empty($data['name'])) {
                return ['success' => false, 'message' => 'Category name is required'];
            }

            $result = $this->category->create(
                $data['name'],
                $data['description'] ?? '',
                $data['status'] ?? 'active'
            );

            if ($result) {
                return ['success' => true, 'message' => 'Category created successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to create category'];
            }

        } catch (Exception $e) {
            error_log("AdminController::createCategory() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while creating category'];
        }
    }

    /**
     * Update a category and handle product status changes
     */
    public function updateCategory($id, $data)
    {
        try {
            $existingCategory = $this->category->readOne($id);
            if (!$existingCategory) {
                return ['success' => false, 'message' => 'Category not found'];
            }

            if (empty($data['name'])) {
                return ['success' => false, 'message' => 'Category name is required'];
            }

            $result = $this->category->update(
                $id,
                $data['name'],
                $data['description'] ?? $existingCategory['description'],
                $data['status'] ?? $existingCategory['status']
            );

            if ($result) {
                // Handle product status changes when category status changes
                $this->handleCategoryStatusChange($id, $existingCategory['status'], $data['status']);
                $this->setRefreshFlag();
                return ['success' => true, 'message' => 'Category updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update category'];
            }

        } catch (Exception $e) {
            error_log("AdminController::updateCategory() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating category'];
        }
    }

    /**
     * Delete a category (only if no products are associated)
     */
    public function deleteCategory($id)
    {
        try {
            $category = $this->category->readOne($id);
            if (!$category) {
                return ['success' => false, 'message' => 'Category not found'];
            }

            // Check if category has products
            $productCount = $this->category->countAllProducts($id);
            if ($productCount > 0) {
                return ['success' => false, 'message' => 'Cannot delete category with existing products'];
            }

            $result = $this->category->delete($id);

            if ($result) {
                return ['success' => true, 'message' => 'Category deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete category'];
            }

        } catch (Exception $e) {
            error_log("AdminController::deleteCategory() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting category'];
        }
    }

    // ============================================================================
    // USER MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get all users with their statistics
     */
    public function getAllCustomers()
    {
        try {
            $sql = "SELECT u.*, 
                        COUNT(o.id) as order_count,
                        COALESCE(SUM(o.total_amount), 0.00) as total_spent
                    FROM users u
                    LEFT JOIN orders o ON u.id = o.user_id
                    WHERE u.role = 'user'  -- This line filters only customers
                    GROUP BY u.id
                    ORDER BY u.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Get all customers error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user status
     */
    public function updateUserStatus($id, $status)
    {
        try {
            if (!in_array($status, ['active', 'inactive', 'suspended'])) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $result = $this->user->updateUserStatus($id, $status);

            if ($result) {
                return ['success' => true, 'message' => 'User status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user status'];
            }

        } catch (Exception $e) {
            error_log("AdminController::updateUserStatus() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating user status'];
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole($id, $role)
    {
        try {
            if (!in_array($role, ['user', 'admin', 'moderator'])) {
                return ['success' => false, 'message' => 'Invalid role'];
            }

            $result = $this->user->updateUserRole($id, $role);

            if ($result) {
                return ['success' => true, 'message' => 'User role updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update user role'];
            }

        } catch (Exception $e) {
            error_log("AdminController::updateUserRole() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating user role'];
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        try {
            $user = $this->user->getUserById($id);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }

            // Prevent deletion of admin users (optional security measure)
            if ($user['role'] === 'admin') {
                return ['success' => false, 'message' => 'Cannot delete admin users'];
            }

            $result = $this->user->deleteUser($id);

            if ($result) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete user'];
            }

        } catch (Exception $e) {
            error_log("AdminController::deleteUser() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting user'];
        }
    }

    // ============================================================================
    // ORDER MANAGEMENT METHODS
    // ============================================================================

    /**
     * Get all orders with user information including profile pictures
     */
    public function getAllOrders()
    {
        try {
            $sql = "SELECT o.*, 
                        u.username, 
                        u.email, 
                        u.phone,
                        u.address,
                        u.profile_pic,
                        COUNT(oi.id) as item_count
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    GROUP BY o.id, o.order_number, o.user_id, o.total_amount, o.status, o.created_at, u.username, u.email, u.phone, u.address, u.profile_pic
                    ORDER BY o.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                if (empty($order['username'])) {
                    $order['username'] = 'Deleted User';
                    $order['email'] = 'N/A';
                }

                if (!empty($order['profile_pic'])) {
                    $filename = basename($order['profile_pic']);
                    $serverPath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . $filename;

                    if (file_exists($serverPath)) {
                        $order['profile_pic_url'] = '/E-COMMERCE/public/uploads/' . $filename;
                    } else {
                        $order['profile_pic_url'] = null;
                    }
                } else {
                    $order['profile_pic_url'] = null;
                }

                $order['item_count'] = max(1, (int) $order['item_count']);
            }

            return $orders;

        } catch (Exception $e) {
            error_log("AdminController::getAllOrders() - " . $e->getMessage());
            return [];
        }
    }



    /* Get order details by ID with user information */
    public function getOrderById($orderId)
    {
        try {
            $sql = "SELECT o.*, u.username, u.email, u.profile_pic, u.phone, u.address
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    WHERE o.id = :order_id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AdminController::getOrderById() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order items by order ID
     */
    public function getOrderItems($orderId)
    {
        try {
            $sql = "SELECT oi.*, p.name as product_name, p.image as product_image, p.price as product_price
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = :order_id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AdminController::getOrderItems() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order status
     */
    // Add this method to your AdminController.php class

    /**
     * Update order status
     */
    /**
     * Update order status and automatically set payment status to completed when delivered
     */
    public function updateOrderStatus($orderId, $status)
    {
        try {
            // Validate status based on your system
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status provided'];
            }

            // Check if order exists
            $checkSql = "SELECT id, status as current_status, payment_status FROM orders WHERE id = :order_id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $checkStmt->execute();

            $order = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            // Check if status is actually changing
            if ($order['current_status'] === $status) {
                return ['success' => true, 'message' => 'Order status is already ' . ucfirst($status)];
            }

            // Prepare the update query
            if ($status === 'delivered') {
                $sql = "UPDATE orders 
                    SET status = :status, 
                        payment_status = 'completed', 
                        updated_at = NOW() 
                    WHERE id = :order_id";
            } else {
                $sql = "UPDATE orders 
                    SET status = :status, 
                        updated_at = NOW() 
                    WHERE id = :order_id";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

            if ($stmt->execute()) {

                // ============================================================
                // NEW: TRIGGER EMAIL NOTIFICATION
                // ============================================================
                $orderData = $this->getOrderById($orderId);
                if ($orderData && !empty($orderData['email'])) {
                    $this->sendOrderStatusEmail($orderData['email'], $orderData['order_number'], $status);
                }
                // ============================================================

                // Log the status change for audit trail
                $logMessage = "Order #{$orderId} status changed from {$order['current_status']} to {$status}";
                if ($status === 'delivered') {
                    $logMessage .= " and payment status set to 'completed'";
                }
                $logMessage .= " by admin user ID: " . ($_SESSION['user_id'] ?? 'unknown');
                error_log($logMessage);

                // Prepare success message
                $message = 'Order status updated successfully to ' . ucfirst($status);
                if ($status === 'delivered') {
                    $message .= ' and payment marked as completed';
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'new_status' => $status,
                    'payment_status' => $status === 'delivered' ? 'completed' : $order['payment_status'],
                    'order_id' => $orderId
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to update order status in database'];
            }

        } catch (PDOException $e) {
            error_log("AdminController::updateOrderStatus() PDO Error - " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred while updating order status'];
        } catch (Exception $e) {
            error_log("AdminController::updateOrderStatus() Error - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating order status'];
        }
    }


    /**
     * Delete an order
     */
    public function deleteOrder($id)
    {
        try {
            $this->db->beginTransaction();

            // First delete order items
            $deleteItemsSql = "DELETE FROM order_items WHERE order_id = :order_id";
            $deleteItemsStmt = $this->db->prepare($deleteItemsSql);
            $deleteItemsStmt->bindParam(':order_id', $id);
            $deleteItemsStmt->execute();

            // Then delete the order
            $deleteOrderSql = "DELETE FROM orders WHERE id = :id";
            $deleteOrderStmt = $this->db->prepare($deleteOrderSql);
            $deleteOrderStmt->bindParam(':id', $id);
            $result = $deleteOrderStmt->execute();

            if ($result) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Order deleted successfully'];
            } else {
                $this->db->rollback();
                return ['success' => false, 'message' => 'Failed to delete order'];
            }

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("AdminController::deleteOrder() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting order'];
        }
    }


    // ============================================================================
    // ADMIN PROFILE UPDATE
    // ============================================================================

    /**
     * Get current user profile information
     */
    public function getCurrentUserProfile($userId)
    {
        try {
            return $this->user->getUserById($userId);
        } catch (Exception $e) {
            error_log("AdminController::getCurrentUserProfile() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update current user profile
     */
    public function updateUserProfile($userId, $data)
    {
        try {
            // Validate required fields
            if (empty($data['username']) || empty($data['email'])) {
                return ['success' => false, 'message' => 'Username and email are required'];
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }

            // Check if username already exists (excluding current user)
            $existingUser = $this->user->getUserByUsername($data['username']);
            if ($existingUser && $existingUser['id'] != $userId) {
                return ['success' => false, 'message' => 'Username already exists'];
            }

            // Check if email already exists (excluding current user)
            $existingEmailUser = $this->user->getUserByEmail($data['email']);
            if ($existingEmailUser && $existingEmailUser['id'] != $userId) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Update profile - using the new method signature
            $result = $this->user->updateUserProfile(
                $userId,
                $data['username'],
                $data['email'],
                $data['first_name'] ?? '',
                $data['last_name'] ?? '',
                $data['phone'] ?? ''
            );

            if ($result) {
                // Update session data
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['username'] = $data['username'];
                $_SESSION['email'] = $data['email'];

                return ['success' => true, 'message' => 'Profile updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update profile'];
            }

        } catch (Exception $e) {
            error_log("AdminController::updateUserProfile() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating profile'];
        }
    }

    /**
     * Change user password
     */
    public function changeUserPassword($userId, $currentPassword, $newPassword, $confirmPassword)
    {
        try {
            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return ['success' => false, 'message' => 'All password fields are required'];
            }

            if ($newPassword !== $confirmPassword) {
                return ['success' => false, 'message' => 'New passwords do not match'];
            }

            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'New password must be at least 6 characters long'];
            }

            // Use the new changePassword method from User model
            $result = $this->user->changePassword($userId, $currentPassword, $newPassword);

            if ($result === true) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                // Handle specific error cases
                if ($result === "user_not_found") {
                    return ['success' => false, 'message' => 'User not found'];
                } elseif ($result === "incorrect_current_password") {
                    return ['success' => false, 'message' => 'Current password is incorrect'];
                } else {
                    return ['success' => false, 'message' => 'Failed to change password'];
                }
            }

        } catch (Exception $e) {
            error_log("AdminController::changeUserPassword() - " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password'];
        }
    }

    // ============================================================================
    // DASHBOARD & STATISTICS METHODS
    // ============================================================================

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $stats = [];

            // Product statistics
            $stats['total_products'] = $this->getProductCount();
            $stats['active_products'] = $this->getActiveProductCount();
            $stats['low_stock_products'] = $this->getLowStockProductCount();

            // Category statistics
            $stats['total_categories'] = $this->getCategoryCount();
            $stats['active_categories'] = $this->getActiveCategoryCount();

            // User statistics
            $stats['total_users'] = $this->getUserCount();
            $stats['active_users'] = $this->getActiveUserCount();

            // Order statistics
            $stats['total_orders'] = $this->getOrderCount();
            $stats['pending_orders'] = $this->getPendingOrderCount();
            $stats['total_revenue'] = $this->getTotalRevenue();

            return $stats;
        } catch (Exception $e) {
            error_log("AdminController::getDashboardStats() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order statistics for the order management section
     */
    public function getOrderStats()
    {
        try {
            $stats = [];

            // Get counts for each order status
            $sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Set default values for all possible statuses
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            foreach ($validStatuses as $status) {
                $stats[$status . '_orders'] = $statusCounts[$status] ?? 0;
            }

            // Additional statistics
            $stats['total_orders'] = array_sum($statusCounts);

            // Total revenue (excluding cancelled orders)
            $revenueSql = "SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'";
            $revenueStmt = $this->db->prepare($revenueSql);
            $revenueStmt->execute();
            $stats['total_revenue'] = $revenueStmt->fetchColumn();

            return $stats;

        } catch (Exception $e) {
            error_log("AdminController::getOrderStats() - " . $e->getMessage());
            return [
                'pending_orders' => 0,
                'confirmed_orders' => 0,
                'processing_orders' => 0,
                'shipped_orders' => 0,
                'delivered_orders' => 0,
                'cancelled_orders' => 0,
                'total_orders' => 0,
                'total_revenue' => 0
            ];
        }
    }

    /**
     * Get recent orders for dashboard
     */
    public function getRecentOrders($limit = 5)
    {
        try {
            $sql = "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at,
                           u.username, u.profile_pic
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    ORDER BY o.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AdminController::getRecentOrders() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = 10)
    {
        try {
            $sql = "SELECT p.*, c.name as category_name
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.stock <= :threshold AND p.status = 'active'
                    ORDER BY p.stock ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("AdminController::getLowStockProducts() - " . $e->getMessage());
            return [];
        }
    }

    // ============================================================================
    // UTILITY & HELPER METHODS
    // ============================================================================

    /**
     * Handle category status changes and update related products
     */
    private function handleCategoryStatusChange($categoryId, $oldStatus, $newStatus)
    {
        try {
            if ($oldStatus === $newStatus) {
                return; // No change needed
            }

            $products = $this->product->getProductsByCategory($categoryId);

            foreach ($products as $product) {
                if ($newStatus === 'inactive') {
                    // Category became inactive: set products to pending
                    if ($product['status'] === 'active') {
                        $this->product->updateStatus($product['id'], 'pending');
                    }
                } elseif ($newStatus === 'active' && $oldStatus === 'inactive') {
                    // Category became active: restore products that were pending due to category
                    if ($product['status'] === 'pending' && $product['stock'] > 0) {
                        $this->product->updateStatus($product['id'], 'active');
                    }
                }
            }
        } catch (Exception $e) {
            error_log("AdminController::handleCategoryStatusChange() - " . $e->getMessage());
        }
    }

    /**
     * Set refresh flag for shop stock updates
     */
    private function setRefreshFlag()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['refresh_shop_stock'] = true;
    }

    // Statistics helper methods
    private function getProductCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getActiveProductCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE status = 'active'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getLowStockProductCount($threshold = 10)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE stock <= :threshold AND status = 'active'");
        $stmt->bindParam(':threshold', $threshold);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getCategoryCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getActiveCategoryCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE status = 'active'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getUserCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role != 'admin'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getActiveUserCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE status = 'active' AND role != 'admin'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getOrderCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getPendingOrderCount()
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    private function getTotalRevenue()
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Validate admin permissions
     */
    public function validateAdminAccess()
    {
        // Allow both Admin and Staff
        $allowed_roles = ['admin', 'staff'];

        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
            header("Location: ../../views/admin/login.php");
            exit;
        }
    }

    /**
     * Search orders by various criteria
     */
    public function searchOrders($searchTerm, $status = '', $dateFrom = '', $dateTo = '')
    {
        try {
            $sql = "SELECT o.*, u.username, u.email, u.profile_pic,
                           COUNT(oi.id) as item_count
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    WHERE 1=1";

            $params = [];

            // Add search term filter
            if (!empty($searchTerm)) {
                $sql .= " AND (o.order_number LIKE :search 
                             OR u.username LIKE :search 
                             OR u.email LIKE :search)";
                $params[':search'] = '%' . $searchTerm . '%';
            }

            // Add status filter
            if (!empty($status)) {
                $sql .= " AND o.status = :status";
                $params[':status'] = $status;
            }

            // Add date range filter
            if (!empty($dateFrom)) {
                $sql .= " AND DATE(o.created_at) >= :date_from";
                $params[':date_from'] = $dateFrom;
            }

            if (!empty($dateTo)) {
                $sql .= " AND DATE(o.created_at) <= :date_to";
                $params[':date_to'] = $dateTo;
            }

            $sql .= " GROUP BY o.id, o.order_number, o.user_id, o.total_amount, o.status, o.created_at, u.username, u.email, u.profile_pic
                     ORDER BY o.created_at DESC";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("AdminController::searchOrders() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export orders data to CSV
     */
    public function exportOrdersToCSV($filters = [])
    {
        try {
            $orders = empty($filters) ? $this->getAllOrders() : $this->searchOrders(
                $filters['search'] ?? '',
                $filters['status'] ?? '',
                $filters['date_from'] ?? '',
                $filters['date_to'] ?? ''
            );

            $csvData = [];
            $csvData[] = ['Order ID', 'Order Number', 'Customer', 'Email', 'Items', 'Total Amount', 'Status', 'Date Created'];

            foreach ($orders as $order) {
                $csvData[] = [
                    $order['id'],
                    $order['order_number'],
                    $order['username'],
                    $order['email'],
                    $order['item_count'],
                    $order['total_amount'],
                    $order['status'],
                    $order['created_at']
                ];
            }

            return $csvData;

        } catch (Exception $e) {
            error_log("AdminController::exportOrdersToCSV() - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get monthly sales data for charts
     */
    public function getMonthlySalesData($months = 12)
    {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as order_count,
                        SUM(total_amount) as revenue
                    FROM orders 
                    WHERE status != 'cancelled' 
                        AND created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':months', $months, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("AdminController::getMonthlySalesData() - " . $e->getMessage());
            return [];
        }
    }


    /**
     * Get customer statistics
     */
    public function getCustomerStats()
    {
        try {
            $stats = [];

            // New customers this month
            $sql = "SELECT COUNT(*) FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['new_customers_this_month'] = $stmt->fetchColumn();

            // Total customers with orders
            $sql = "SELECT COUNT(DISTINCT user_id) FROM orders";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['customers_with_orders'] = $stmt->fetchColumn();

            // Average order value
            $sql = "SELECT AVG(total_amount) FROM orders WHERE status != 'cancelled'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['average_order_value'] = $stmt->fetchColumn() ?: 0;

            return $stats;

        } catch (Exception $e) {
            error_log("AdminController::getCustomerStats() - " . $e->getMessage());
            return [
                'new_customers_this_month' => 0,
                'customers_with_orders' => 0,
                'average_order_value' => 0
            ];
        }
    }


    /**
     * Get top performing products (with sales, revenue, category)
     */
    public function getTopPerformingProducts($limit = 5)
    {
        $topProducts = $this->getTopPerformingProductsSimple($limit);

        // Add growth + rank
        foreach ($topProducts as &$product) {
            $product['growth'] = $this->getSimpleProductGrowth($product['id']);
            $product['rank'] = isset($product['total_sold']) && $product['total_sold'] > 0
                ? (int) $product['total_sold'] : 0;
        }

        // Sort by rank (total_sold)
        usort($topProducts, function ($a, $b) {
            return $b['rank'] - $a['rank'];
        });

        // Apply numeric rank 1,2,3,...
        $rank = 1;
        foreach ($topProducts as &$product) {
            $product['rank'] = $rank++;
        }

        return $topProducts;
    }

    /**
     * Simple query to get top selling products (no growth calc)
     */
    private function getTopPerformingProductsSimple($limit)
    {
        try {
            $sql = "SELECT 
                    p.id,
                    p.name,
                    p.image,
                    p.price,
                    p.stock,
                    c.name AS category_name,
                    COALESCE(SUM(oi.quantity), 0) AS total_sold,
                    COALESCE(SUM(oi.subtotal), 0) AS total_revenue
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN order_items oi ON oi.product_id = p.id
                LEFT JOIN orders o ON o.id = oi.order_id
                WHERE p.status = 'active'
                GROUP BY p.id, p.name, p.image, p.price, p.stock, c.name
                ORDER BY total_sold DESC
                LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching top products: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate growth % (current 30 days vs previous 30 days)
     */
    private function getSimpleProductGrowth($productId)
    {
        $sql = "SELECT 
                SUM(CASE WHEN o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN oi.quantity ELSE 0 END) AS current_sales,
                SUM(CASE WHEN o.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                          AND o.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) THEN oi.quantity ELSE 0 END) AS previous_sales
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE oi.product_id = :product_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $current = (int) ($result['current_sales'] ?? 0);
            $previous = (int) ($result['previous_sales'] ?? 0);

            if ($previous > 0) {
                return round((($current - $previous) / $previous) * 100, 1);
            } else {
                return $current > 0 ? 100 : 0; // 100% if it’s a new product with sales
            }
        } catch (PDOException $e) {
            error_log("Error calculating growth for product $productId: " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Private helper to send SMTP emails using PHPMailer
     */
    private function sendOrderStatusEmail($recipientEmail, $orderNumber, $status)
    {
        // Correct paths based on your folder structure
        require_once __DIR__ . '/../../libraries/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../../libraries/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../libraries/phpmailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Change if not using Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'empirebsit2025@gmail.com';
            $mail->Password = 'mqvg swfp cfbu vhze'; // 16-character Google App Password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@empire.com', 'EMPIRE E-COMMERCE');
            $mail->addAddress($recipientEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Update on your Order #$orderNumber";

            // Simple HTML Template
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px;'>
                <h2 style='color: #333; text-align: center;'>Order Status Update</h2>
                <p>Hello,</p>
                <p>We wanted to let you know that your order <strong>#$orderNumber</strong> has been updated.</p>
                <div style='background: #f9f9f9; padding: 15px; text-align: center; border-radius: 5px;'>
                    <span style='font-size: 18px; color: #d32f2f; font-weight: bold;'>" . strtoupper($status) . "</span>
                </div>
                <p>You can view your order details by logging into your account.</p>
                <hr style='border: 0; border-top: 1px solid #eee;' />
                <p style='font-size: 12px; color: #888;'>Thank you for choosing EMPIRE E-COMMERCE.</p>
            </div>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Order Notification Mail Error: " . $mail->ErrorInfo);
            return false;
        }
    }





}