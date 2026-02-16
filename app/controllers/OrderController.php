<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/PayPalPayment.php';
// AdminController NOT required here — its constructor loads User/Category/Order
// models which are not available in this file and cause a fatal error on every
// placeOrder request. The receipt email is handled by sendOrderReceiptEmail()
// defined as a private method on this class below.

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
        
        // PayPal specific fields
        $paypal_order_id  = trim($_POST['paypal_order_id'] ?? '');
        $payment_status   = trim($_POST['payment_status'] ?? 'pending');
    
        // For PayPal payments, store cart data in session for recovery after payment
        if ($payment_method === 'PayPal' && !empty($cart)) {
            $_SESSION['pending_paypal_cart'] = [
                'cart' => $cart,
                'total_amount' => $total_amount,
                'shipping_address' => $shipping_address,
                'timestamp' => time()
            ];
        }
        
        // If cart is empty but we have PayPal payment, try to recover from session
        if (empty($cart) && $payment_method === 'PayPal' && !empty($paypal_order_id)) {
            if (isset($_SESSION['pending_paypal_cart'])) {
                $pendingData = $_SESSION['pending_paypal_cart'];
                
                // Check if session data is not too old (30 minutes max)
                if ((time() - $pendingData['timestamp']) <= 1800) {
                    $cart = $pendingData['cart'];
                    $total_amount = $pendingData['total_amount'];
                    $shipping_address = $pendingData['shipping_address'];
                    
                    // Clear the session data after recovery
                    unset($_SESSION['pending_paypal_cart']);
                } else {
                    unset($_SESSION['pending_paypal_cart']);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Session expired. Please create a new order.'
                    ]);
                    return;
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Cart data lost after payment. Contact support with PayPal ID: ' . $paypal_order_id
                ]);
                return;
            }
        }
    
        if ($total_amount <= 0 || empty($shipping_address) || empty($payment_method) || empty($cart)) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing or invalid order data'
            ]);
            return;
        }
        
        // Validate PayPal order if payment method is PayPal
        if ($payment_method === 'PayPal' && empty($paypal_order_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'PayPal order ID is required for PayPal payments'
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
                $payment_method,
                $paypal_order_id,
                $payment_status
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

            // ================================================================
            // SAVE PAYPAL TRANSACTION TO paypal_transactions TABLE
            // Only runs for PayPal payments with a valid order ID.
            // Failure is non-fatal — order is already safely saved above.
            // ================================================================
            if ($payment_method === 'PayPal' && !empty($paypal_order_id)) {
                try {
                    $captureDetails = json_decode(
                        trim($_POST['paypal_capture_details'] ?? ''),
                        true
                    );
                    $this->savePayPalTransaction($paypal_order_id, $total_amount, $captureDetails);
                } catch (Exception $txEx) {
                    error_log("PayPal transaction log failed for order #$order_id: " . $txEx->getMessage());
                }
            }
            // ================================================================

            // ================================================================
            // SEND ORDER RECEIPT EMAIL
            // Runs after commit so the order is safely saved before emailing.
            // ================================================================
            try {
                // Fetch the order_number generated by the DB (e.g. 'ORD-000042')
                $stmt = $this->db->prepare("SELECT order_number FROM orders WHERE id = :id");
                $stmt->execute([':id' => $order_id]);
                $orderRow     = $stmt->fetch(PDO::FETCH_ASSOC);
                $order_number = $orderRow['order_number'] ?? ('ORD-' . str_pad($order_id, 6, '0', STR_PAD_LEFT));

                // Build items array for the email from the cart POST data
                $emailItems = [];
                foreach ($cart as $item) {
                    $unitPrice      = floatval($item['price']    ?? 0);
                    $qty            = intval($item['quantity']   ?? 1);
                    $emailItems[] = [
                        'name'     => $item['name']  ?? 'Product',
                        'size'     => $item['size']  ?? 'One Size',
                        'quantity' => $qty,
                        'price'    => $unitPrice,
                        'subtotal' => $unitPrice * $qty,
                    ];
                }

                $this->sendOrderReceiptEmail(
                    $_SESSION['email']    ?? '',
                    $order_number,
                    $emailItems,
                    $total_amount,
                    $payment_method,
                    $shipping_address,
                    $paypal_order_id ?: null,
                    $_SESSION['username'] ?? 'Valued Customer'
                );
            } catch (Exception $mailEx) {
                // Email failure must never block the order success response
                error_log("Receipt email failed for order #$order_id: " . $mailEx->getMessage());
            }
            // ================================================================
            
            $successMessage = ($payment_method === 'PayPal') 
                ? 'Order placed successfully! Payment confirmed via PayPal.'
                : 'Order placed successfully! Stock updated.';
    
            echo json_encode([
                'success'          => true,
                'message'          => $successMessage,
                'order_id'         => $order_id,
                'payment_method'   => $payment_method,
                'payment_status'   => $payment_status,
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
        $input    = json_decode(file_get_contents('php://input'), true);
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
                'orders'  => $orders
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
                'items'   => $items
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
            if (!isset($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User not logged in'
                ]);
                return;
            }

            $user_id = $_SESSION['user_id'];

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

            $formattedOrders = [];
            foreach ($orders as $order) {
                $timeAgo     = $this->getTimeAgo($order['last_update']);
                $paymentInfo = '';
                if ($order['payment_method'] === 'PayPal' && !empty($order['paypal_order_id'])) {
                    $paymentInfo = ' (Paid via PayPal)';
                }
                $message = $this->generateOrderMessage($order['status'], $order['order_number'], $order['total_amount']) . $paymentInfo;
                
                $formattedOrders[] = [
                    'id'             => $order['id'],
                    'order_number'   => $order['order_number'],
                    'status'         => $order['status'],
                    'payment_method' => $order['payment_method'],
                    'payment_status' => $order['payment_status'] ?? 'pending',
                    'message'        => $message,
                    'time_ago'       => $timeAgo,
                    'timestamp'      => $order['last_update'],
                    'total_amount'   => $order['total_amount'],
                    'item_count'     => $order['item_count']
                ];
            }

            echo json_encode([
                'success' => true,
                'orders'  => $formattedOrders,
                'count'   => count($formattedOrders)
            ]);

        } catch (Exception $e) {
            error_log("Error fetching recent orders: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching recent orders'
            ]);
        }
    }

    private function generateOrderMessage($status, $orderNumber, $totalAmount) {
        $formattedAmount = '₱' . number_format($totalAmount, 2);
        switch (strtolower($status)) {
            case 'pending':    return "Order {$orderNumber} ({$formattedAmount}) is pending confirmation";
            case 'processing': return "Order {$orderNumber} ({$formattedAmount}) is being processed";
            case 'shipped':    return "Order {$orderNumber} ({$formattedAmount}) has been shipped";
            case 'delivered':  return "Order {$orderNumber} ({$formattedAmount}) has been delivered";
            case 'cancelled':  return "Order {$orderNumber} ({$formattedAmount}) was cancelled";
            default:           return "Order {$orderNumber} ({$formattedAmount}) status: {$status}";
        }
    }

    private function getTimeAgo($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   { $m = floor($diff/60);   return $m . ' minute' . ($m>1?'s':'') . ' ago'; }
        if ($diff < 86400)  { $h = floor($diff/3600);  return $h . ' hour'   . ($h>1?'s':'') . ' ago'; }
        if ($diff < 604800) { $d = floor($diff/86400); return $d . ' day'    . ($d>1?'s':'') . ' ago'; }
        return date('M j, Y', strtotime($datetime));
    }

    public function getPendingOrdersCount() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'count' => 0]);
            return;
        }
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as pending_count FROM orders
                WHERE user_id = :user_id AND status = 'pending'
            ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => (int)$result['pending_count']]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }

    public function getUnreadNotificationsCount() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'count' => 0]);
            return;
        }
        echo json_encode(['success' => true, 'count' => 0]);
    }

    public function markNotificationsRead() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            return;
        }
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
            echo json_encode(['success' => true, 'count' => (int)$result['notification_count']]);
        } catch (Exception $e) {
            error_log("Error getting notification count: " . $e->getMessage());
            echo json_encode(['success' => false, 'count' => 0]);
        }
    }

    public function getCustomerOrders() {
        $customer_id = intval($_GET['customer_id'] ?? 0);
        if ($customer_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
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
                    o.payment_method,
                    o.payment_status,
                    o.paypal_order_id,
                    COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = :customer_id
                GROUP BY o.id, o.order_number, o.created_at, o.total_amount, o.status, o.payment_method, o.payment_status, o.paypal_order_id
                ORDER BY o.created_at DESC
                LIMIT 5
            ");
            $stmt->execute(['customer_id' => $customer_id]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $formatted_orders = [];
            foreach ($orders as $order) {
                $order_number  = $order['order_number'] ?: 'ORD-' . str_pad($order['order_id'], 6, '0', STR_PAD_LEFT);
                $paymentBadge  = ($order['payment_method'] === 'PayPal')
                    ? ' <span class="badge bg-info">PayPal</span>' : '';

                $formatted_orders[] = [
                    'order_id'       => '#' . $order_number . $paymentBadge,
                    'date'           => date('M j, Y', strtotime($order['order_date'])),
                    'items'          => (int)$order['item_count'] . ' item' . ((int)$order['item_count'] !== 1 ? 's' : ''),
                    'total'          => '₱' . number_format((float)$order['total_amount'], 2),
                    'status'         => ucfirst($order['status']),
                    'status_class'   => $this->getStatusClass($order['status']),
                    'payment_status' => $order['payment_status'] ?? 'pending'
                ];
            }
            echo json_encode(['success' => true, 'orders' => $formatted_orders]);

        } catch (PDOException $e) {
            error_log("Error fetching customer orders: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error occurred']);
        }
    }

    private function getStatusClass($status) {
        switch (strtolower($status)) {
            case 'completed':
            case 'delivered':  return 'bg-success';
            case 'pending':    return 'bg-warning';
            case 'processing': return 'bg-info';
            case 'cancelled':  return 'bg-danger';
            default:           return 'bg-secondary';
        }
    }

    // =========================================================================
    // RECEIPT EMAIL — private, no AdminController dependency needed
    // =========================================================================
    private function sendOrderReceiptEmail(
        $recipientEmail,
        $orderNumber,
        $items,
        $totalAmount,
        $paymentMethod,
        $shippingAddress,
        $paypalOrderId = null,
        $customerName  = 'Valued Customer'
    ) {
        require_once __DIR__ . '/../../libraries/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../../libraries/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../libraries/phpmailer/src/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $isPayPal      = (stripos($paymentMethod, 'paypal') !== false);
        $payBadgeColor = $isPayPal ? '#0070ba' : '#1a7a3c';
        $payLabel      = $isPayPal ? 'PayPal' : 'Cash on Delivery (COD)';
        $payIcon       = $isPayPal ? '&#128179;' : '&#128181;';
        $payNote       = $isPayPal
            ? 'Your payment has been captured via PayPal. No further action is required.'
            : 'Please prepare the exact amount upon delivery. Payment will be collected by the courier.';

        $itemRows = '';
        foreach ($items as $item) {
            $name     = htmlspecialchars($item['name']     ?? 'Item');
            $size     = htmlspecialchars($item['size']     ?? 'One Size');
            $qty      = (int)($item['quantity']            ?? 1);
            $price    = number_format((float)($item['price']    ?? 0), 2);
            $subtotal = number_format((float)($item['subtotal'] ?? ($item['price'] * $item['quantity'])), 2);
            $itemRows .= "
                <tr>
                    <td style='padding:14px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#1a1a1a;font-weight:500;'>
                        $name<div style='color:#999;font-size:12px;margin-top:3px;font-weight:400;'>Size: $size</div>
                    </td>
                    <td style='padding:14px 10px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#666;text-align:center;'>$qty</td>
                    <td style='padding:14px 10px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#666;text-align:right;'>&#8369;$price</td>
                    <td style='padding:14px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;color:#000;font-weight:700;text-align:right;'>&#8369;$subtotal</td>
                </tr>";
        }

        $paypalRow = '';
        if ($isPayPal && !empty($paypalOrderId)) {
            $sid = htmlspecialchars($paypalOrderId);
            $paypalRow = "<tr>
                <td style='padding:10px 0 0;font-size:12px;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:1px;font-weight:600;'>Transaction ID</td>
                <td style='padding:10px 0 0;font-size:13px;color:#fff;font-weight:700;font-family:monospace;text-align:right;'>$sid</td>
            </tr>";
        }

        $formattedTotal   = number_format((float)$totalAmount, 2);
        $formattedAddress = nl2br(htmlspecialchars($shippingAddress));
        $orderDate        = date('F j, Y \a\t g:i A');
        $year             = date('Y');
        $safeName         = htmlspecialchars($customerName);
        $safeOrderNum     = htmlspecialchars($orderNumber);

        $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');</style>
    </head>
    <body style='margin:0;padding:0;background:#f4f4f4;font-family:\"Poppins\",-apple-system,sans-serif;'>
    <table role='presentation' style='width:100%;border-collapse:collapse;background:#f4f4f4;'>
    <tr><td align='center' style='padding:40px 20px;'>
    <table role='presentation' style='width:100%;max-width:600px;border-collapse:collapse;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.08);'>
        <tr><td style='background:linear-gradient(135deg,#000 0%,#1a1a1a 100%);padding:50px 40px;text-align:center;'>
            <h1 style='margin:0;color:#fff;font-size:48px;font-weight:900;letter-spacing:4px;text-transform:uppercase;'>EMPIRE</h1>
            <p style='margin:12px 0 0;color:#a0a0a0;font-size:13px;letter-spacing:3px;text-transform:uppercase;font-weight:500;'>Streetwear Culture</p>
        </td></tr>
        <tr><td style='background:#111;padding:18px 40px;text-align:center;'>
            <p style='margin:0;color:#fff;font-size:15px;font-weight:600;letter-spacing:1px;'>&#9989;&nbsp; ORDER CONFIRMED &mdash; Here is your receipt</p>
        </td></tr>
        <tr><td style='padding:50px 40px 30px;'>
            <h2 style='margin:0 0 14px;color:#1a1a1a;font-size:28px;font-weight:700;'>Thank you trusting us!</h2>
            <p style='margin:0;color:#555;font-size:15px;line-height:1.7;'>We&rsquo;ve received your order and it&rsquo;s now being prepared. Below is your full receipt.</p>
        </td></tr>
        <tr><td style='padding:0 40px 30px;'>
            <table role='presentation' style='width:100%;border-collapse:collapse;background:#f8f8f8;border:3px solid #000;border-radius:12px;'>
                <tr>
                    <td style='padding:24px 28px;border-right:2px solid #e8e8e8;'>
                        <p style='margin:0;color:#999;font-size:11px;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;'>Order Number</p>
                        <p style='margin:8px 0 0;color:#000;font-size:22px;font-weight:800;'>#$safeOrderNum</p>
                    </td>
                    <td style='padding:24px 28px;'>
                        <p style='margin:0;color:#999;font-size:11px;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;'>Order Date</p>
                        <p style='margin:8px 0 0;color:#000;font-size:14px;font-weight:600;'>$orderDate</p>
                    </td>
                </tr>
            </table>
        </td></tr>
        <tr><td style='padding:0 40px 30px;'>
            <table role='presentation' style='width:100%;border-collapse:collapse;background:$payBadgeColor;border-radius:12px;'>
                <tr><td style='padding:22px 28px;'>
                    <table role='presentation' style='width:100%;border-collapse:collapse;'>
                        <tr><td>
                            <p style='margin:0;color:rgba(255,255,255,.8);font-size:11px;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;'>Payment Method</p>
                            <p style='margin:8px 0 4px;color:#fff;font-size:20px;font-weight:700;'>$payIcon &nbsp;$payLabel</p>
                            <p style='margin:6px 0 0;color:rgba(255,255,255,.88);font-size:13px;line-height:1.5;'>$payNote</p>
                        </td></tr>
                        $paypalRow
                    </table>
                </td></tr>
            </table>
        </td></tr>
        <tr><td style='padding:0 40px 30px;'>
            <p style='margin:0 0 14px;color:#1a1a1a;font-size:12px;text-transform:uppercase;letter-spacing:2px;font-weight:700;'>Items Ordered</p>
            <table role='presentation' style='width:100%;border-collapse:collapse;border:1px solid #e8e8e8;border-radius:10px;overflow:hidden;'>
                <thead><tr style='background:#000;'>
                    <th style='padding:12px 16px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:1px;text-align:left;font-weight:600;'>Product</th>
                    <th style='padding:12px 10px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:1px;text-align:center;font-weight:600;'>Qty</th>
                    <th style='padding:12px 10px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:1px;text-align:right;font-weight:600;'>Unit Price</th>
                    <th style='padding:12px 16px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:1px;text-align:right;font-weight:600;'>Subtotal</th>
                </tr></thead>
                <tbody>$itemRows</tbody>
                <tfoot><tr style='background:#f8f8f8;'>
                    <td colspan='3' style='padding:18px 10px 18px 16px;text-align:right;font-size:14px;font-weight:700;color:#1a1a1a;text-transform:uppercase;letter-spacing:1px;border-top:2px solid #000;'>Total Amount</td>
                    <td style='padding:18px 16px;text-align:right;font-size:22px;font-weight:800;color:#000;border-top:2px solid #000;'>&#8369;$formattedTotal</td>
                </tr></tfoot>
            </table>
        </td></tr>
        <tr><td style='padding:0 40px 30px;'>
            <p style='margin:0 0 12px;color:#1a1a1a;font-size:12px;text-transform:uppercase;letter-spacing:2px;font-weight:700;'>Delivery Address</p>
            <table role='presentation' style='width:100%;border-collapse:collapse;'>
                <tr><td style='padding:18px 22px;background:#f8f8f8;border:1px solid #e8e8e8;border-left:4px solid #000;border-radius:10px;'>
                    <p style='margin:0;color:#333;font-size:14px;line-height:1.7;'>&#128205;&nbsp;$formattedAddress</p>
                </td></tr>
            </table>
        </td></tr>
        <tr><td style='padding:0 40px 50px;'>
            <table role='presentation' style='width:100%;border-collapse:collapse;'>
                <tr><td style='padding:20px 24px;background:#f0f8ff;border-left:4px solid #000;border-radius:8px;'>
                    <p style='margin:0 0 8px;color:#1a1a1a;font-size:14px;font-weight:700;'>What happens next?</p>
                    <p style='margin:0;color:#555;font-size:13px;line-height:1.7;'>Our team will process your order shortly. You will receive another email when your order status is updated. You can also track your order anytime by logging into your account.</p>
                </td></tr>
            </table>
            <p style='margin:32px 0 0;color:#999;font-size:13px;text-align:center;'>Questions? Contact us at <a href='mailto:empirebsit2025@gmail.com' style='color:#000;text-decoration:none;font-weight:600;'>empirebsit2025@gmail.com</a></p>
        </td></tr>
        <tr><td style='background:#000;padding:40px;text-align:center;'>
            <p style='margin:0 0 8px;color:#fff;font-size:16px;font-weight:700;letter-spacing:2px;text-transform:uppercase;'>EMPIRE</p>
            <p style='margin:0 0 24px;color:#808080;font-size:13px;'>Culture &bull; Exclusivity &bull; Lifestyle</p>
            <div style='border-top:1px solid #333;padding-top:24px;'>
                <p style='margin:0;color:#666;font-size:12px;line-height:1.6;'>&copy; " . $year . " Empire Streetwear. All rights reserved.<br>This is an automated order receipt. Please do not reply to this email.</p>
            </div>
        </td></tr>
    </table>
    </td></tr>
    </table>
    </body>
    </html>
";

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'empirebsit2025@gmail.com';
            $mail->Password   = 'mqvg swfp cfbu vhze';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('no-reply@empire.com', 'EMPIRE E-COMMERCE');
            $mail->addAddress($recipientEmail, $safeName);

            $mail->isHTML(true);
            $mail->Subject = "Order Receipt #$safeOrderNum — EMPIRE Streetwear";
            $mail->AltBody = "Thank you for your order #$safeOrderNum! Total: PHP $formattedTotal | Payment: $payLabel | Ship to: $shippingAddress";

            $mail->send();
            error_log("Order receipt sent to $recipientEmail for order #$orderNumber");
            return true;

        } catch (Exception $e) {
            error_log("Order Receipt Mail Error #$orderNumber: " . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Saves a completed PayPal payment to the paypal_transactions table.
     * Mirrors the logTransaction() method in PayPalController.
     *
     * @param string     $paypalOrderId  The PayPal order ID (e.g. 2GM46407E9880714T)
     * @param float      $fallbackAmount Amount to use when capture details are missing
     * @param array|null $captureDetails Full capture response decoded from JSON (may be null)
     */
    private function savePayPalTransaction(string $paypalOrderId, float $fallbackAmount, ?array $captureDetails): void {
        // Extract fields from the capture response when available
        $status          = $captureDetails['status']
                           ?? 'COMPLETED';
        $payerEmail      = $captureDetails['payer']['email_address']
                           ?? '';
        $capturedAmount  = $captureDetails['purchase_units'][0]['payments']['captures'][0]['amount']['value']
                           ?? $fallbackAmount;
        $currency        = $captureDetails['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code']
                           ?? 'PHP';
        $transactionData = $captureDetails
                           ? json_encode($captureDetails)
                           : json_encode(['paypal_order_id' => $paypalOrderId, 'amount' => $fallbackAmount]);

        $stmt = $this->db->prepare("
            INSERT INTO paypal_transactions
                (paypal_order_id, status, payer_email, amount, currency, transaction_data, created_at)
            VALUES
                (:paypal_order_id, :status, :payer_email, :amount, :currency, :transaction_data, NOW())
        ");

        $stmt->execute([
            ':paypal_order_id'   => $paypalOrderId,
            ':status'            => $status,
            ':payer_email'       => $payerEmail,
            ':amount'            => $capturedAmount,
            ':currency'          => $currency,
            ':transaction_data'  => $transactionData,
        ]);
    }

    public function getOrderSummary() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to view order summary']);
            return;
        }
        $order_id = intval($_GET['order_id'] ?? 0);
        if ($order_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
            return;
        }
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, u.username as user_name, u.phone as user_phone, u.address as user_address
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = :order_id AND o.user_id = :user_id
            ");
            $stmt->execute(['order_id' => $order_id, 'user_id' => $_SESSION['user_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($order) {
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error fetching order summary: ' . $e->getMessage()]);
        }
    }
}

// ── Dispatcher ───────────────────────────────────────────────────────────────
try {
    $database = new Database();
    $db       = $database->getConnection();

    $controller = new OrderController($db);

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'placeOrder':               $controller->placeOrder();               break;
        case 'cancelOrder':              $controller->cancelOrder();              break;
        case 'getUserOrders':            $controller->getUserOrders();            break;
        case 'getOrderDetails':          $controller->getOrderDetails();          break;
        case 'getOrderSummary':          $controller->getOrderSummary();          break;
        case 'getRecentOrders':          $controller->getRecentOrders();          break;
        case 'getNotificationCount':     $controller->getNotificationCount();     break;
        case 'getCustomerOrders':        $controller->getCustomerOrders();        break;
        case 'getPendingOrdersCount':    $controller->getPendingOrdersCount();    break;
        case 'getUnreadNotificationsCount': $controller->getUnreadNotificationsCount(); break;
        case 'markNotificationsRead':    $controller->markNotificationsRead();    break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $e->getMessage()]);
}