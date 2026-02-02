<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/../config/dbcon.php';
require_once __DIR__ . '/../config/constants.php';

// Get the requested page from URL parameter
$page = $_GET['page'] ?? 'home';

// Handle different pages
switch($page) {
    case 'login':
        include __DIR__ . '/../app/views/auth/login.php';
        exit;
    case 'register':
        include __DIR__ . '/../app/views/auth/register.php';
        exit;
    case 'home':
    default:
        // Include navbar for home page
        require_once __DIR__ . '/../app/views/includes/navbar.php';
        // Rest of your home page content...
        break;
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMPIRE - Premium Street Wear Collection | Urban Fashion & Style</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Discover premium street wear fashion at StreetFit. Shop the latest urban clothing, sneakers, and accessories. Free shipping on orders over $100. Express your style with our curated collection.">
    <meta name="keywords" content="street wear, urban fashion, streetwear clothing, hip hop fashion, sneakers, urban style, street style, fashion, clothing, trendy outfits">
    <meta name="author" content="Empire">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="EMPIRE - Premium Street Wear Collection">
    <meta property="og:description" content="Discover premium street wear fashion. Shop the latest urban clothing and accessories.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="EMPIRE">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 
    
    <!-- AOS CSS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="css/index.css">


    
</head>
        <body>

            <!-- New Hero Section -->
            <section class="hero">
                <div class="hero-container" data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="100" data-aos-offset="0">
                <!-- Text stays fixed -->
                <div class="hero-content">
                    <h1>Street Style Redefined</h1>
                    <p>Discover the latest in urban fashion with our curated collection of premium street wear.</p>
                    <div class="hero-buttons">
                    <a href="#arrivals" class="btn btn-primary">Shop Now</a>
                    <a href="#story" class="btn btn-outline">Learn More</a>
                    </div>
                </div>
            
                <!-- Only images slide -->
                <div class="hero-image">
                    <div class="swiper hero-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                        <img src="../public/zassets/images/hero1.jpg" alt="Streetwear Look" class="hero-photo">
                        </div>
                        <div class="swiper-slide">
                        <img src="../public/assets/images/hero2.jpg" alt="Streetwear Outfit" class="hero-photo">
                        </div>
                        <div class="swiper-slide">
                        <img src="../public/assets/images/hero3.jpg" alt="Urban Fashion" class="hero-photo">
                        </div>
                        <div class="swiper-slide">
                            <img src="../public/assets/images/hero4.jpg" alt="Urban Fashion" class="hero-photo">
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </section>

            <!-- Brand Collaborations Section -->
            <section class="brand-collaborate">
                <div class="container">
                <div class="brands-slider">
                    <div class="brands-track">
                    <img src="../public/assets/images/brand1.jpg" alt="Brand Partner 1" />
                    <img src="../public/assets/images/brand2.jpg" alt="Brand Partner 2" />
                    <img src="../public/assets/images/brand3.jpg" alt="Brand Partner 3" />
                    <img src="../public/assets/images/brand4.jpg" alt="Brand Partner 4" />
                    <img src="../public/assets/images/brand5.jpg" alt="Brand Partner 5" />
                    <img src="../public/assets/images/brand6.jpg" alt="Brand Partner 6" />
                    <!-- Duplicate for seamless infinite scroll -->
                    <img src="../public/assets/images/brand1.jpg" alt="Brand Partner 1" />
                    <img src="../public/assets/images/brand2.jpg" alt="Brand Partner 2" />
                    <img src="../public/assets/images/brand3.jpg" alt="Brand Partner 3" />
                    <img src="../public/assets/images/brand4.jpg" alt="Brand Partner 4" />
                    <img src="../public/assets/images/brand5.jpg" alt="Brand Partner 5" />
                    <img src="../public/assets/images/brand6.jpg" alt="Brand Partner 6" />
                    </div>
                </div>
                </div>
            </section>

            <!-- Featured Collection -->
            <section id="collections" class="featured-collection">
                <div class="container">
                <h2 class="section-title">Featured Collection</h2>
                <p class="section-subtitle">Born from the streets, crafted for the culture. We believe fashion is a form of self-expression.</p>
            
                <div class="content-wrapper">
                    <!-- Left Sidebar Filters -->
                    <div class="filters-sidebar" id="filtersSidebar">
                    <h3 class="filters-title">Filters</h3>
                    
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">Category</h4>
                        <div class="category-filters">
                        <div class="category-filter active" onclick="selectCategory(this, 'all')">
                            <input type="radio" name="category" value="all" checked>
                            <label>All Products</label>
                        </div>
                        <div class="category-filter" onclick="selectCategory(this, 'clothing')">
                            <input type="radio" name="category" value="clothing">
                            <label>Clothing</label>
                        </div>
                        <div class="category-filter" onclick="selectCategory(this, 'accessories')">
                            <input type="radio" name="category" value="accessories">
                            <label>Accessories</label>
                        </div>
                        <div class="category-filter" onclick="selectCategory(this, 'outerwear')">
                            <input type="radio" name="category" value="outerwear">
                            <label>Outerwear</label>
                        </div>
                        </div>
                    </div>
            
                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">Price Range</h4>
                        <div class="price-range-container">
                        <div class="price-inputs">
                            <input type="number" class="price-input" id="minPrice" placeholder="Min" value="0" min="0" max="200">
                            <input type="number" class="price-input" id="maxPrice" placeholder="Max" value="200" min="0" max="200">
                        </div>
                        <div class="price-slider-container">
                            <input type="range" class="price-slider" id="priceSlider" min="0" max="200" value="200">
                        </div>
                        <div class="price-range-display" id="priceDisplay">
                            $0 - $200
                        </div>
                        </div>
                    </div>
            
                    <button class="clear-filters" onclick="clearAllFilters()">
                        Clear All Filters
                    </button>
                    </div>
            
                    <!-- Products Container -->
                    <div class="products-container">
                    <div class="products-header">
                        <div class="results-count" id="resultsCount">
                        Showing 6 products
                        </div>
                        <select class="sort-dropdown" id="sortDropdown" onchange="sortProducts()">
                        <option value="default">Sort by: Featured</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name">Name: A to Z</option>
                        </select>
                    </div>
            
                    <div class="featured-grid">
                        <!-- Product Cards -->
                        <div class="product-card" data-category="clothing" data-price="89" data-name="Urban Oversized Hoodie">
                        <div class="product-image">
                            <img src="../public/assets/images/f1.jpg" alt="Urban Oversized Hoodie"   class="clickable-img"  data-large="../public/assets/images/al1.jpg">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">LeoPard Sleeves</h3>
                            <div class="product-price">500</div>
                            <p class="product-description">Premium cotton blend with embroidered logo. The perfect statement piece for the streets.</p>
                            <span class="product-category">Clothing</span>
                        </div>
                        </div>
            
                        <div class="product-card" data-category="accessories" data-price="35" data-name="Classic Cap">
                        <div class="product-image">
                            <img src="../public/assets/images/f2.jpg" alt="Classic Cap" class="clickable-img"  data-large="../public/assets/images/al2.jpg">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Real Tree Pants</h3>
                            <div class="product-price">₱400</div>
                            <p class="product-description">Timeless design with modern materials. Perfect for any street style look.</p>
                            <span class="product-category">Accessories</span>
                        </div>
                        </div>
            
                        <div class="product-card" data-category="outerwear" data-price="129" data-name="Urban Jacket">
                        <div class="product-image">
                            <img src="../public/assets/images/f3.jpg" alt="Urban Jacket" class="clickable-img"  data-large="../public/assets/images/al3.jpg">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Real Tree Shirt</h3>
                            <div class="product-price">₱200</div>
                            <p class="product-description">Weather-resistant with street-ready style. Your go-to outer layer.</p>
                            <span class="product-category">Outerwear</span>
                        </div>
                        </div>
            
                        <div class="product-card" data-category="clothing" data-price="65" data-name="Street Graphic Tee">
                        <div class="product-image">
                            <img src="../public/assets/images/f4.jpg" alt="Street Graphic Tee" class="clickable-img"  data-large="../public/assets/images/al4.jpg">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Street Graphic Tee</h3>
                            <div class="product-price">₱999</div>
                            <p class="product-description">Bold graphics on premium fabric. Express your urban style.</p>
                            <span class="product-category">Clothing</span>
                        </div>
                        </div>
            
                        <div class="product-card" data-category="accessories" data-price="45" data-name="Street Crossbody Bag">
                        <div class="product-image">
                            <img src="../public/assets/images/f5.jpg" alt="Street Crossbody Bag" class="clickable-img"  data-large="../public/assets/images/al5.jpg">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Fur Hoodie</h3>
                            <div class="product-price">₱900</div>
                            <p class="product-description">Functional and stylish. Perfect for your urban adventures.</p>
                            <span class="product-category">Accessories</span>
                        </div>
                        </div>
            
                        <div class="product-card" data-category="clothing" data-price="95" data-name="Urban Joggers">
                        <div class="product-image">
                            <img src="../public/assets/images/f6.jpg" alt="Urban Joggers" class="clickable-img"  data-large="../public/assets/images/al6.jpg">
                        </div>    
                        <div class="product-info">
                            <h3 class="product-name">Black Plain Hoodie</h3>
                            <div class="product-price">₱999</div>
                            <p class="product-description">Comfort meets style. Premium joggers for the modern streetwear enthusiast.</p>
                            <span class="product-category">Clothing</span>
                        </div>
                        </div>
            
                        <!-- No results message -->
                        <div class="no-results">
                        <h3>No products found</h3>
                        <p>Try adjusting your filter selection</p>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="features">
                <div class="container" data-aos="fade-down" data-aos-duration="1500">
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <h3>Free Shipping</h3>
                            <p>Free worldwide shipping on orders over $100. Get your style delivered fast.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-undo-alt"></i>
                            </div>
                            <h3>Easy Returns</h3>
                            <p>30-day hassle-free returns. Shop with confidence knowing we've got you covered.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3>Premium Quality</h3>
                            <p>Carefully curated pieces made from the finest materials for lasting style.</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Community</h3>
                            <p>Join a global community of style enthusiasts and street culture lovers.</p>
                        </div>
                    </div>
                </div>
            </section>


            <!-- Our Story Section -->
            <section id="story" class="our-story">
                <div class="container">
                    <h2 class="section-title">Our Story</h2>
                    <p class="section-subtitle">Born from the streets, crafted for the culture. We believe fashion is a form of self-expression.</p>
                    
                    <div class="story-content">
                        <div class="story-text">
                            <p>StreetFit was founded with a simple mission: to bring authentic street culture to fashion enthusiasts worldwide. Our journey began in the underground scenes of major cities, where style meets substance and every piece tells a story.</p>
                            
                            <p>We collaborate with emerging artists, designers, and cultural icons to create collections that resonate with the urban spirit. Each piece is carefully curated to ensure quality, authenticity, and that unmistakable street aesthetic.</p>
                            
                            <p>From limited edition drops to everyday essentials, we're committed to providing our community with fashion that speaks to who they are and where they're going.</p>
                        </div>
                        
                        <div class="story-image">
                            <div class="image-slider">
                                <div class="slide active">
                                    <img src="../public/assets/images/s1.jpg" alt="Urban Culture" />
                                    <div class="slide-content">
                                    </div>
                                </div>
                                
                                <div class="slide">
                                    <img src="../public/assets/images/s2.jpg" alt="Creative Design" />
                                    <div class="slide-content">
                                    </div>
                                </div>
                                
                                <div class="slide">
                                    <img src="../public/assets/images/s3.jpg" alt="Community" />
                                    <div class="slide-content">
                                    </div>
                                </div>
                                
                                <div class="slide">
                                    <img src="../public/assets/images/s4.jpg" alt="Premium Quality" />
                                    <div class="slide-content">
                                    </div>
                                </div>
                                
                                <div class="slide">
                                    <img src="../public/assets/images/s3.jpg" alt="Limited Drops" />
                                    <div class="slide-content">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress-bar">
                                <div class="progress-dot active" data-slide="0"></div>
                                <div class="progress-dot" data-slide="1"></div>
                                <div class="progress-dot" data-slide="2"></div>
                                <div class="progress-dot" data-slide="3"></div>
                                <div class="progress-dot" data-slide="4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- New Arrivals Section -->
            <section id="arrivals" class="new-arrivals">
                <div class="container" data-aos="fade-left">
                <h2 class="section-title">New Arrivals</h2>
                <p class="section-subtitle">
                    Fresh drops from the latest collections. Limited quantities available.
                </p>
            
                <div class="products-grid">
                <!-- Product 1 -->
                <div class="product-card" data-category="clothing" data-price="45" data-name="Urban Graphic Tee" data-stock="in-stock" data-special="limited">
                    <div class="product-image">
                    <img src="../public/assets/images/p1.jpg" alt="Urban Graphic Tee" class="clickable-img"data-large="../public/assets/images/p1.jpg">
                    </div>
                    <div class="product-info">
                    <h3 class="product-name">Urban Graphic Tee</h3>
                    <div class="product-price">₱450</div>
                    <div class="stock-indicator in-stock">
                        <span class="stock-dot"></span>
                        <span>In Stock</span>
                    </div>
                    <p class="product-description">Premium cotton blend with exclusive street art design</p>
                    <span class="product-category">Clothing</span>
                    <br><br>
                    <a href="#" class="btn btn-primary add-to-cart">Add to Cart</a>
                    </div>
                </div>
                
                <!-- Product 2 -->
                <div class="product-card" data-category="clothing" data-price="89" data-name="Oversized Hoodie" data-stock="in-stock">
                    <div class="product-image">
                    <img src="../public/assets/images/p2.jpg" alt="Oversized Hoodie" class="clickable-img" data-large="../public/assets/images/p2.jpg">
                    </div>
                    <div class="product-info">
                    <h3 class="product-name">Oversized Hoodie</h3>
                    <div class="product-price">₱890</div>
                    <div class="stock-indicator in-stock">
                        <span class="stock-dot"></span>
                        <span>In Stock</span>
                    </div>
                    <p class="product-description">Soft fleece interior with bold front graphic</p>
                    <span class="product-category">Clothing</span>
                    <br><br>
                    <a href="#" class="btn btn-primary add-to-cart">Add to Cart</a>
                    </div>
                </div>
                
                <!-- Product 3 -->
                <div class="product-card" data-category="footwear" data-price="95" data-name="Canvas High-tops" data-stock="in-stock">
                    <div class="product-image">
                    <img src="../public/assets/images/p3.jpg" alt="Canvas High-tops" class="clickable-img" data-large="../public/assets/images/p3.jpg">
                    </div>
                    <div class="product-info">
                    <h3 class="product-name">Canvas High-tops</h3>
                    <div class="product-price">₱950</div>
                    <div class="stock-indicator in-stock">
                        <span class="stock-dot"></span>
                        <span>In Stock</span>
                    </div>
                    <p class="product-description">Classic silhouette with modern street appeal</p>
                    <span class="product-category">Footwear</span>
                    <br><br>
                    <a href="#" class="btn btn-primary add-to-cart">Add to Cart</a>
                    </div>
                </div>
            </section>

            <!-- Team Section -->
            <section id="team" class="team">
                <div class="container" data-aos="zoom-out-left">
                    <h2 class="section-title">Meet The Team</h2>
                    <p class="section-subtitle">The creative minds behind the brand, bringing street culture to life.</p>

                    <div class="team-grid">
                        <div class="team-member">
                            <div class="member-image">
                                <img src="../public/assets/images/gab.jpg" alt="James Duran - Creative Director">
                            </div>
                            <h3 class="member-name">Gabriel Vargas</h3>
                            <p class="member-role">Developer</p>
                            <div class="member-social">
                                <a href="https://www.facebook.com/share/14P8gYPKPbe/" class="social-link"><i class="fab fa-facebook"></i></a>
                                <a href="https://www.instagram.com/gabbyeer?igsh=NHk3YWNlYnQ3czI2" class="social-link"><i class="fab fa-instagram"></i></a>
                                
                            </div>
                        </div>
                        
                        <div class="team-member">
                            <div class="member-image">
                                <img src="../public/assets/images/james.jpg" alt="James Duran - Head Designer">
                            </div>
                            <h3 class="member-name">James Duran</h3>
                            <p class="member-role">QA Tester</p>
                            <div class="member-social">
                                <a href="https://www.facebook.com/share/19MktjikyQ/" class="social-link"><i class="fab fa-facebook"></i></a>
                                <a href="https://www.instagram.com/jamess.sssd?igsh=cW5wdzFoZGFnbmcw" class="social-link"><i class="fab fa-instagram"></i></a>
                                
                            </div>  
                        </div>
                        
                        <div class="team-member">
                            <div class="member-image">
                                <img src="../public/assets/images/renz.jpg" alt="James Duran - Brand Manager">
                            </div>
                            <h3 class="member-name">Renz Alvarez</h3>
                            <p class="member-role">Project Manager</p>
                            <div class="member-social">
                                <a href="https://www.facebook.com/share/1CE16FMyKA/" class="social-link"><i class="fab fa-facebook"></i></a>
                                <a href="igsh=cW5wdzFoZGFnbmcw
                                You sent
                                https://www.instagram.com/zznr.mk?igsh=aDFwNDlnYzhtZDAy" class="social-link"><i class="fab fa-instagram"></i></a>
                                
                            </div>
                        </div>
                        
                        <div class="team-member">
                            <div class="member-image">
                                <img src="../public/assets/images/mandy.jpg" alt="James Duran - Marketing Lead">
                            </div>
                            <h3 class="member-name">Mandy Francisco</h3>
                            <p class="member-role">UI/UX</p>
                            <div class="member-social">
                                <a href="https://www.facebook.com/share/1KsRoRbqJ7/" class="social-link"><i class="fab fa-facebook"></i></a>
                                <a href="https://www.instagram.com/mandyfrz_?igsh=N294ZWtjNmZ3am0z" class="social-link"><i class="fab fa-instagram"></i></a>
                            
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Newsletter Section -->
            <section class="newsletter">
                <div class="newsletter-container" div data-aos="fade-right">
                    <h2>Stay in the Loop</h2>
                    <p>Subscribe to our newsletter for the latest streetwear drops, exclusive offers, and style inspiration.</p>
                    <form class="newsletter-form" id="newsletterForm">
                        <div class="form-group">
                            <input type="email" placeholder="Enter your email" required id="emailInput">
                            <div class="error-message" id="errorMessage"></div>
                        </div>
                        <button type="submit" class="btn" id="submitBtn">Subscribe</button>
                    </form>
                </div>
            </section>

            <?php require_once '../app/views/includes/footer.php'; ?>

            <!-- AOS JS -->
            <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
            <script> AOS.init({
                duration: 2500,
                once: true,
                offset: 100
            });
            </script>

            <!-- Swiper JS -->
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

            <!-- Main JS -->
            <script src="js/indexs.js"></script>

        </body>
</html>