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

        <link rel="stylesheet" href="../../../public/css/shop.css">
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
                            <option>All Categories</option>
                            <?php foreach ($categoryIcons as $id => $info): ?>
                                <option><?= htmlspecialchars($info[0]) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select class="filter-select">
                            <option>Default Sorting</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Most Popular</option>
                            <option>Newest First</option>
                        </select>
                    </div>
                </div>

                <h2 class="fw-bold display-7 mb-4 text-center">Featured Products</h2>
                <div class="products-grid" id="productsGrid">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <div class="product-card" data-category="<?= $product['category_id'] ?>" data-price="<?= $product['price'] ?>">
                                        <div class="product-badge">
                                            <?php if ($product['stock'] < 10): ?>
                                                <span class="badge low-stock">Low Stock</span>
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
            </section>
    </div>

    



    
        <?php require_once '../../../app/views/includes/shop_footer.php';?>

        <!-- Bootstrap 5.3.0 JS (with Popper) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="../../../public/js/pages.js"></script>

    



    
    </body>
    </html>
