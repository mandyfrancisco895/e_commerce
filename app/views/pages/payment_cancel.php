<?php
// app/views/pages/payment_cancel.php

require_once __DIR__ . '/../../../config/session_checker.php';
require_once __DIR__ . '/../../../config/dbcon.php';

$database = new Database();
$db = $database->getConnection();

require_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - EMPIRE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cancel-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cancel-card {
            max-width: 600px;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .cancel-icon-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #ffc107;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        
        .cancel-icon {
            color: white;
            font-size: 3rem;
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
    </style>
</head>
<body>
    <div class="container cancel-container">
        <div class="cancel-card text-center bg-white">
            <div class="cancel-icon-circle">
                <i class="fas fa-times cancel-icon"></i>
            </div>
            
            <h1 class="mb-3 text-warning fw-bold">Payment Cancelled</h1>
            <p class="lead text-muted mb-4">
                You have cancelled the payment process. No charges have been made to your account.
            </p>
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                Your cart items are still saved. You can complete your purchase anytime.
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a href="../pages/shop.php" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-shopping-cart me-2"></i>Return to Cart
                </a>
                <a href="../pages/shop.php" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
            
            <hr class="my-4">
            
            <div class="text-muted small">
                <p class="mb-2">
                    <i class="fas fa-question-circle me-1"></i>
                    Having trouble with payment? Try using Cash on Delivery instead.
                </p>
                <p class="mb-0">
                    <i class="fas fa-headset me-1"></i>
                    Need assistance? <a href="contact.php">Contact our support team</a>
                </p>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/shop_footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-redirect after 8 seconds
        setTimeout(() => {
            window.location.href = '../pages/shop.php';
        }, 8000);
    </script>
</body>
</html>