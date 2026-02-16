<?php

    require_once __DIR__ . '/../../../config/session_checker.php';
    require_once __DIR__ . '/../../../config/dbcon.php';

    require_once __DIR__ . '/../../../app/controllers/ProductController.php';
    require_once __DIR__ . '/../../../app/controllers/NotificationController.php';

    require_once __DIR__ . '/../../../app/models/Product.php';
    require_once __DIR__ . '/../../../app/controllers/CategoryController.php';
    require_once __DIR__ . '/../../../app/models/Category.php';
    require_once __DIR__ . '/../../../config/PayPalConfig.php';

    $database = new Database();
    $db = $database->getConnection();

    $maintStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'");
    $isMaint = $maintStmt->fetchColumn();
    $userRole = $_SESSION['role'] ?? 'guest';

    $categoryController = new CategoryController($db);
    $productController  = new ProductController($db);
    $dbCategories = $categoryController->index(); 
    
    $onSaleCategoryId = null;
    foreach ($dbCategories as $category) {
        if ($category['name'] === 'On Sale') {
            $onSaleCategoryId = $category['id'];
            break;
        }
    }

    $categoryIcons = [];
    foreach ($dbCategories as $cat) {
        $categoryIcons[$cat['id']] = [
            $cat['name'],
            $cat['icon'] ?? 'fas fa-box' // fallback icon
        ];
    }

    if ($onSaleCategoryId) {
        $products = $productController->getProductsByCategory($onSaleCategoryId);
        
        foreach ($products as &$product) {
            if (isset($categoryIcons[$product['category_id']])) {
                $product['category_name'] = $categoryIcons[$product['category_id']][0];
                $product['category_icon'] = $categoryIcons[$product['category_id']][1];
            } else {
                // Fallback if category not found
                $product['category_name'] = 'Other';
                $product['category_icon'] = 'fas fa-box';
            }
        }
        unset($product); 
    } else {
        $products = []; 
    }

    $categoryIcons = [];
    foreach ($dbCategories as $cat) {
        $categoryIcons[$cat['id']] = [
            $cat['name'],
            $cat['icon'] ?? 'fas fa-box' 
        ];
    }

    $categories = [];
    foreach ($products as $prod) {
        $categories[$prod['category_id']][] = $prod;
    }

    $controller = new NotificationController($db);

    if (isset($_GET['action']) && $_GET['action'] === 'getUserNotifications') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }
        $controller->getUserNotifications($_SESSION['user_id']);
        exit;
    }

    if ($isMaint === '1' && $userRole !== 'admin') {
        $maintenancePath = __DIR__ . '/../maintenance_screen.php';
        if (file_exists($maintenancePath)) {
            include $maintenancePath;
        } else {
            echo "<h1>Site Under Maintenance</h1><p>We'll be back soon!</p>";
        }
        exit(); // This prevents any products from being loaded below
    }
    
    // Get PayPal Client ID for frontend
    $paypalConfig = PayPalConfig::getInstance();
    $paypalClientId = $paypalConfig->getClientId();
    $paypalMode = $paypalConfig->getMode();

    // Get user data for checkout form
    $user = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT username, email, phone, address FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
    
    require_once __DIR__ . '/../../../app/views/includes/header.php';
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EMPIRE - E-Commerce Store</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Bootstrap 5.3.0 CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

        <!-- PayPal SDK -->
        <script src="https://www.paypal.com/sdk/js?client-id=<?= htmlspecialchars($paypalClientId) ?>&currency=<?= htmlspecialchars($paypalConfig->getCurrency()) ?>"></script>

        <link rel="stylesheet" href="../../../public/css/shop.css">

        <style>
            .payment-method-card {
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                padding: 15px;
                margin-bottom: 15px;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .payment-method-card:hover {
                border-color: #0070ba;
                box-shadow: 0 4px 12px rgba(0, 112, 186, 0.1);
            }
            .payment-method-card.selected {
                border-color: #0070ba;
                background-color: #f0f7ff;
            }
            .payment-method-card input[type="radio"] {
                width: 18px;
                height: 18px;
                margin-right: 12px;
            }
            .payment-icon {
                font-size: 1.8rem;
                margin-right: 12px;
            }
            #paypal-button-container {
                margin-top: 15px;
                display: none;
                min-height: 45px;
            }
            .checkout-item {
                border-bottom: 1px solid #dee2e6;
                padding: 10px 0;
            }
            .checkout-item:last-child {
                border-bottom: none;
            }
        </style>
    </head>

    <body>

    <!-- Main Content -->
    <div class="container">
        <!-- Bootstrap Carousel with Auto-scroll (No Side Arrows) -->
        <div id="heroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel" data-bs-interval="5000">
            <!-- Carousel Indicators -->
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
            </div>

            <!-- Carousel Slides -->
            <div class="carousel-inner">
                <!-- Slide 1 - Replace with your image -->
                <div class="carousel-item active" style="background-image: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 text-center">
                                    <h1 class="hero-title">StreetWear Collection 2025</h1>
                                    <p class="hero-subtitle">Discover the latest trends and find your unique style with our exclusive StreetWear collection</p>
                                    <a href="#products" class="hero-btn">
                                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 - Replace with your image -->
                <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1469334031218-e382a71b716b?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 text-center">
                                    <h1 class="hero-title">Urban Essentials</h1>
                                    <p class="hero-subtitle">Premium quality meets street culture. Elevate your wardrobe with our handpicked urban essentials</p>
                                    <a href="#products" class="hero-btn">
                                        <i class="fas fa-tshirt me-2"></i>Explore Collection
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 - Replace with your image -->
                <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1434389677669-e08b4cac3105?ixlib=rb-4.0.3&auto=format&fit=crop&w=2105&q=80');">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 text-center">
                                    <h1 class="hero-title">Limited Edition Drops</h1>
                                    <p class="hero-subtitle">Exclusive releases you won't find anywhere else. Be part of the culture, own the streets</p>
                                    <a href="#products" class="hero-btn">
                                        <i class="fas fa-star me-2"></i>Get Exclusive Access
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 4 - Replace with your image -->
                <div class="carousel-item" style="background-image: url('https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80');">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 text-center">
                                    <h1 class="hero-title">Fresh Streetstyle</h1>
                                    <p class="hero-subtitle">From underground to mainstream. Express your individuality with our bold streetwear designs</p>
                                    <a href="#products" class="hero-btn">
                                        <i class="fas fa-fire me-2"></i>Shop Fresh Styles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
            <!-- Products Section -->
            <section id="products" class="products">
                <div class="shop-controls">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search for products...">
                    </div>
                    <div class="filters">
                        <select class="filter-select">
                            <option>Default Sorting</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <h2 class="fw-bold display-7 mb-4 text-center">On Sales Products</h2>
                <div class="products-grid" id="productsGrid">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card" data-category="<?= $product['category_id'] ?>" data-price="<?= $product['price'] ?>">
                                    <div class="product-badge">
                                        <span class="badge sale-badge">ON SALE</span>
                                        <?php if ($product['stock'] < 10): ?>
                                            <span class="badge low-stock">LOW STOCK</span>
                                        <?php endif; ?>
                                    </div>
                                        
                                        <img src="../../../public/uploads/<?= htmlspecialchars($product['image']) ?>"  
                                            alt="<?= htmlspecialchars($product['name']) ?>" 
                                            class="product-image clickable-image"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#productDetailsModal"
                                            data-product-id="<?= $product['id'] ?>"
                                            data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                            data-product-description="<?= htmlspecialchars($product['description']) ?>"
                                            data-product-price="<?= $product['price'] ?>"
                                            data-product-stock="<?= $product['stock'] ?>"
                                            data-product-category="<?= htmlspecialchars($product['category_name'] ?? 'Other') ?>"
                                            data-product-image="../../../public/uploads/<?= htmlspecialchars($product['image']) ?>"
                                            data-product-sizes="<?= htmlspecialchars($product['sizes'] ?? '') ?>"
                                            data-product-original-price="<?= isset($product['original_price']) ? $product['original_price'] : '' ?>">
                                        
                                        <div class="product-info">
                                            <p class="product-category">
                                                <i class="<?= htmlspecialchars($product['category_icon'] ?? 'fas fa-box') ?>"></i>
                                                <?= htmlspecialchars($product['category_name'] ?? "Other") ?>
                                            </p>
                                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                            <div class="product-meta">
                                                <div class="product-price sale-price">
                                                    <span class="current-price">₱<?= number_format($product['price'], 2) ?></span>
                                                    <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                                        <span class="original-price">₱<?= number_format($product['original_price'], 2) ?></span>
                                                        <span class="discount-percent">
                                                            <?= round((($product['original_price'] - $product['price']) / $product['original_price']) * 100) ?>% OFF
                                                        </span>
                                                    <?php else: ?>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="product-stock">
                                                    <i class="fas fa-box"></i> <?= $product['stock'] ?> in stock
                                                </p>
                                            </div>
                                            <div class="product-actions">
                                                <button class="add-to-cart sale-cart-btn" data-product-id="<?= $product['id'] ?>" 
                                                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                                    data-product-price="<?= $product['price'] ?>"
                                                    data-product-image="../../../public/uploads/<?= htmlspecialchars($product['image']) ?>"
                                                    data-product-original-price="<?= isset($product['original_price']) ? $product['original_price'] : $product['price'] ?>">
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                                <button class="wishlist sale-wishlist">
                                                    <i class="far fa-heart"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-products">
                                    <i class="fas fa-box-open"></i>
                                    <h3>No products found</h3>
                                    <p>Try adjusting your filters or search terms</p>
                                </div>
                            <?php endif; ?>
                        </div>
            </section>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-white text-dark border-0">
                <div class="modal-header  border-0">
                    <h5 class="modal-title text-white fw-bold" id="productModalTitle">Product Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-white">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="modalProductImage" src="" alt="" class="img-fluid rounded border" style="max-height: 400px; width: 100%; object-fit: cover;">
                            <div class="mt-3" id="modalAltImages">
                                <!-- Alternative images will go here -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3 id="modalProductName" class="fw-bold text-dark mb-2"></h3>
                            <p class="text-muted mb-3" id="modalProductCategory"></p>
                            <p id="modalProductDescription" class="mb-4 text-dark"></p>
                            
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="card border">
                                        <div class="card-body py-2 px-3">
                                            <small class="text-muted">Price</small>
                                            <div id="modalProductPrice" class="text-success fw-bold fs-5"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card border">
                                        <div class="card-body py-2 px-3">
                                            <small class="text-muted">Stock</small>
                                            <div id="modalProductStock" class="fw-bold text-dark"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="fw-semibold text-dark mb-2">Available Sizes:</p>
                                <div id="modalProductSizes" class="mt-2">
                                    <!-- Sizes will go here -->
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-dark btn-lg fw-semibold">
                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                </button>
                                <button class="btn btn-outline-dark btn-lg">
                                    <i class="far fa-heart me-2"></i>Add to Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- UPDATED Checkout Modal with PayPal Integration -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="checkoutModalLabel">
                        <i class="fas fa-shopping-cart me-2"></i>Checkout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="checkoutForm" method="POST" action="../../../app/controllers/OrderController.php">
                    <input type="hidden" name="action" value="placeOrder">
                    <input type="hidden" name="payment_method" id="selected_payment_method" value="Cash on Delivery (COD)">
                    <input type="hidden" name="paypal_order_id" id="paypal_order_id" value="">
                    <input type="hidden" name="payment_status" id="payment_status" value="pending">
                    
                    <div class="modal-body">
                        <!-- Order Summary -->
                        <div class="checkout-summary mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-receipt me-2"></i>Order Summary
                            </h6>
                            <div id="checkoutItems" class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                <!-- Cart items will be populated here -->
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Total Amount:</h5>
                                <h5 class="text-success mb-0" id="checkoutTotalDisplay">₱0.00</h5>
                            </div>
                        </div>

                        <!-- Shipping Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-truck me-2"></i>Shipping Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user me-1 text-muted"></i>Full Name
                                    </label>
                                    <input type="text" 
                                        class="form-control bg-light" 
                                        name="full_name" 
                                        value="<?= htmlspecialchars($user['username'] ?? '') ?>" 
                                        readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-envelope me-1 text-muted"></i>Email Address
                                    </label>
                                    <input type="email" 
                                        class="form-control bg-light" 
                                        name="email" 
                                        value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                        readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>Shipping Address
                                    </label>
                                    <textarea class="form-control bg-light" 
                                            name="shipping_address" 
                                            rows="3" 
                                            readonly><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-credit-card me-2"></i>Payment Method
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Cash on Delivery Option -->
                                <div class="payment-method-card selected" data-payment="cod">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" name="payment_option" value="cod" id="payment_cod" checked>
                                        <div class="payment-icon text-success">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div>
                                            <label for="payment_cod" class="mb-0 fw-bold">Cash on Delivery (COD)</label>
                                            <p class="text-muted small mb-0">Pay when you receive your order</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- PayPal Option -->
                                <div class="payment-method-card" data-payment="paypal">
                                    <div class="d-flex align-items-center">
                                        <input type="radio" name="payment_option" value="paypal" id="payment_paypal">
                                        <div class="payment-icon" style="color: #0070ba;">
                                            <i class="fab fa-paypal"></i>
                                        </div>
                                        <div>
                                            <label for="payment_paypal" class="mb-0 fw-bold">PayPal</label>
                                            <p class="text-muted small mb-0">Pay securely with PayPal</p>
                                            <?php if ($paypalMode === 'sandbox'): ?>
                                            <span class="badge bg-warning text-dark">Sandbox Mode</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- PayPal Button Container -->
                                <div id="paypal-button-container"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <div class="w-100">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-shield-alt me-1"></i>Secure checkout process
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-truck me-1"></i>Free delivery available
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <button type="submit" class="btn btn-success px-4" id="place-order-btn">
                                        <i class="fas fa-check me-1"></i>Place Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Login Success Modal -->
    <div class="modal fade" id="loginSuccessModal" tabindex="-1" aria-labelledby="loginSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-0 text-center position-relative">
                    <div class="w-100">
                        <div class="success-animation mb-3">
                            <i class="fas fa-user-check modal-icon text-success"></i>
                            <div class="success-ripple"></div>
                        </div>
                        <h5 class="modal-title fw-bold text-dark" id="loginSuccessModalLabel">Welcome Back!</h5>
                    </div>
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center px-4 pb-4">
                    <p class="mb-3 text-muted">You have successfully logged in to your account.</p>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../../app/views/includes/shop_footer.php';?>

    <!-- Bootstrap 5.3.0 JS (with Popper) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../../public/js/pagess.js"></script>
    <script src="../../../public/js/on-sale.js"></script>

    <!-- PayPal Integration Script -->
    <script>
        // ─── Payment method toggle ────────────────────────────────────────────────
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;

                if (this.dataset.payment === 'cod') {
                    document.getElementById('selected_payment_method').value = 'Cash on Delivery (COD)';
                    document.getElementById('paypal-button-container').style.display = 'none';
                    document.getElementById('place-order-btn').style.display = 'block';
                } else if (this.dataset.payment === 'paypal') {
                    document.getElementById('selected_payment_method').value = 'PayPal';
                    document.getElementById('paypal-button-container').style.display = 'block';
                    document.getElementById('place-order-btn').style.display = 'none';
                }
            });
        });

        function buildPayPalPayload() {
            let cartData = (window.cartManager && window.cartManager.cart && window.cartManager.cart.length > 0)
                ? window.cartManager.cart
                : (() => { try { return JSON.parse(localStorage.getItem('shopping_cart') || '[]'); } catch(e){ return []; } })();

            if (!cartData || cartData.length === 0) {
                try { cartData = JSON.parse(localStorage.getItem('cart') || '[]'); } catch(e) { cartData = []; }
            }

            if (!cartData || cartData.length === 0) return null;

            const items = [];
            let computedTotal = 0;

            cartData.forEach(item => {
                const unitPrice = parseFloat(item.price);
                const qty       = parseInt(item.quantity, 10);
                computedTotal  += unitPrice * qty;

                items.push({
                    name:        String(item.name).substring(0, 127),
                    description: ('Size: ' + (item.size || 'N/A')).substring(0, 127),
                    quantity:    qty,
                    price:       unitPrice
                });
            });

            computedTotal = Math.round(computedTotal * 100) / 100;
            return { items, total: computedTotal, cartData };
        }

        function showPayPalResultModal(success, message) {
            let modalId = 'paypalResultModal';
            let el = document.getElementById(modalId);
            if (!el) {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                <div class="modal fade" id="${modalId}" tabindex="-1">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                      <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="${modalId}Title"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body text-center px-4 pb-4">
                        <div id="${modalId}Icon" class="display-3 mb-3"></div>
                        <p id="${modalId}Msg" class="text-muted"></p>
                      </div>
                      <div class="modal-footer border-0 justify-content-center">
                        <button class="btn btn-dark px-4" data-bs-dismiss="modal">OK</button>
                      </div>
                    </div>
                  </div>
                </div>`;
                document.body.appendChild(wrapper.firstElementChild);
                el = document.getElementById(modalId);
            }
            el.querySelector(`#${modalId}Title`).textContent = success ? 'Payment Successful!' : 'Payment Failed';
            el.querySelector(`#${modalId}Icon`).innerHTML    = success
                ? '<i class="fas fa-check-circle text-success"></i>'
                : '<i class="fas fa-times-circle text-danger"></i>';
            el.querySelector(`#${modalId}Msg`).textContent   = message;
            new bootstrap.Modal(el).show();
        }

        // ─── PayPal Buttons ───────────────────────────────────────────────────────
        paypal.Buttons({
            createOrder: function (data, actions) {
                const payload = buildPayPalPayload();

                if (!payload) {
                    showPayPalResultModal(false, 'Your cart is empty. Please add items before paying.');
                    return Promise.reject(new Error('Empty cart'));
                }

                const orderItems = payload.items.map(item => ({
                    name:        item.name,
                    description: item.description,
                    quantity:    String(item.quantity),
                    unit_amount: {
                        currency_code: 'PHP',
                        value:         item.price.toFixed(2)
                    },
                    category: 'PHYSICAL_GOODS'
                }));

                return actions.order.create({
                    purchase_units: [{
                        description: 'Empire Streetwear Purchase',
                        amount: {
                            currency_code: 'PHP',
                            value: payload.total.toFixed(2),
                            breakdown: {
                                item_total: {
                                    currency_code: 'PHP',
                                    value: payload.total.toFixed(2)
                                }
                            }
                        },
                        items: orderItems
                    }],
                    application_context: {
                        brand_name:  'EMPIRE STREETWEAR',
                        user_action: 'PAY_NOW'
                    }
                });
            },

            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    const payerName = (details.payer && details.payer.name)
                        ? details.payer.name.given_name + ' ' + (details.payer.name.surname || '')
                        : 'Customer';

                    const form    = document.getElementById('checkoutForm');
                    const address = form.querySelector('[name="shipping_address"]').value;
                    const payload = buildPayPalPayload();

                    if (!payload) {
                        showPayPalResultModal(false, 'Cart data lost after payment. Contact support with PayPal ID: ' + data.orderID);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('total_amount',           payload.total.toFixed(2));
                    formData.append('shipping_address',       address);
                    formData.append('payment_method',         'PayPal');
                    formData.append('cart',                   JSON.stringify(payload.cartData));
                    formData.append('paypal_order_id',        data.orderID);
                    formData.append('payment_status',         'paid');
                    formData.append('action',                 'placeOrder');
                    formData.append('paypal_capture_details', JSON.stringify(details));

                    fetch('../../../app/controllers/OrderController.php?action=placeOrder', {
                        method: 'POST',
                        body:   formData
                    })
                    .then(r => r.json())
                    .then(result => {
                        if (result.success) {
                            if (window.cartManager) {
                                window.cartManager.cart = [];
                                window.cartManager.saveCartToStorage();
                                window.cartManager.updateCartDisplay();
                            }
                            localStorage.removeItem('shopping_cart');
                            localStorage.removeItem('cart');

                            const checkoutModal = bootstrap.Modal.getInstance(
                                document.getElementById('checkoutModal')
                            );
                            if (checkoutModal) checkoutModal.hide();

                            showPayPalResultModal(
                                true,
                                'Thank you, ' + payerName + '! Your order ' +
                                (result.order_id ? '#' + result.order_id : '') + ' has been placed.'
                            );

                            document.getElementById('paypalResultModal')
                                .addEventListener('hidden.bs.modal', () => window.location.reload(), { once: true });
                        } else {
                            showPayPalResultModal(
                                false,
                                'Payment received but order save failed: ' +
                                (result.message || 'Unknown error. Contact support.')
                            );
                        }
                    })
                    .catch(err => {
                        console.error('Order save error:', err);
                        showPayPalResultModal(
                            false,
                            'Payment received but order not saved. Contact support with PayPal order ID: ' + data.orderID
                        );
                    });
                });
            },

            onCancel: function () {
                showPayPalResultModal(false, 'Payment cancelled. Your cart is still intact.');
            },

            onError: function (err) {
                console.error('PayPal error:', err);
                showPayPalResultModal(false, 'A PayPal error occurred. Please try again or choose Cash on Delivery.');
            }

        }).render('#paypal-button-container');
    </script>

    
    </body>
    </html>