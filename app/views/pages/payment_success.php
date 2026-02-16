<?php
// app/views/pages/payment_success.php

require_once __DIR__ . '/../../../config/session_checker.php';
require_once __DIR__ . '/../../../config/dbcon.php';

$database = new Database();
$db = $database->getConnection();

// Get PayPal order details from URL
$paypalOrderId = $_GET['token'] ?? null;
$payerId = $_GET['PayerID'] ?? null;

require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - EMPIRE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-card {
            max-width: 600px;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .checkmark-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        .checkmark {
            color: white;
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <div class="container success-container">
        <div class="success-card text-center bg-white">
            <div class="checkmark-circle">
                <i class="fas fa-check checkmark"></i>
            </div>
            
            <h1 class="mb-3 text-success fw-bold">Payment Successful!</h1>
            <p class="lead text-muted mb-4">
                Thank you for your purchase. Your order has been received and is being processed.
            </p>
            
            <?php if ($paypalOrderId): ?>
            <div class="alert alert-info mb-4">
                <strong>PayPal Transaction ID:</strong><br>
                <small class="font-monospace"><?= htmlspecialchars($paypalOrderId) ?></small>
            </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <i class="fas fa-envelope text-primary me-2"></i>
                <span>A confirmation email has been sent to your email address.</span>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="orders.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-box me-2"></i>View My Orders
                </a>
                <a href="shop.php" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
            
            <hr class="my-4">
            
            <div class="text-muted small">
                <p class="mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Your order will be shipped within 2-3 business days.
                </p>
                <p class="mb-0">
                    <i class="fas fa-headset me-1"></i>
                    Need help? <a href="contact.php">Contact our support team</a>
                </p>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/shop_footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>