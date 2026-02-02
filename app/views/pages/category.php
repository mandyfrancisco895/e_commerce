<?php

    require_once __DIR__ . '/../../../config/session_checker.php';
    require_once __DIR__ . '/../../../config/dbcon.php';

    require_once __DIR__ . '/../../../app/controllers/ProductController.php';
    require_once __DIR__ . '/../../../app/controllers/NotificationController.php';

    require_once __DIR__ . '/../../../app/models/Product.php';
    require_once __DIR__ . '/../../../app/controllers/CategoryController.php';
    require_once __DIR__ . '/../../../app/models/Category.php';

    $database = new Database();
    $db = $database->getConnection();

    $maintStmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode'");
    $isMaint = $maintStmt->fetchColumn();
    $userRole = $_SESSION['role'] ?? 'guest';

    $categoryController = new CategoryController($db);
    $productController  = new ProductController($db);
    $dbCategories = $categoryController->index(); 
    $products = $productController->indexActive();


    $categoryIcons = [];
    foreach ($dbCategories as $cat) {
        $categoryIcons[$cat['id']] = [
            $cat['name'],
            $cat['icon'] ?? 'fas fa-box' // fallback icon
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
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

        <link rel="stylesheet" href="../../../public/css/categoryy.css">
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
            <!-- Categories Section -->
            <section class="categories">
                <h2 class="section-title">Shop by Category</h2>
                <div class="categories-grid">
                    <?php foreach ($categories as $catId => $catProducts): 
                        $catName = $categoryIcons[$catId][0] ?? "Other";
                        $catIcon = $categoryIcons[$catId][1] ?? "fas fa-box";
                        $count   = count($catProducts);        
                    ?>
                        <div class="category-card">
                            <div class="category-icon">
                                <i class="<?= $catIcon ?>"></i>
                            </div>
                            <h3 class="category-title"><?= htmlspecialchars($catName) ?></h3>
                            <p class="category-count"><?= $count ?> products</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Products Section with Sidebar -->
            <section id="products" class="products">
                <!-- Mobile filter toggle button -->
                <button class="mobile-filter-toggle d-lg-none" id="mobileFilterToggle">
                    <i class="fas fa-filter"></i> Filters
                </button>

                <div class="shop-layout">
                    <!-- Sidebar -->
                    <aside class="shop-sidebar" id="shopSidebar">
                        <div class="sidebar-header">
                            <h3><i class="fas fa-sliders-h"></i> Filters</h3>
                            <button class="close-sidebar d-lg-none" id="closeSidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Search in Sidebar -->
                        <div class="sidebar-section">
                            <h4>Search Products</h4>
                            <div class="search-box">
                                <i class="fas fa-search"></i>
                                <input type="text" placeholder="Search for products...">
                            </div>
                        </div>

                        <!-- Categories Filter -->
                        <div class="sidebar-section">
                            <h4>Categories</h4>
                            <div class="filter-group">
                                <div class="filter-option">
                                    <input type="radio" id="all-categories" name="category" value="all" checked>
                                    <label for="all-categories">
                                        <i class="fas fa-th-large"></i> All Categories
                                        <span class="count"><?= count($products) ?></span>
                                    </label>
                                </div>
                                <?php foreach ($categoryIcons as $id => $info): ?>
                                    <?php $categoryCount = isset($categories[$id]) ? count($categories[$id]) : 0; ?>
                                    <?php if ($categoryCount > 0): ?>
                                        <div class="filter-option">
                                            <input type="radio" id="cat-<?= $id ?>" name="category" value="<?= $id ?>">
                                            <label for="cat-<?= $id ?>">
                                                <i class="<?= $info[1] ?>"></i> <?= htmlspecialchars($info[0]) ?>
                                                <span class="count"><?= $categoryCount ?></span>
                                            </label>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="sidebar-section">
                            <h4>Price Range</h4>
                            <div class="price-range">
                                <div class="price-inputs">
                                    <div class="price-input">
                                        <label>Min</label>
                                        <input type="number" placeholder="0" id="minPrice">
                                    </div>
                                    <div class="price-divider">-</div>
                                    <div class="price-input">
                                        <label>Max</label>
                                        <input type="number" placeholder="10000" id="maxPrice">
                                    </div>
                                </div>
                                <button class="apply-price-btn">Apply</button>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div class="sidebar-section">
                            <h4>Sort By</h4>
                            <select class="filter-select" id="sortBy">
                                <option value="default">Default Sorting</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                                <option value="popular">Most Popular</option>
                                <option value="name-az">Name: A to Z</option>
                                <option value="name-za">Name: Z to A</option>
                            </select>
                        </div>

                        <!-- Quick Filters -->
                        <div class="sidebar-section">
                            <h4>Quick Filters</h4>
                            <div class="quick-filters">
                                <div class="quick-filter-tag">
                                    <input type="checkbox" id="in-stock">
                                    <label for="in-stock">In Stock</label>
                                </div>
                                <div class="quick-filter-tag">
                                    <input type="checkbox" id="on-sale">
                                    <label for="on-sale">On Sale</label>
                                </div>
                                <div class="quick-filter-tag">
                                    <input type="checkbox" id="new-arrival">
                                    <label for="new-arrival">New Arrivals</label>
                                </div>
                            </div>
                        </div>

                        <!-- Clear Filters -->
                        <div class="sidebar-section">
                            <button class="clear-filters-btn">
                                <i class="fas fa-refresh"></i> Clear All Filters
                            </button>
                        </div>
                    </aside>

                    <!-- Main Products Area -->
                    <main class="shop-main">
                        <!-- Results Header -->
                        <div class="results-header">
                            <div class="results-info">
                                <span class="results-count">Showing <?= count($products) ?> products</span>
                            </div>
                            <div class="view-toggle">
                                <button class="view-btn active" data-view="grid" title="Grid View">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button class="view-btn" data-view="list" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Products Grid -->
                        <div class="products-grid" id="productsGrid">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card" data-category="<?= $product['category_id'] ?>" data-price="<?= $product['price'] ?>">
                                        <div class="product-badge">
                                            <?php if (isset($product['category_name']) && $product['category_name'] === 'On Sale'): ?>
                                                <span class="badge sale-badge">ON SALE</span>
                                            <?php elseif (isset($product['category_name']) && $product['category_name'] === 'New Arrivals'): ?>
                                                <span class="badge new-arrivals-badge">NEW ARRIVAL</span>
                                            <?php endif; ?>
                                            
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
                                            data-product-sizes="<?= htmlspecialchars($product['sizes'] ?? '') ?>">
                                        
                                        <div class="product-info">
                                            <p class="product-category">
                                                <i class="<?= htmlspecialchars($product['category_icon'] ?? 'fas fa-box') ?>"></i>
                                                <?= htmlspecialchars($product['category_name'] ?? "Other") ?>
                                            </p>
                                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                            <div class="product-meta">
                                                <p class="product-price">₱<?= number_format($product['price'], 2) ?></p>
                                                <p class="product-stock">
                                                    <i class="fas fa-box"></i> <?= $product['stock'] ?> in stock
                                                </p>
                                            </div>
                                            <div class="product-actions">
                                                <button class="add-to-cart" data-product-id="<?= $product['id'] ?>" 
                                                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                                    data-product-price="<?= $product['price'] ?>"
                                                    data-product-image="../../../public/uploads/<?= htmlspecialchars($product['image']) ?>">
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                                <button class="wishlist">
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

                        <!-- Pagination 
                        <div class="pagination-wrapper">
                            <nav aria-label="Products pagination">
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        -->
                    </main>
                </div>
            </section>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <br>
    <br>

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

    <!-- Updated Checkout Modal with Read-only Fields -->
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="checkoutForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-credit-card me-2"></i>Checkout
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Order Summary Column -->
                            <div class="col-md-6">
                                <div class="h6 mb-3">
                                    <i class="fas fa-receipt me-2 text-primary"></i>Order Summary
                                </div>
                                <div id="checkoutItems" class="border rounded p-3 mb-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Cart items will be populated here -->
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                                    <span class="h6 mb-0">Total Amount:</span>
                                    <span class="h5 mb-0 text-success" id="checkoutTotalDisplay">₱0.00</span>
                                </div>
                            </div>
                            
                            <!-- Customer Information Column -->
                            <div class="col-md-6">
                                <div class="h6 mb-3">
                                    <i class="fas fa-user me-2 text-primary"></i>Customer Information
                                </div>
                                
                                <!-- Full Name Field -->
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
                                
                                <!-- Phone Number Field -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-phone me-1 text-muted"></i>Phone Number
                                    </label>
                                    <input type="tel" 
                                        class="form-control bg-light" 
                                        name="phone" 
                                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                        readonly>
                                </div>
                                
                                <!-- Address Field -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>Delivery Address
                                    </label>
                                    <textarea class="form-control bg-light" 
                                            name="shipping_address" 
                                            rows="3" 
                                            readonly><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                                
                                <!-- Payment Method Field -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-money-bill me-1 text-muted"></i>Payment Method
                                    </label>
                                    <input type="text" 
                                        class="form-control bg-light" 
                                        name="payment_method" 
                                        value="Cash on Delivery (COD)" 
                                        readonly>
                                </div>
                                
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
                                    <button type="submit" class="btn btn-success px-4">
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


    <?php require_once '../../../app/views/includes/shop_footer.php';?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../../../public/js/pagess.js"></script>
    <script src="../../../public/js/categoryy.js"></script>





    

    </body>
    </html>