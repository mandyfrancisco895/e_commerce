<?php
// app/controllers/PayPalController.php

require_once __DIR__ . '/../../config/dbcon.php';
require_once __DIR__ . '/../models/PayPalPayment.php';

header('Content-Type: application/json');

class PayPalController {
    private $paypal;
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->paypal = new PayPalPayment();
    }
    
    /**
     * Create PayPal Order and store cart data for later retrieval
     */
    public function createOrder() {
        try {
            session_start();
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['total_amount']) || !isset($input['items'])) {
                throw new Exception('Missing required parameters');
            }
            
            // Store cart data and order info in session for retrieval after PayPal redirect
            $_SESSION['pending_paypal_order'] = [
                'cart' => $input['items'],
                'total_amount' => $input['total_amount'],
                'shipping_address' => $input['shipping_address'] ?? '',
                'timestamp' => time()
            ];
            
            // Prepare order data for PayPal
            $orderData = [
                'total_amount' => $input['total_amount'],
                'description' => 'Empire Streetwear Purchase',
                'items' => $input['items']
            ];
            
            // Create PayPal order
            $result = $this->paypal->createOrder($orderData);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Capture PayPal Payment and create order in database
     */
    public function captureOrder() {
        try {
            session_start();
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['order_id'])) {
                throw new Exception('Missing PayPal order ID');
            }
            
            // Retrieve stored cart data
            if (!isset($_SESSION['pending_paypal_order'])) {
                throw new Exception('Cart data lost after payment. Contact support with PayPal ID: ' . $input['order_id']);
            }
            
            $pendingOrder = $_SESSION['pending_paypal_order'];
            
            // Check if session data is not too old (30 minutes max)
            if ((time() - $pendingOrder['timestamp']) > 1800) {
                unset($_SESSION['pending_paypal_order']);
                throw new Exception('Session expired. Please create a new order.');
            }
            
            // Capture the payment
            $result = $this->paypal->captureOrder($input['order_id']);
            
            if ($result['success']) {
                // Log the transaction
                $this->logTransaction($input['order_id'], $result);
                
                // Create order in database
                $orderId = $this->createDatabaseOrder(
                    $input['order_id'],
                    $pendingOrder['cart'],
                    $pendingOrder['total_amount'],
                    $pendingOrder['shipping_address'],
                    $result
                );
                
                if ($orderId) {
                    // Send order receipt email
                    $this->sendOrderReceiptEmail(
                        $orderId,
                        $input['order_id'],
                        $pendingOrder['cart'],
                        $pendingOrder['total_amount'],
                        $pendingOrder['shipping_address']
                    );
                    
                    // Clear the pending order from session
                    unset($_SESSION['pending_paypal_order']);
                    
                    $result['order_id'] = $orderId;
                }
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create order in database after successful PayPal payment
     */
    private function createDatabaseOrder($paypalOrderId, $cart, $totalAmount, $shippingAddress, $paypalResult) {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not logged in');
            }
            
            $this->db->beginTransaction();
            
            // Create order
            $sql = "INSERT INTO orders 
                    (user_id, total_amount, status, shipping_address, payment_method, paypal_order_id, payment_status, created_at) 
                    VALUES 
                    (:user_id, :total_amount, 'pending', :shipping_address, 'PayPal', :paypal_order_id, 'completed', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':total_amount' => $totalAmount,
                ':shipping_address' => $shippingAddress,
                ':paypal_order_id' => $paypalOrderId
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Add order items
            $itemSql = "INSERT INTO order_items 
                        (order_id, product_id, product_name, price, quantity, size, subtotal) 
                        VALUES 
                        (:order_id, :product_id, :product_name, :price, :quantity, :size, :subtotal)";
            
            $itemStmt = $this->db->prepare($itemSql);
            
            foreach ($cart as $item) {
                $productId = intval($item['id'] ?? 0);
                $productName = $item['name'] ?? '';
                $price = floatval($item['price'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);
                $size = $item['size'] ?? null;
                $subtotal = $price * $quantity;
                
                $itemStmt->execute([
                    ':order_id' => $orderId,
                    ':product_id' => $productId,
                    ':product_name' => $productName,
                    ':price' => $price,
                    ':quantity' => $quantity,
                    ':size' => $size,
                    ':subtotal' => $subtotal
                ]);
            }
            
            $this->db->commit();
            
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to create database order: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send order receipt email (similar to OrderController)
     */
    private function sendOrderReceiptEmail($orderId, $paypalOrderId, $cart, $totalAmount, $shippingAddress) {
        try {
            // Get order number
            $stmt = $this->db->prepare("SELECT order_number FROM orders WHERE id = :id");
            $stmt->execute([':id' => $orderId]);
            $orderRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $orderNumber = $orderRow['order_number'] ?? ('ORD-' . str_pad($orderId, 6, '0', STR_PAD_LEFT));
            
            // Build items array
            $emailItems = [];
            foreach ($cart as $item) {
                $unitPrice = floatval($item['price'] ?? 0);
                $qty = intval($item['quantity'] ?? 1);
                $emailItems[] = [
                    'name' => $item['name'] ?? 'Product',
                    'size' => $item['size'] ?? 'One Size',
                    'quantity' => $qty,
                    'price' => $unitPrice,
                    'subtotal' => $unitPrice * $qty,
                ];
            }
            
            // Use PHPMailer
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            $recipientEmail = $_SESSION['email'] ?? '';
            $customerName = $_SESSION['username'] ?? 'Valued Customer';
            
            // Sanitize for HTML
            $safeName = htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8');
            $safeOrderNum = htmlspecialchars($orderNumber, ENT_QUOTES, 'UTF-8');
            $formattedTotal = number_format($totalAmount, 2);
            $formattedAddress = nl2br(htmlspecialchars($shippingAddress, ENT_QUOTES, 'UTF-8'));
            $orderDate = date('F j, Y');
            $year = date('Y');
            
            // Build item rows
            $itemRows = '';
            foreach ($emailItems as $item) {
                $itemName = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
                $itemSize = htmlspecialchars($item['size'], ENT_QUOTES, 'UTF-8');
                $itemQty = intval($item['quantity']);
                $itemPrice = number_format($item['price'], 2);
                $itemSubtotal = number_format($item['subtotal'], 2);
                
                $itemRows .= "<tr style='border-bottom:1px solid #e8e8e8;'>
                    <td style='padding:16px;color:#1a1a1a;font-size:14px;font-weight:600;'>$itemName<br><span style='color:#777;font-size:12px;font-weight:400;'>Size: $itemSize</span></td>
                    <td style='padding:16px 10px;text-align:center;color:#555;font-size:14px;'>$itemQty</td>
                    <td style='padding:16px 10px;text-align:right;color:#555;font-size:14px;'>&#8369;$itemPrice</td>
                    <td style='padding:16px;text-align:right;color:#1a1a1a;font-size:14px;font-weight:600;'>&#8369;$itemSubtotal</td>
                </tr>";
            }
            
            // PayPal specific
            $payBadgeColor = '#0070ba';
            $payIcon = '💳';
            $payLabel = 'PayPal';
            $payNote = 'Payment successfully processed via PayPal.';
            $safePaypalId = htmlspecialchars($paypalOrderId, ENT_QUOTES, 'UTF-8');
            $paypalRow = "<tr><td style='padding-top:16px;'><p style='margin:0;color:rgba(255,255,255,.75);font-size:11px;'>Transaction ID: <strong style='color:#fff;'>$safePaypalId</strong></p></td></tr>";
            
            // Build HTML email
            $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width,initial-scale=1.0'>
    <title>Order Receipt #$safeOrderNum</title>
</head>
<body style='margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Helvetica,Arial,sans-serif;background:#f4f4f4;'>
    <table role='presentation' style='width:100%;border-collapse:collapse;background:#f4f4f4;'>
    <tr><td style='padding:40px 20px;'>
    <table role='presentation' style='max-width:640px;margin:0 auto;background:#fff;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,.08);'>
        <tr><td style='background:#000;padding:32px 40px;border-radius:16px 16px 0 0;text-align:center;'>
            <h1 style='margin:0;color:#fff;font-size:28px;font-weight:800;letter-spacing:3px;text-transform:uppercase;'>EMPIRE</h1>
            <p style='margin:10px 0 0;color:#808080;font-size:13px;letter-spacing:2px;'>STREETWEAR E-COMMERCE</p>
        </td></tr>
        <tr><td style='padding:40px 40px 0;'>
            <h2 style='margin:0 0 12px;color:#1a1a1a;font-size:24px;font-weight:700;'>Hey $safeName! 👋</h2>
            <p style='margin:0 0 24px;color:#555;font-size:15px;line-height:1.6;'>Thank you for your order! We've received your payment and your order is being processed. Here are the details:</p>
        </td></tr>
        <tr><td style='padding:0 40px 30px;'>
            <table role='presentation' style='width:100%;border-collapse:collapse;border:2px solid #e8e8e8;border-radius:12px;overflow:hidden;'>
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
                <p style='margin:0;color:#666;font-size:12px;line-height:1.6;'>&copy; $year Empire Streetwear. All rights reserved.<br>This is an automated order receipt. Please do not reply to this email.</p>
            </div>
        </td></tr>
    </table>
    </td></tr>
    </table>
</body>
</html>
";
            
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'empirebsit2025@gmail.com';
            $mail->Password = 'mqvg swfp cfbu vhze';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            $mail->setFrom('no-reply@empire.com', 'EMPIRE E-COMMERCE');
            $mail->addAddress($recipientEmail, $safeName);
            
            $mail->isHTML(true);
            $mail->Subject = "Order Receipt #$safeOrderNum — EMPIRE Streetwear";
            $mail->AltBody = "Thank you for your order #$safeOrderNum! Total: PHP $formattedTotal | Payment: PayPal | Ship to: $shippingAddress";
            
            $mail->send();
            error_log("PayPal order receipt sent to $recipientEmail for order #$orderNumber");
            
        } catch (Exception $e) {
            error_log("PayPal order receipt email failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get Order Details
     */
    public function getOrderDetails() {
        try {
            $orderId = $_GET['order_id'] ?? null;
            
            if (!$orderId) {
                throw new Exception('Missing order ID');
            }
            
            $result = $this->paypal->getOrderDetails($orderId);
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Log PayPal transaction to database
     */
    private function logTransaction($paypalOrderId, $captureData) {
        try {
            $sql = "INSERT INTO paypal_transactions 
                    (paypal_order_id, status, payer_email, amount, currency, transaction_data, created_at) 
                    VALUES 
                    (:paypal_order_id, :status, :payer_email, :amount, :currency, :transaction_data, NOW())";
            
            $stmt = $this->db->prepare($sql);
            
            $payerEmail = $captureData['payer']['email_address'] ?? '';
            $amount = $captureData['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? 0;
            $currency = $captureData['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? 'PHP';
            $transactionData = json_encode($captureData);
            
            $stmt->execute([
                ':paypal_order_id' => $paypalOrderId,
                ':status' => $captureData['status'],
                ':payer_email' => $payerEmail,
                ':amount' => $amount,
                ':currency' => $currency,
                ':transaction_data' => $transactionData
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Failed to log PayPal transaction: " . $e->getMessage());
            return false;
        }
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new PayPalController();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'createOrder':
            $controller->createOrder();
            break;
            
        case 'captureOrder':
            $controller->captureOrder();
            break;
            
        case 'getOrderDetails':
            $controller->getOrderDetails();
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}