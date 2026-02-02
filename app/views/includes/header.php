<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/dbcon.php';

$database = new Database();
$db = $database->getConnection();

$user = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $db->prepare("SELECT id, username, email, phone, address, profile_pic, created_at, status  FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $profilePic = !empty($user['profile_pic']) 
            ? "../../../public/uploads/" . htmlspecialchars($user['profile_pic']) 
            : "../../../public/uploads/default-avatar.jpg";
    } catch (PDOException $e) {
        error_log("Header user fetch error: " . $e->getMessage());
        $profilePic = "../../../public/uploads/default-avatar.jpg";
    }
} else {
    $profilePic = "../../../public/uploads/default-avatar.jpg";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMPIRE. - E-Commerce Store</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    
    <!-- Custom CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

   /* FIXED BADGE STYLES */

/* Badge base styles - ONLY for header badges */
#headerCartBadge,
#headerWishlistBadge,
#headerOrdersBadge,
#headerNotificationBadge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    font-size: 11px;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
    border-radius: 50%;
    margin-left: 8px;
    flex-shrink: 0;
}

/* Individual badge colors */
#headerCartBadge {
    background-color: #dc3545;
    color: white;
}

#headerWishlistBadge {
    background-color: #e83e8c;
    color: white;
}

#headerOrdersBadge {
    background-color: #007bff;
    color: white;
}

#headerNotificationBadge {
    background-color: #28a745;
    color: white;
}

/* Pulse animation for notifications */
.badge-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { 
        transform: scale(1); 
        opacity: 1;
    }
    50% { 
        transform: scale(1.1); 
        opacity: 0.8;
    }
}

/* Dropdown item - NO badges inside */
.dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    text-decoration: none;
    color: #333;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
    position: relative;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #333;
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item i {
    margin-right: 10px;
    width: 16px;
    text-align: center;
    flex-shrink: 0;
}

.dropdown-item.logout {
    color: #dc3545;
}

.dropdown-item.logout:hover {
    background-color: #fff5f5;
}

/* Header and dropdown structure */
.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: #333333;
    text-decoration: none;
}

nav ul {
    display: flex;
    list-style: none;
    align-items: center;
    margin: 0;
    padding: 0;
}

nav ul li {
    margin: 0 1rem;
}

nav ul li a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    padding: 0.5rem 0;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
}

nav ul li a:hover,
nav ul li a.active {
    color: #007bff;
}

.header-icons {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-dropdown {
    position: relative;
}

.user-dropdown > a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.user-dropdown > a:hover {
    transform: scale(1.05);
}

.user-dropdown img {
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.user-dropdown img:hover {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 280px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-section {
    border-bottom: 1px solid #f0f0f0;
}

.dropdown-section:last-child {
    border-bottom: none;
}

.dropdown-header {
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #666;
    background-color: #f8f9fa;
    border-bottom: 1px solid #f0f0f0;
}

/* Modal Customizations */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: #0d6efd;
    color: white;
    border-radius: 12px 12px 0 0;
    border: none;
}

.modal-title {
    font-weight: 600;
}

.btn-close {
    filter: invert(1);
}

.btn {
    border-radius: 5px !important;
}

/* Responsive */
@media (max-width: 768px) {
    .header-container {
        padding: 1rem;
    }
    
    nav ul {
        display: none;
    }
    
    .dropdown-menu {
        min-width: 260px;
    }
}
    </style>
</head>
<body>



    <header>
        <div class="header-container">
            <div class="logo">EMPIRE.</div>
            <nav>
                <ul>
                    <li><a href="../../views/pages/shop.php">Shop</a></li>
                    <li><a href="../../views/pages/category.php">Categories</a></li>
                    <li><a href="../../views/pages/new-arrivals.php">New Arrivals</a></li>
                    <li><a href="../../views/pages/on-sale.php">On Sale</a></li>
                </ul>
            </nav>

            <div class="header-icons">
                <div class="user-dropdown">
                    <a href="#" onclick="toggleDropdown(event)">
                        <img src="<?= $profilePic ?>" alt="Profile" 
                             style="width:45px; height:45px; border-radius:50%; object-fit:cover;">
                    </a>
                    <div class="dropdown-menu" id="userDropdown">
                        <?php if ($user): ?>
                            <!-- Profile Section -->
                            <div class="dropdown-section">
                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#profileModal">
                                    <i class="fas fa-user-circle"></i> Profile
                                </a>
                                <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#settingsModal">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </div>

                            <!-- Shopping Section -->
                        <div class="dropdown-section">
                            <div class="dropdown-header">Shopping</div>

                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cartModal">
                                <i class="fas fa-shopping-cart"></i> 
                                <span style="flex: 1;">Shopping Cart</span>
                                <span id="headerCartBadge" style="display: none;">0</span>
                            </a>

                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#wishlistModal">
                                <i class="fas fa-heart"></i> 
                                <span style="flex: 1;">Wishlist</span>
                                <span id="headerWishlistBadge" style="display: none;">0</span>
                            </a>

                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#ordersModal">
                                <i class="fas fa-box"></i> 
                                <span style="flex: 1;">My Orders</span>
                                <span id="headerOrdersBadge" style="display: none;">0</span>
                            </a>

                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#notificationsModal">
                                <i class="fas fa-bell"></i> 
                                <span style="flex: 1;">Notifications</span>
                                <span id="headerNotificationBadge" style="display: none;">0</span>
                            </a>
                        </div>


                            <!-- Account Section -->
                            <div class="dropdown-section">
                                <a href="../../../app/controllers/AuthController.php?action=logout" class="dropdown-item logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="../../views/auth/login.php" class="dropdown-item">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="../../views/auth/register.php" class="dropdown-item">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- User Profile Modal -->
    <?php if ($user): ?>
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel"><i class="fas fa-user-circle me-2"></i>User Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProfileForm" method="POST" action="../../../app/controllers/UserController.php?action=updateProfile" enctype="multipart/form-data">
                    <input type="hidden" name="old_profile_pic" value="<?= htmlspecialchars($user['profile_pic'] ?? '') ?>">
                    <div class="modal-body container-fluid">
                        <div class="row align-items-center">
                            <!-- Profile Picture Column -->
                            <div class="col-md-4 text-center mb-3">
                                <label for="profilePictureInput" class="d-block position-relative" style="cursor: pointer;">
                                    <img 
                                        src="<?= $profilePic ?>" 
                                        alt="Profile Picture" 
                                        id="profilePreview" 
                                        class="rounded-circle border" 
                                        style="width:150px; height:150px; object-fit:cover;">
                                    <span class="position-absolute bottom-0 end-0 bg-primary rounded-circle p-2">
                                        <i class="fas fa-camera text-white"></i>
                                    </span>
                                </label>
                                <input type="file" name="profile_pic" id="profilePictureInput" accept="image/*" class="d-none">
                                <div class="form-text">Click image to change</div>
                                <div id="fileSizeInfo" class="form-text text-muted mt-1"></div>
                            </div>
                            
                            <!-- User Info Column -->
                            <div class="col-md-8">
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly title="Email cannot be changed">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                        <input 
                                            type="tel" 
                                            name="phone" 
                                            id="phoneInput"
                                            class="form-control" 
                                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                            pattern="[0-9]{11}"
                                            maxlength="11"
                                            placeholder="09XXXXXXXXX"
                                            title="Please enter exactly 11 digits">
                                        <div class="invalid-feedback" id="phoneError">
                                            Phone number must be exactly 11 digits
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle"></i> Enter 11-digit mobile number
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address</label>
                                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <span class="badge <?= isset($user['status']) && strtolower($user['status']) === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($user['status'] ?? 'Inactive') ?>
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Member Since</label>
                                        <span><?= isset($user['created_at']) ? date("F j, Y", strtotime($user['created_at'])) : 'N/A' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveProfileBtn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

     <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="settingsModalLabel"><i class="fas fa-cog me-2"></i>Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <!-- Settings Menu -->
            <div id="settingsMenu">
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-target="#privacyPolicyModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                <span><i class="fas fa-shield-alt me-2"></i>Privacy & Policy</span>
                <i class="fas fa-chevron-right"></i>
                </a>
                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-bs-target="#accountInfoModal" data-bs-toggle="modal" data-bs-dismiss="modal">
                <span><i class="fas fa-user-cog me-2"></i>Account Information</span>
                <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            </div>
      </div>
    </div>
  </div>
    </div>

    <!--Cart Modal with Stock Validation Features -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cartModalLabel">
                        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body" id="cartItems" style="max-height: 60vh; overflow-y: auto;">
                    <!-- Cart items will be dynamically inserted here -->
                    <div class="text-center py-5" id="emptyCartMessage">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                        <h6 class="text-muted mb-2">Your cart is empty</h6>
                        <p class="text-muted small">Add some products to get started!</p>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-store me-1"></i>Continue Shopping
                        </button>
                    </div>
                </div>
                
                <!-- Cart Summary Section -->
                <div class="modal-body border-top bg-light" id="cartSummary" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Items:</span>
                                <span id="summaryItemCount">0</span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Subtotal:</span>
                                <span>₱<span id="summarySubtotal">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Shipping:</span>
                                <span class="text-success">Free</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="border-start ps-3">
                                <div class="small text-muted mb-1">Total Amount</div>
                                <h5 class="mb-0 text-primary">₱<span id="cartTotal">0.00</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-0 pt-0">
                    <div class="w-100">
                        <!-- Stock Validation Alerts -->
                        <div id="stockAlerts" class="mb-3"></div>
                        
                        <!-- Action Buttons -->
                        <div class="row g-2">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                                    <i class="fas fa-arrow-left me-1"></i>Continue Shopping
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-primary w-100" id="checkoutBtn">
                                    <i class="fas fa-credit-card me-1"></i>Proceed to Checkout
                                    <span class="badge bg-light text-primary ms-2">₱<span id="checkoutTotal">0.00</span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy & Policy Modal -->
    <div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyPolicyModalLabel"><i class="fas fa-shield-alt me-2"></i>Privacy & Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <h6>Privacy Policy</h6>
                    <p>At EMPIRE., we are committed to protecting your privacy and personal information. This policy outlines how we collect, use, and protect your data.</p>
                    
                    <h6>Information We Collect</h6>
                    <ul>
                        <li>Personal information (name, email, phone number)</li>
                        <li>Shipping and billing addresses</li>
                        <li>Purchase history and preferences</li>
                        <li>Website usage data and cookies</li>
                    </ul>
                    
                    <h6>How We Use Your Information</h6>
                    <ul>
                        <li>Process orders and payments</li>
                        <li>Provide customer support</li>
                        <li>Send order updates and promotional emails</li>
                        <li>Improve our services and website</li>
                    </ul>
                    
                    <h6>Data Security</h6>
                    <p>We implement industry-standard security measures to protect your personal information from unauthorized access, disclosure, or misuse.</p>
                    
                    <h6>Your Rights</h6>
                    <p>You have the right to access, update, or delete your personal information. Contact us to exercise these rights.</p>
                    
                    <p class="text-muted"><small>Last updated: August 2025</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information Modal -->
    <div class="modal fade" id="accountInfoModal" tabindex="-1" aria-labelledby="accountInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountInfoModalLabel">
                        <i class="fas fa-user-cog me-2"></i>Account Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!-- Profile Picture -->
                    <div class="text-center mb-4">
                    <img src="<?= $profilePic ?>" alt="Profile Picture" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover;">
                        <div class="mt-2 fw-bold"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-4"><strong>User ID:</strong></div>
                        <div class="col-8"><?= htmlspecialchars($user['id'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Username:</strong></div>
                        <div class="col-8"><?= htmlspecialchars($user['username'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Email:</strong></div>
                        <div class="col-8"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Phone:</strong></div>
                        <div class="col-8"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Address:</strong></div>
                        <div class="col-8"><?= htmlspecialchars($user['address'] ?? 'Not provided') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Status:</strong></div>
                        <div class="col-8">
                            <span class="badge <?= isset($user['status']) && strtolower($user['status']) === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($user['status'] ?? 'Inactive') ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Member Since:</strong></div>
                        <div class="col-8"><?= isset($user['created_at']) ? date("F j, Y", strtotime($user['created_at'])) : 'N/A' ?></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Wishlist Modal -->
    <div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="wishlistModalLabel"><i class="fas fa-heart me-2"></i>My Wishlist</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center py-4">
                            <i class="fas fa-heart text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Your wishlist is empty</p>
                            <button class="btn btn-primary">Start Shopping</button>
                        </div>
                    </div>
                </div>
            </div>
    </div>


    <!-- Orders Modal -->
    <div class="modal fade" id="ordersModal" tabindex="-1" aria-labelledby="ordersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header orders-header text-white modal-title" >
                    <div>
                        <h3 class="modal-title mb-1 fw-bold opacity-90" id="ordersModalLabel"><i class="fas fa-shopping-bag me-3"></i>My Orders</h3>
                        <small class="opacity-75">Track and manage your order history</small>
                    </div>
                    <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <?php
                    // Fetch user orders - add this to your header.php before the modal
                    $userOrders = [];
                    if (isset($_SESSION['user_id'])) {
                        try {
                            $stmt = $db->prepare("
                                SELECT 
                                    o.*,
                                    u.username as user_name,      -- User's name
                                    u.phone as user_phone,        -- User's phone number
                                    u.address as user_address,    -- User's address
                                    COUNT(oi.id) as item_count
                                FROM orders o
                                LEFT JOIN order_items oi ON o.id = oi.order_id
                                LEFT JOIN users u ON o.user_id = u.id  -- Join with users table
                                WHERE o.user_id = :user_id
                                GROUP BY o.id
                                ORDER BY o.created_at DESC
                            ");
                            $stmt->execute(['user_id' => $_SESSION['user_id']]);
                            $userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            error_log("User orders fetch error: " . $e->getMessage());
                        }
                    }
                    ?>

                    <!-- Filter Section -->
                    <div class="filter-section bg-light py-4 px-4 border-bottom">
                        <div class="row g-4 align-items-end">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-filter me-2 text-primary"></i>Status
                                </label>
                                <select class="form-select shadow-sm border-0" id="userOrderStatusFilter">
                                    <option value="">All Orders</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-calendar me-2 text-primary"></i>Date
                                </label>
                                <input type="date" class="form-control shadow-sm border-0" id="userOrderDateFilter">
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold text-dark mb-2">
                                    <i class="fas fa-search me-2 text-primary"></i>Search
                                </label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text border-0 bg-white">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="search" class="form-control border-0" placeholder="Search by order number..." id="userOrderSearch">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Container -->
                    <div class="orders-container" style="max-height: 65vh; overflow-y: auto;">
                        <?php if (!empty($userOrders) && is_array($userOrders)): ?>
                            <?php foreach ($userOrders as $order): ?>
                                <?php
                                $statusConfig = match($order['status'] ?? 'pending') {
                                    'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending'],
                                    'processing' => ['class' => 'info', 'icon' => 'cog', 'text' => 'Processing'],
                                    'shipped' => ['class' => 'primary', 'icon' => 'truck', 'text' => 'Shipped'],
                                    'delivered' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Delivered'],
                                    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'text' => 'Cancelled'],
                                    default => ['class' => 'secondary', 'icon' => 'question', 'text' => 'Unknown']
                                };

                                $orderDate = date('M j, Y \a\t g:i A', strtotime($order['created_at']));
                                $canCancel = ($order['status'] ?? 'pending') === 'pending'; // Only pending orders can be cancelled
                                $canDownload = in_array($order['status'] ?? '', ['processing', 'shipped', 'delivered']);
                                ?>
                                
                                <div class="order-card border-bottom" 
                                    data-id="<?= $order['id'] ?>"
                                    data-status="<?= $order['status'] ?? 'pending' ?>"
                                    data-date="<?= $order['created_at'] ?>"
                                    data-search="<?= htmlspecialchars(strtolower(($order['order_number'] ?? $order['id']) . ' ' . ($order['status'] ?? ''))) ?>">
                                    
                                    <!-- Order Card Container -->
                                    <div class="order-wrapper mx-4 my-4">
                                        <div class="card border-0 shadow-sm hover-card">
                                            <div class="card-body p-4">
                                                <!-- Order Header -->
                                                <div class="d-flex justify-content-between align-items-start mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="order-icon me-3">
                                                            <i class="fas fa-receipt text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-1 fw-bold">Order #<?= $order['order_number'] ?? $order['id'] ?></h5>
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock me-1"></i><?= $orderDate ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-<?= $statusConfig['class'] ?> px-3 py-2 fs-6">
                                                        <i class="fas fa-<?= $statusConfig['icon'] ?> me-2"></i><?= $statusConfig['text'] ?>
                                                    </span>
                                                </div>

                                                <!-- Order Stats -->
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-3 col-6">
                                                        <div class="stat-card text-center p-3 bg-light rounded-3">
                                                            <i class="fas fa-box text-primary fs-4 mb-2"></i>
                                                            <div class="fw-bold"><?= $order['item_count'] ?? 0 ?></div>
                                                            <small class="text-muted">Items</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-6">
                                                        <div class="stat-card text-center p-3 bg-light rounded-3">
                                                            <i class="fas fa-peso-sign text-success fs-4 mb-2"></i>
                                                            <div class="fw-bold text-success">₱<?= number_format($order['total_amount'] ?? 0, 2) ?></div>
                                                            <small class="text-muted">Total</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-6">
                                                        <div class="stat-card text-center p-3 bg-light rounded-3">
                                                            <i class="fas fa-credit-card text-info fs-4 mb-2"></i>
                                                            <div class="fw-bold"><?= ucfirst($order['payment_method'] ?? 'N/A') ?></div>
                                                            <small class="text-muted">Payment</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-6">
                                                        <div class="stat-card text-center p-3 bg-light rounded-3">
                                                            <i class="fas fa-<?= ($order['payment_status'] ?? 'pending') === 'paid' ? 'check-circle' : 'clock' ?> text-<?= ($order['payment_status'] ?? 'pending') === 'paid' ? 'success' : 'warning' ?> fs-4 mb-2"></i>
                                                            <div class="fw-bold"><?= ucfirst($order['payment_status'] ?? 'Pending') ?></div>
                                                            <small class="text-muted">Status</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Progress Bar for shipped orders -->
                                                <?php if (($order['status'] ?? '') === 'shipped'): ?>
                                                    <div class="mb-4">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted fw-semibold">Order Progress</small>
                                                            <small class="text-primary fw-bold">In Transit 🚚</small>
                                                        </div>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-primary progress-bar-animated progress-bar-striped" 
                                                                style="width: 75%; border-radius: 4px;"></div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Action Buttons - FIXED LAYOUT -->
                                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                                    <!-- Show Details Button -->
                                                    <button type="button" class="btn btn-outline-primary toggle-details" 
                                                            data-order-id="<?= $order['id'] ?>" title="Show Details">
                                                        <i class="fas fa-chevron-down me-2 details-toggle-icon"></i>Show Details
                                                    </button>
                                                    
                                                    <!-- Invoice and Cancel Buttons -->
                                                    <div class="d-flex gap-2">
                                                        <!-- Download Invoice (if delivered) -->
                                                        <?php if ($canDownload): ?>
                                                            <button type="button" class="btn btn-outline-success" 
                                                                    title="Download Invoice"
                                                                    onclick="downloadUserInvoice(<?= $order['id'] ?>)">
                                                                <i class="fas fa-download me-1"></i>Invoice
                                                            </button>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Cancel Order (only if pending) -->
                                                        <?php if ($canCancel): ?>
                                                            <button type="button" class="btn btn-outline-danger" 
                                                                    onclick="cancelUserOrder(<?= $order['id'] ?>)">
                                                                <i class="fas fa-times me-1"></i>Cancel
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Order Details (Hidden by default) -->
                                                <div class="order-details mt-4 d-none" id="details-<?= $order['id'] ?>">
                                                    <hr class="my-4">
                                                    
                                                    <!-- Loading state -->
                                                    <div class="text-center py-4 order-details-loading">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <p class="mt-3 text-muted mb-0">Loading order details...</p>
                                                    </div>
                                                    
                                                    <!-- Order items will be loaded here -->
                                                    <div class="order-items-container d-none">
                                                        <h6 class="mb-3 fw-bold">
                                                            <i class="fas fa-list me-2 text-primary"></i>Order Items
                                                        </h6>
                                                    </div>
                                                    
                                                    <!-- Order Information -->
<div class="row g-4 mt-3">
    <div class="col-md-6">
        <div class="info-card bg-light p-4 rounded-3 h-100">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-info-circle me-2 text-primary"></i>Order Information
            </h6>
            <div class="info-item d-flex justify-content-between mb-2">
                <span class="text-muted">Order Date:</span>
                <span class="fw-medium"><?= $orderDate ?></span>
            </div>
            <div class="info-item d-flex justify-content-between mb-2">
                <span class="text-muted">Payment Method:</span>
                <span class="fw-medium"><?= ucfirst($order['payment_method'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item d-flex justify-content-between mb-0">
                <span class="text-muted">Payment Status:</span>
                <?php 
                    // Determine payment status: if order is delivered, payment is completed
                    $displayPaymentStatus = ($order['status'] === 'delivered') ? 'completed' : ($order['payment_status'] ?? 'pending');
                    $badgeClass = ($displayPaymentStatus === 'completed' || $displayPaymentStatus === 'paid') ? 'success' : 'warning';
                    $statusText = ($displayPaymentStatus === 'completed' || $displayPaymentStatus === 'paid') ? 'Completed' : ucfirst($displayPaymentStatus);
                ?>
                <span class="badge bg-<?= $badgeClass ?>" id="payment-status-badge-<?= $order['id'] ?>">
                    <?= $statusText ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="info-card bg-light p-4 rounded-3 h-100">
            <h6 class="fw-bold mb-3">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Delivery Information
            </h6>
            
            <!-- Customer Information -->
            <div class="mb-3">
                <small class="text-muted d-block mb-1">Customer:</small>
                <p class="mb-0 fw-medium">
                    <i class="fas fa-user me-1 text-primary"></i>
                    <?= htmlspecialchars($order['user_name'] ?? 'N/A') ?>
                </p>
            </div>
            
            <!-- Phone Number -->
            <div class="mb-3">
                <small class="text-muted d-block mb-1">Contact Number:</small>
                <p class="mb-0 fw-medium">
                    <i class="fas fa-phone me-1 text-primary"></i>
                    <?= htmlspecialchars($order['user_phone'] ?? 'N/A') ?>
                </p>
            </div>
            
            <!-- Delivery Address -->
            <div class="mb-3">
                <small class="text-muted d-block mb-1">Delivery Address:</small>
                <p class="mb-0 fw-medium">
                    <i class="fas fa-map-marker-alt me-1 text-primary"></i>
                    <?= htmlspecialchars($order['user_address'] ?? 'Address not specified') ?>
                </p>
            </div>
            
            <?php if (!empty($order['delivery_notes'])): ?>
            <div>
                <small class="text-muted d-block mb-1">Delivery Notes:</small>
                <p class="mb-0">
                    <i class="fas fa-sticky-note me-1 text-primary"></i>
                    <?= htmlspecialchars($order['delivery_notes']) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
                                                    
                                                    <!-- Order Summary -->
                                                    <div class="order-summary-section mt-4">
                                                        <div class="row justify-content-end">
                                                            <div class="col-md-6">
                                                                <div class="summary-card bg-success bg-opacity-10 p-4 rounded-3 border border-success border-opacity-25">
                                                                    <h6 class="fw-bold mb-3 text-success">
                                                                        <i class="fas fa-calculator me-2"></i>Order Summary
                                                                    </h6>
                                                                    <div class="d-flex justify-content-between mb-2">
                                                                        <span>Subtotal:</span>
                                                                        <span>₱<?= number_format(($order['total_amount'] ?? 0) - ($order['delivery_fee'] ?? 0), 2) ?></span>
                                                                    </div>
                                                                    <?php if (($order['delivery_fee'] ?? 0) > 0): ?>
                                                                    <div class="d-flex justify-content-between mb-2 text-muted">
                                                                        <span>Delivery Fee:</span>
                                                                        <span>₱<?= number_format($order['delivery_fee'], 2) ?></span>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <hr>
                                                                    <div class="d-flex justify-content-between fs-5 fw-bold text-success">
                                                                        <span>Total:</span>
                                                                        <span>₱<?= number_format($order['total_amount'] ?? 0, 2) ?></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- No results found (hidden by default, shown by JS when filtered) -->
                            <div class="no-orders-found text-center py-5 d-none">
                                <div class="mb-4">
                                    <i class="fas fa-search text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                </div>
                                <h4 class="text-muted mb-2">No orders found</h4>
                                <p class="text-muted">Try adjusting your search criteria</p>
                            </div>
                            
                        <?php else: ?>
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-shopping-bag text-muted" style="font-size: 5rem; opacity: 0.2;"></i>
                                </div>
                                <h3 class="text-muted mb-3">No Orders Yet</h3>
                                <p class="text-muted mb-4">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                                <button type="button" class="btn btn-primary btn-lg px-5" data-bs-dismiss="modal">
                                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Showing <?= count($userOrders) ?> order<?= count($userOrders) != 1 ? 's' : '' ?>
                        </small>
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Enhanced Styles for Redesigned Orders Modal */
    .hover-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.08) !important;
    }

    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .order-icon {
        width: 50px;
        height: 50px;
        background: rgba(99, 102, 241, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .stat-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .stat-card:hover {
        background-color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .info-card {
        border: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .info-card:hover {
        background-color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .summary-card {
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        transform: translateY(-1px);
    }

    .details-toggle-icon {
        transition: transform 0.3s ease;
    }

    .details-toggle-icon.rotated {
        transform: rotate(180deg);
    }

    .order-details {
        animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            max-height: 1000px;
            transform: translateY(0);
        }
    }

    .order-item {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: white;
        transition: all 0.3s ease;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .order-item:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }

    .order-item-image {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        background-color: #f8f9fa;
        border: 2px solid rgba(0,0,0,0.05);
    }

    /* Scrollbar Styling */
    .orders-container::-webkit-scrollbar {
        width: 8px;
    }

    .orders-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .orders-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .orders-container::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }

    /* Button & Form Improvements */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .form-control, .form-select {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .badge {
        border-radius: 8px;
        font-weight: 500;
        letter-spacing: 0.025em;
    }

    .progress-bar {
        border-radius: 4px;
    }
    </style>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
        <!-- Header -->
        <div class="modal-header">
            <h5 class="modal-title" id="notificationsModalLabel">
            <i class="fas fa-bell me-2"></i> Notifications
            <span class="badge bg-primary ms-2" id="notificationCount"></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs px-3 pt-2" id="notifTabs" role="tablist">
            <li class="nav-item">
            <button class="nav-link active" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">
                <i class="fas fa-box me-1"></i> Orders
            </button>
            </li>
        </ul>

        <!-- Body with Tab Panes -->
        <div class="modal-body p-0">
            <div class="tab-content" id="notifTabsContent">
            
            <!-- Orders Tab with Order Details -->
            <div class="tab-pane fade show active" id="orders" role="tabpanel">
                <div class="p-3">
                <!-- Order notifications will be loaded here -->
                <div id="orderNotificationsList">
                    <!-- Loading state -->
                    <div class="text-center py-4" id="orderNotificationsLoading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading order notifications...</p>
                    </div>
                    
                    <!-- Order notifications content will be inserted here -->
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
        
        </div>
    </div>
    </div>


    <?php endif; ?>


<!-- alert for updateProfile -->

<?php if (isset($_SESSION['success'])): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: '<?= $_SESSION['success']; ?>',
      toast: true,
      position: 'top',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php unset($_SESSION['success']); ?>
<?php elseif (isset($_SESSION['error'])): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: '<?= $_SESSION['error']; ?>',
      toast: true,
      position: 'top',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>


<!-- alert for change password -->

<?php if (isset($_SESSION['success'])): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: '<?= $_SESSION['success']; ?>',
      toast: true,
      position: 'top',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php unset($_SESSION['success']); ?>

<?php elseif (isset($_SESSION['error'])): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: '<?= $_SESSION['error']; ?>',
      toast: true,
      position: 'top',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>


<script>
    // Unified Badge Management System - Works with all badges
document.addEventListener('DOMContentLoaded', function() {
    console.log('Unified badge system initialized');
    
    // Initialize all badges immediately
    updateAllBadges();
    
    // Update badges periodically
    setInterval(updateAllBadges, 10000);
    
    // Listen for custom events from cart/wishlist/orders
    window.addEventListener('cartUpdated', updateCartBadge);
    window.addEventListener('wishlistUpdated', updateWishlistBadge);
    window.addEventListener('ordersUpdated', updateOrdersBadge);
    window.addEventListener('notificationsUpdated', updateNotificationBadge);
});

// Update all badges at once
function updateAllBadges() {
    updateCartBadge();
    updateWishlistBadge();
    updateOrdersBadge();
    updateNotificationBadge();
}

// Update Cart Badge from CartManager
function updateCartBadge() {
    const badge = document.getElementById('headerCartBadge');
    if (!badge) {
        console.log('Badge element not found');
        return;
    }
    
    try {
        let totalItems = 0;
        
        // Try to get from CartManager first
        if (window.cartManager && window.cartManager.getTotalItems) {
            totalItems = window.cartManager.getTotalItems();
        } 
        // Fallback: try to read from localStorage directly
        else {
            const savedCart = localStorage.getItem('shopping_cart');
            if (savedCart) {
                const cart = JSON.parse(savedCart);
                totalItems = cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
            }
        }
        
        console.log('Updating cart badge - Total items:', totalItems);
        
        if (totalItems > 0) {
            badge.textContent = totalItems;
            badge.style.display = 'inline-flex';
            badge.style.visibility = 'visible';
        } else {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error updating cart badge:', error);
        badge.style.display = 'none';
    }
}

// Update Wishlist Badge from wishlist array
function updateWishlistBadge() {
    const badge = document.getElementById('headerWishlistBadge');
    if (!badge) return;
    
    try {
        // Get wishlist count from global wishlist variable
        let wishlistCount = 0;
        
        if (window.wishlist && Array.isArray(window.wishlist)) {
            wishlistCount = window.wishlist.length;
        }
        
        console.log('Wishlist items:', wishlistCount);
        
        if (wishlistCount > 0) {
            badge.textContent = wishlistCount;
            badge.style.display = 'inline-flex';
            badge.style.visibility = 'visible';
        } else {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error updating wishlist badge:', error);
        badge.style.display = 'none';
    }
}

// Update Orders Badge from server
function updateOrdersBadge() {
    const badge = document.getElementById('headerOrdersBadge');
    if (!badge) return;
    
    // Check if we have pending orders data
    if (window.pendingOrdersCount !== undefined) {
        if (window.pendingOrdersCount > 0) {
            badge.textContent = window.pendingOrdersCount;
            badge.style.display = 'inline-flex';
            badge.style.visibility = 'visible';
        } else {
            badge.style.display = 'none';
        }
        return;
    }
    
    // Otherwise fetch from server
    fetch('../../../app/controllers/OrderController.php?action=getPendingOrdersCount')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            console.log('Orders count:', data);
            if (data.success && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-flex';
                badge.style.visibility = 'visible';
                window.pendingOrdersCount = data.count;
            } else {
                badge.style.display = 'none';
                window.pendingOrdersCount = 0;
            }
        })
        .catch(error => {
            console.error('Error fetching orders count:', error);
            badge.style.display = 'none';
        });
}

// Update Notification Badge from server
function updateNotificationBadge() {
    const badge = document.getElementById('headerNotificationBadge');
    if (!badge) return;
    
    // Check if we have notifications data
    if (window.unreadNotificationsCount !== undefined) {
        if (window.unreadNotificationsCount > 0) {
            badge.textContent = window.unreadNotificationsCount;
            badge.style.display = 'inline-flex';
            badge.style.visibility = 'visible';
            badge.classList.add('badge-pulse');
        } else {
            badge.style.display = 'none';
            badge.classList.remove('badge-pulse');
        }
        return;
    }
    
    // Otherwise fetch from server
    fetch('../../../app/controllers/OrderController.php?action=getUnreadNotificationsCount')
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            console.log('Notifications count:', data);
            if (data.success && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-flex';
                badge.style.visibility = 'visible';
                badge.classList.add('badge-pulse');
                window.unreadNotificationsCount = data.count;
            } else {
                badge.style.display = 'none';
                badge.classList.remove('badge-pulse');
                window.unreadNotificationsCount = 0;
            }
        })
        .catch(error => {
            console.error('Error fetching notifications count:', error);
            badge.style.display = 'none';
        });
}

// Make functions globally available
window.updateCartBadge = updateCartBadge;
window.updateWishlistBadge = updateWishlistBadge;
window.updateOrdersBadge = updateOrdersBadge;
window.updateNotificationBadge = updateNotificationBadge;
window.updateAllBadges = updateAllBadges;

// Integrate with CartManager when it's initialized
document.addEventListener('DOMContentLoaded', function() {
    // Wait for CartManager to be initialized
    const checkCartManager = setInterval(() => {
        if (window.cartManager) {
            clearInterval(checkCartManager);
            
            // Override CartManager's updateCartBadge to use our unified system
            const originalUpdateDisplay = window.cartManager.updateCartDisplay.bind(window.cartManager);
            window.cartManager.updateCartDisplay = function() {
                originalUpdateDisplay();
                window.updateCartBadge(); // Use unified badge update
            };
            
            console.log('CartManager integrated with unified badge system');
        }
    }, 100);
    
    // Stop checking after 5 seconds
    setTimeout(() => clearInterval(checkCartManager), 5000);
});
</script>

<script>
    // Handle settings modal links
    document.addEventListener('DOMContentLoaded', function() {
        // Change Password link
        const changePasswordLink = document.querySelector('[data-target="changePasswordModal"]');
        if (changePasswordLink) {
            changePasswordLink.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('settingsModal').addEventListener('hidden.bs.modal', function() {
                    openModalWithDelay('changePasswordModal');
                }, { once: true });
                bootstrap.Modal.getInstance(document.getElementById('settingsModal')).hide();
            });
        }
     
        // Account Info link
        const accountInfoLink = document.querySelector('[data-target="accountInfoModal"]');
        if (accountInfoLink) {
            accountInfoLink.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('settingsModal').addEventListener('hidden.bs.modal', function() {
                    openModalWithDelay('accountInfoModal');
                }, { once: true });
                bootstrap.Modal.getInstance(document.getElementById('settingsModal')).hide();
            });
        }

        // Delete Account link
        const deleteAccountLink = document.querySelector('[data-target="deleteAccountModal"]');
        if (deleteAccountLink) {
            deleteAccountLink.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('accountInfoModal').addEventListener('hidden.bs.modal', function() {
                    openModalWithDelay('deleteAccountModal');
                }, { once: true });
                bootstrap.Modal.getInstance(document.getElementById('accountInfoModal')).hide();
            });
        }
    });

    // Profile picture preview
    const inputFile = document.getElementById('profilePictureInput');
    if (inputFile) {
        inputFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Display file size
                const fileSizeInfo = document.getElementById('fileSizeInfo');
                const fileSize = (file.size / 1024).toFixed(2); // Size in KB
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2); // Size in MB
                
                if (file.size < 1024 * 1024) {
                    fileSizeInfo.textContent = `File size: ${fileSize} KB`;
                } else {
                    fileSizeInfo.textContent = `File size: ${fileSizeMB} MB`;
                }
                
                // Check file size limit (e.g., 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    fileSizeInfo.className = 'form-text text-danger mt-1';
                    fileSizeInfo.textContent += ' (Too large! Max 5MB allowed)';
                    return;
                } else {
                    fileSizeInfo.className = 'form-text text-success mt-1';
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Dropdown functions
    function toggleDropdown(e) {
        e.preventDefault();
        document.getElementById('userDropdown').classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('userDropdown');
        const userDropdown = document.querySelector('.user-dropdown');
        if (!userDropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Close dropdown when modal opens
    document.addEventListener('show.bs.modal', function() {
        document.getElementById('userDropdown').classList.remove('show');
    });

    // Show success/error messages
    <?php if (isset($_SESSION['success'])): ?>
        alert('<?= $_SESSION['success'] ?>');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        alert('Error: <?= $_SESSION['error'] ?>');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</script>



<script>
document.addEventListener("DOMContentLoaded", function () {
    const checkoutBtn = document.getElementById("checkoutBtn");
    const cartModal = document.getElementById("cartModal");
    const checkoutModal = document.getElementById("checkoutModal");

    if (checkoutBtn && cartModal && checkoutModal) {
        checkoutBtn.addEventListener("click", function () {
            // Get the cart modal instance
            const cartModalInstance = bootstrap.Modal.getInstance(cartModal);
            
            if (cartModalInstance) {
                // Hide the cart modal
                cartModalInstance.hide();
                
                // Wait for cart modal to be completely hidden
                cartModal.addEventListener("hidden.bs.modal", function () {
                    // Remove any lingering backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                        backdrop.remove();
                    });
                    
                    // Small delay before opening checkout modal
                    setTimeout(() => {
                        const checkoutModalInstance = new bootstrap.Modal(checkoutModal);
                        checkoutModalInstance.show();
                    }, 100);
                }, { once: true });
            }
        });
    }
    
    // Add cleanup when checkout modal is closed
    if (checkoutModal) {
        checkoutModal.addEventListener('hidden.bs.modal', function () {
            // Remove any lingering backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body styles
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
    
    // Add cleanup for cart modal as well
    if (cartModal) {
        cartModal.addEventListener('hidden.bs.modal', function () {
            // Remove any lingering backdrops
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.remove();
            });
            
            // Reset body styles
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
});
</script>



<script>
    // Unified Wishlist Management System with localStorage
document.addEventListener('DOMContentLoaded', function() {
    // Load wishlist from localStorage
    let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    console.log('Wishlist loaded from localStorage:', wishlist);
    
    // Restore wishlist icons on page load
    restoreWishlistIcons();
    
    // ==============================================
    // PRODUCT CARD WISHLIST BUTTONS
    // ==============================================
    const wishlistButtons = document.querySelectorAll('.wishlist');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productCard = this.closest('.product-card');
            const productImage = productCard.querySelector('.product-image');
            
            const product = {
                id: productImage.dataset.productId,
                name: productImage.dataset.productName,
                description: productImage.dataset.productDescription,
                price: productImage.dataset.productPrice,
                stock: productImage.dataset.productStock,
                category: productImage.dataset.productCategory,
                image: productImage.dataset.productImage,
                sizes: productImage.dataset.productSizes
            };
            
            const existingIndex = wishlist.findIndex(item => item.id === product.id);
            
            if (existingIndex !== -1) {
                wishlist.splice(existingIndex, 1);
                this.innerHTML = '<i class="far fa-heart"></i>';
                this.classList.remove('active');
                showWishlistNotification('Removed from wishlist', 'info');
            } else {
                wishlist.push(product);
                this.innerHTML = '<i class="fas fa-heart"></i>';
                this.classList.add('active');
                showWishlistNotification('Added to wishlist!', 'success');
            }
            
            localStorage.setItem('wishlist', JSON.stringify(wishlist));
            updateWishlistModal();
            updateProductDetailsModalButton(product.id);
        });
    });
    
    // ==============================================
    // PRODUCT DETAILS MODAL INTEGRATION
    // ==============================================
    const productDetailsModal = document.getElementById('productDetailsModal');
    
    if (productDetailsModal) {
        productDetailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            const productId = button.dataset.productId;
            const productName = button.dataset.productName;
            const productDescription = button.dataset.productDescription;
            const productPrice = button.dataset.productPrice;
            const productStock = button.dataset.productStock;
            const productCategory = button.dataset.productCategory;
            const productImage = button.dataset.productImage;
            const productSizes = button.dataset.productSizes;
            
            // Update modal content
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductDescription').textContent = productDescription;
            document.getElementById('modalProductPrice').textContent = '₱' + parseFloat(productPrice).toLocaleString('en-PH', {minimumFractionDigits: 2});
            document.getElementById('modalProductStock').innerHTML = `<i class="fas fa-box me-1"></i>${productStock} in stock`;
            document.getElementById('modalProductCategory').innerHTML = `<i class="fas fa-tag me-1"></i>${productCategory}`;
            document.getElementById('modalProductImage').src = productImage;
            document.getElementById('modalProductImage').alt = productName;
            
            // Handle sizes
            const sizesContainer = document.getElementById('modalProductSizes');
            if (productSizes && productSizes.trim() !== '') {
                const sizesArray = productSizes.split(',').map(s => s.trim());
                sizesContainer.innerHTML = sizesArray.map(size => 
                    `<span class="badge bg-light text-dark border me-2 mb-2 px-3 py-2">${size}</span>`
                ).join('');
            } else {
                sizesContainer.innerHTML = '<span class="text-muted">One size fits all</span>';
            }
            
            // Setup wishlist button in modal
            const modalWishlistBtn = productDetailsModal.querySelector('.btn-outline-dark, .btn-danger');
            const isInWishlist = wishlist.some(item => item.id === productId);
            
            if (isInWishlist) {
                modalWishlistBtn.innerHTML = '<i class="fas fa-heart me-2"></i>Remove from Wishlist';
                modalWishlistBtn.classList.remove('btn-outline-dark');
                modalWishlistBtn.classList.add('btn-danger');
            } else {
                modalWishlistBtn.innerHTML = '<i class="far fa-heart me-2"></i>Add to Wishlist';
                modalWishlistBtn.classList.remove('btn-danger');
                modalWishlistBtn.classList.add('btn-outline-dark');
            }
            
            // Clone button to remove old event listeners
            const newWishlistBtn = modalWishlistBtn.cloneNode(true);
            modalWishlistBtn.parentNode.replaceChild(newWishlistBtn, modalWishlistBtn);
            
            newWishlistBtn.addEventListener('click', function() {
                const existingIndex = wishlist.findIndex(item => item.id === productId);
                
                if (existingIndex !== -1) {
                    // Remove from wishlist
                    wishlist.splice(existingIndex, 1);
                    this.innerHTML = '<i class="far fa-heart me-2"></i>Add to Wishlist';
                    this.classList.remove('btn-danger');
                    this.classList.add('btn-outline-dark');
                    updateProductCardWishlistIcon(productId, false);
                    showWishlistNotification('Removed from wishlist', 'info');
                } else {
                    // Add to wishlist
                    const product = {
                        id: productId,
                        name: productName,
                        description: productDescription,
                        price: productPrice,
                        stock: productStock,
                        category: productCategory,
                        image: productImage,
                        sizes: productSizes
                    };
                    
                    wishlist.push(product);
                    this.innerHTML = '<i class="fas fa-heart me-2"></i>Remove from Wishlist';
                    this.classList.remove('btn-outline-dark');
                    this.classList.add('btn-danger');
                    updateProductCardWishlistIcon(productId, true);
                    showWishlistNotification('Added to wishlist!', 'success');
                }
                
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
                updateWishlistModal();
            });
            
            // Setup Add to Cart button in modal
            const modalAddToCartBtn = productDetailsModal.querySelector('.btn-dark');
            const newAddToCartBtn = modalAddToCartBtn.cloneNode(true);
            modalAddToCartBtn.parentNode.replaceChild(newAddToCartBtn, modalAddToCartBtn);
            
            newAddToCartBtn.addEventListener('click', function() {
                const productCards = document.querySelectorAll('.product-card');
                productCards.forEach(card => {
                    const img = card.querySelector('.product-image');
                    if (img && img.dataset.productId === productId) {
                        const addToCartBtn = card.querySelector('.add-to-cart');
                        if (addToCartBtn) {
                            addToCartBtn.click();
                        }
                    }
                });
            });
        });
    }
    
    // ==============================================
    // HELPER FUNCTIONS
    // ==============================================
    function restoreWishlistIcons() {
        const wishlistButtons = document.querySelectorAll('.wishlist');
        wishlistButtons.forEach(button => {
            const productCard = button.closest('.product-card');
            const productImage = productCard.querySelector('.product-image');
            const productId = productImage?.dataset.productId;
            
            if (productId && wishlist.some(item => item.id === productId)) {
                button.innerHTML = '<i class="fas fa-heart"></i>';
                button.classList.add('active');
            }
        });
        updateWishlistModal();
    }
    
    function updateProductCardWishlistIcon(productId, isInWishlist) {
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            const img = card.querySelector('.product-image');
            if (img && img.dataset.productId === productId) {
                const heartBtn = card.querySelector('.wishlist');
                if (heartBtn) {
                    if (isInWishlist) {
                        heartBtn.innerHTML = '<i class="fas fa-heart"></i>';
                        heartBtn.classList.add('active');
                    } else {
                        heartBtn.innerHTML = '<i class="far fa-heart"></i>';
                        heartBtn.classList.remove('active');
                    }
                }
            }
        });
    }
    
    function updateProductDetailsModalButton(productId) {
        const modal = document.getElementById('productDetailsModal');
        if (modal && modal.classList.contains('show')) {
            const modalWishlistBtn = modal.querySelector('.btn-outline-dark, .btn-danger');
            if (modalWishlistBtn) {
                const currentProductId = modal.querySelector('#modalProductImage')?.alt;
                const isInWishlist = wishlist.some(item => item.id === productId);
                
                if (isInWishlist) {
                    modalWishlistBtn.innerHTML = '<i class="fas fa-heart me-2"></i>Remove from Wishlist';
                    modalWishlistBtn.classList.remove('btn-outline-dark');
                    modalWishlistBtn.classList.add('btn-danger');
                } else {
                    modalWishlistBtn.innerHTML = '<i class="far fa-heart me-2"></i>Add to Wishlist';
                    modalWishlistBtn.classList.remove('btn-danger');
                    modalWishlistBtn.classList.add('btn-outline-dark');
                }
            }
        }
    }
    
    function updateWishlistModal() {
        const wishlistModal = document.getElementById('wishlistModal');
        if (!wishlistModal) return;
        
        const modalBody = wishlistModal.querySelector('.modal-body');
        const modalHeader = wishlistModal.querySelector('.modal-header');
        
        const titleElement = modalHeader.querySelector('.modal-title');
        if (wishlist.length > 0) {
            titleElement.innerHTML = `<i class="fas fa-heart me-2"></i>My Wishlist <span class="badge bg-danger ms-2">${wishlist.length}</span>`;
        } else {
            titleElement.innerHTML = `<i class="fas fa-heart me-2"></i>My Wishlist`;
        }
        
        if (wishlist.length === 0) {
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-heart text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted">Your wishlist is empty</p>
                    <button class="btn btn-primary" data-bs-dismiss="modal">Start Shopping</button>
                </div>
            `;
        } else {
            let wishlistHtml = `
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted small">${wishlist.length} item${wishlist.length !== 1 ? 's' : ''} in your wishlist</span>
                    <button class="btn btn-sm btn-danger" onclick="clearWishlist()">
                        <i class="fas fa-trash-alt me-1"></i>Clear All
                    </button>
                </div>
                <div class="wishlist-items" style="max-height: 400px; overflow-y: auto;">
            `;
            
            wishlist.forEach((item, index) => {
                wishlistHtml += `
                    <div class="card mb-2 wishlist-item-card" data-product-id="${item.id}">
                        <div class="card-body p-3">
                            <div class="row align-items-center g-2">
                                <div class="col-3">
                                    <img src="${item.image}" 
                                         alt="${item.name}" 
                                         class="img-fluid rounded"
                                         style="width: 100%; height: 70px; object-fit: cover;">
                                </div>
                                
                                <div class="col-6">
                                    <h6 class="mb-1 fw-semibold text-truncate">${item.name}</h6>
                                    <p class="text-muted small mb-1">${item.category}</p>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-success">₱${parseFloat(item.price).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                        <span class="badge bg-light text-dark small">
                                            <i class="fas fa-box"></i> ${item.stock} in stock
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="col-3 text-end">
                                    <button class="btn btn-sm btn-primary w-100 mb-1 add-to-cart-from-wishlist" 
                                            data-product-id="${item.id}"
                                            title="Add to Cart">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger w-100 remove-from-wishlist" 
                                            data-index="${index}"
                                            title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            wishlistHtml += '</div>';
            modalBody.innerHTML = wishlistHtml;
            
            // Add event listeners for remove buttons
            document.querySelectorAll('.remove-from-wishlist').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    removeFromWishlist(index);
                });
            });

            // Add event listeners for add to cart buttons
            document.querySelectorAll('.add-to-cart-from-wishlist').forEach(btn => {
                btn.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    
                    const productCards = document.querySelectorAll('.product-card');
                    let addToCartButton = null;
                    
                    productCards.forEach(card => {
                        const img = card.querySelector('.product-image');
                        if (img && img.dataset.productId === productId) {
                            addToCartButton = card.querySelector('.add-to-cart');
                        }
                    });
                    
                    if (addToCartButton) {
                        addToCartButton.click();
                        
                        const wishlistItemIndex = wishlist.findIndex(item => item.id === productId);
                        if (wishlistItemIndex !== -1) {
                            wishlist.splice(wishlistItemIndex, 1);
                            
                            productCards.forEach(card => {
                                const img = card.querySelector('.product-image');
                                if (img && img.dataset.productId === productId) {
                                    const heartBtn = card.querySelector('.wishlist');
                                    if (heartBtn) {
                                        heartBtn.innerHTML = '<i class="far fa-heart"></i>';
                                        heartBtn.classList.remove('active');
                                    }
                                }
                            });
                            
                            localStorage.setItem('wishlist', JSON.stringify(wishlist));
                            updateWishlistModal();
                            updateProductDetailsModalButton(productId);
                        }
                        
                        showWishlistNotification('Added to cart!', 'success');
                    } else {
                        showWishlistNotification('Product not found', 'error');
                    }
                });
            });
        }
        
        updateWishlistBadge();
    }
    
    function showWishlistNotification(message, type) {
        const colors = {
            success: '#28a745',
            info: '#17a2b8',
            warning: '#ffc107',
            error: '#dc3545'
        };
        
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: ${colors[type] || colors.success};
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
            font-weight: 500;
        `;
        notification.innerHTML = `<i class="fas fa-heart me-2"></i>${message}`;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    }
    
    // ==============================================
    // GLOBAL FUNCTIONS
    // ==============================================
    window.removeFromWishlist = function(index) {
        const removedItem = wishlist[index];
        wishlist.splice(index, 1);
        
        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            const img = card.querySelector('.product-image');
            if (img && img.dataset.productId === removedItem.id) {
                const heartBtn = card.querySelector('.wishlist');
                if (heartBtn) {
                    heartBtn.innerHTML = '<i class="far fa-heart"></i>';
                    heartBtn.classList.remove('active');
                }
            }
        });
        
        updateWishlistModal();
        updateProductDetailsModalButton(removedItem.id);
        showWishlistNotification('Removed from wishlist', 'info');
    };
    
    window.clearWishlist = function() {
        if (confirm('Are you sure you want to clear your wishlist?')) {
            wishlist = [];
            
            localStorage.removeItem('wishlist');
            
            document.querySelectorAll('.wishlist').forEach(btn => {
                btn.innerHTML = '<i class="far fa-heart"></i>';
                btn.classList.remove('active');
            });
            
            updateWishlistModal();
            showWishlistNotification('Wishlist cleared', 'info');
        }
    };
    
    // ==============================================
    // STYLES
    // ==============================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .wishlist.active {
            animation: heartBeat 0.3s ease;
        }
        
        .wishlist.active i {
            color: #dc3545;
        }
        
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .wishlist-item-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .wishlist-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #dee2e6;
        }
        
        .wishlist-items::-webkit-scrollbar {
            width: 6px;
        }
        
        .wishlist-items::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .wishlist-items::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        .wishlist-items::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    `;
    document.head.appendChild(style);
    
    // Make wishlist accessible globally
    window.wishlist = wishlist;
    
    // Initialize wishlist on page load
    updateWishlistModal();
});
</script>


<script>
// Phone number validation script
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phoneInput');
    const phoneError = document.getElementById('phoneError');
    const profileForm = document.getElementById('editProfileForm');
    const saveBtn = document.getElementById('saveProfileBtn');
    
    if (phoneInput) {
        // Prevent non-numeric input in real-time
        phoneInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/\D/g, '');
            
            // Limit to 11 digits
            if (this.value.length > 11) {
                this.value = this.value.slice(0, 11);
            }
            
            // Real-time validation feedback
            validatePhoneNumber();
        });
        
        // Prevent paste of non-numeric content
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/\D/g, '').slice(0, 11);
            this.value = numericOnly;
            validatePhoneNumber();
        });
        
        // Prevent non-numeric keypress
        phoneInput.addEventListener('keypress', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        
        // Validation on blur
        phoneInput.addEventListener('blur', validatePhoneNumber);
    }
    
    // Validate phone number function
    function validatePhoneNumber() {
        if (!phoneInput) return true;
        
        const phoneValue = phoneInput.value.trim();
        
        // If empty, it's optional - no error
        if (phoneValue === '') {
            phoneInput.classList.remove('is-invalid');
            phoneInput.classList.remove('is-valid');
            return true;
        }
        
        // Check if exactly 11 digits
        if (phoneValue.length !== 11) {
            phoneInput.classList.add('is-invalid');
            phoneInput.classList.remove('is-valid');
            phoneError.textContent = `Phone number must be exactly 11 digits (currently ${phoneValue.length})`;
            return false;
        }
        
        // Check if all characters are digits
        if (!/^\d{11}$/.test(phoneValue)) {
            phoneInput.classList.add('is-invalid');
            phoneInput.classList.remove('is-valid');
            phoneError.textContent = 'Phone number must contain only digits';
            return false;
        }
        
        // Valid phone number
        phoneInput.classList.remove('is-invalid');
        phoneInput.classList.add('is-valid');
        return true;
    }
    
    // Form submission validation
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const phoneValue = phoneInput.value.trim();
            
            // If phone is provided, validate it
            if (phoneValue !== '' && !validatePhoneNumber()) {
                e.preventDefault();
                
                // Show error alert
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Phone Number',
                    text: 'Please enter a valid 11-digit phone number or leave it empty.',
                    confirmButtonColor: '#dc3545'
                });
                
                // Focus on phone input
                phoneInput.focus();
                return false;
            }
        });
    }
});
</script>






<!--- ORDERS --->
<script>
// Define all functions first before using them
function loadOrderItems(orderId, itemsContainer, loadingDiv) {
    // Use GET request instead of POST
    fetch(`../../../app/controllers/OrderController.php?action=getOrderDetails&order_id=${orderId}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.items) {
            displayOrderItems(data.items, itemsContainer);
        } else {
            // Show error message
            itemsContainer.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Unable to load order items.
                </div>
            `;
        }
        
        // Hide loading and show container
        if (loadingDiv) loadingDiv.classList.add('d-none');
        if (itemsContainer) itemsContainer.classList.remove('d-none');
    })
    .catch(error => {
        console.error('Error loading order items:', error);
        
        // Show error message
        itemsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                Error loading order items. Please try again.
            </div>
        `;
        
        // Hide loading and show container
        if (loadingDiv) loadingDiv.classList.add('d-none');
        if (itemsContainer) itemsContainer.classList.remove('d-none');
    });
}

function displayOrderItems(items, container) {
    if (!container) return;
    
    let itemsHtml = '';
    
    items.forEach(item => {
        const itemSize = item.size ? `Size: ${item.size}` : '';
        const itemColor = item.color ? `Color: ${item.color}` : '';
        const attributes = [itemSize, itemColor].filter(attr => attr).join(' | ');
        itemsHtml += `
            <div class="order-item p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-2">
                        ${item.product_image 
                            ? `<img src="/E-COMMERCE/public/uploads/${item.product_image}" 
                                    alt="${item.product_name}" 
                                    class="order-item-image rounded border"
                                    onerror="handleImageError(this)">`
                            : `<div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted order-item-image">
                                    <i class="fas fa-image"></i>
                               </div>`
                        }
                    </div>
                    <div class="col-6">
                        <h6 class="mb-1">${item.product_name}</h6>
                        ${attributes ? `<small class="text-muted">${attributes}</small>` : ''}
                    </div>
                    <div class="col-2 text-center">
                        <span class="fw-semibold">Qty: ${item.quantity}</span>
                    </div>
                    <div class="col-2 text-end">
                        <span class="fw-bold text-success">₱${parseFloat(item.product_price).toFixed(2)}</span>
                        ${item.quantity > 1 ? `<br><small class="text-muted">₱${(parseFloat(item.product_price) * parseInt(item.quantity)).toFixed(2)} total</small>` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = itemsHtml;
}

function handleImageError(img) {
    if (!img) return;
    img.onerror = null; // Prevent infinite loop
    img.src = 'https://via.placeholder.com/60x60?text=No+Image';
    img.classList.add('img-error');
}

function showOrderDetails(orderId, detailsDiv, icon, button) {
    if (!detailsDiv || !icon || !button) return;
    
    // Update button state
    icon.classList.add('rotated');
    button.innerHTML = '<i class="fas fa-chevron-up me-1 details-toggle-icon rotated"></i>Hide Details';
    
    // Show the details div
    detailsDiv.classList.remove('d-none');
    
    // Check if order items are already loaded
    const itemsContainer = detailsDiv.querySelector('.order-items-container');
    const loadingDiv = detailsDiv.querySelector('.order-details-loading');
    
    if (itemsContainer && itemsContainer.children.length === 0) {
        // Load order items via AJAX
        loadOrderItems(orderId, itemsContainer, loadingDiv);
    } else if (loadingDiv && itemsContainer) {
        // Items already loaded, just hide loading
        loadingDiv.classList.add('d-none');
        itemsContainer.classList.remove('d-none');
    }
}

function hideOrderDetails(detailsDiv, icon, button) {
    if (!detailsDiv || !icon || !button) return;
    
    // Update button state
    icon.classList.remove('rotated');
    button.innerHTML = '<i class="fas fa-chevron-down me-1 details-toggle-icon"></i>Show Details';
    
    // Hide the details div
    detailsDiv.classList.add('d-none');
}

// Helper function to capitalize first letter
function ucfirst(str) {
    if (!str) return 'Pending';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// UPDATED: Main function to update order status and payment status
function updateOrderStatusUI(orderId, newStatus, paymentStatus = null) {
    // Find the order card
    const orderCard = document.querySelector(`.order-card[data-id="${orderId}"]`);
    if (!orderCard) return;
    
    // Determine payment status based on order status if not provided
    if (!paymentStatus) {
        paymentStatus = (newStatus === 'delivered') ? 'completed' : 'pending';
    }
    
    // Update the ORDER status badge
    const statusBadge = orderCard.querySelector('.badge.bg-warning, .badge.bg-info, .badge.bg-primary, .badge.bg-success, .badge.bg-danger');
    if (statusBadge) {
        const statusConfig = getStatusConfig(newStatus);
        statusBadge.className = `badge bg-${statusConfig.class} px-3 py-2 fs-6`;
        statusBadge.innerHTML = `<i class="fas fa-${statusConfig.icon} me-2"></i>${statusConfig.text}`;
    }
    
    // Update PAYMENT status in stat-card (the 4th stat card)
    const statCards = orderCard.querySelectorAll('.stat-card');
    if (statCards.length >= 4) {
        const paymentStatCard = statCards[3]; // 4th stat card is payment status
        
        // Update icon and color
        const icon = paymentStatCard.querySelector('i');
        const statusText = paymentStatCard.querySelector('.fw-bold');
        
        if (paymentStatus === 'completed' || paymentStatus === 'paid') {
            if (icon) {
                icon.className = 'fas fa-check-circle text-success fs-4 mb-2';
            }
            if (statusText) {
                statusText.textContent = 'Completed';
            }
        } else {
            if (icon) {
                icon.className = 'fas fa-clock text-warning fs-4 mb-2';
            }
            if (statusText) {
                statusText.textContent = ucfirst(paymentStatus);
            }
        }
    }
    
    // Update payment status in order details section if expanded
    const detailsDiv = document.getElementById(`details-${orderId}`);
    if (detailsDiv && !detailsDiv.classList.contains('d-none')) {
        // Find the payment status badge in Order Information section
        const infoCards = detailsDiv.querySelectorAll('.info-card');
        infoCards.forEach(card => {
            const cardTitle = card.querySelector('h6');
            if (cardTitle && cardTitle.textContent.includes('Order Information')) {
                const paymentStatusBadge = card.querySelector('.badge');
                if (paymentStatusBadge) {
                    if (paymentStatus === 'completed' || paymentStatus === 'paid') {
                        paymentStatusBadge.className = 'badge bg-success';
                        paymentStatusBadge.textContent = 'Completed';
                    } else {
                        paymentStatusBadge.className = 'badge bg-warning';
                        paymentStatusBadge.textContent = ucfirst(paymentStatus);
                    }
                }
            }
        });
    }
    
    // Update data attributes
    orderCard.setAttribute('data-status', newStatus);
    orderCard.setAttribute('data-payment-status', paymentStatus);
    
    // Remove the cancel button if status is cancelled
    const cancelBtn = orderCard.querySelector('button[onclick^="cancelUserOrder"]');
    if (cancelBtn && (newStatus === 'cancelled' || newStatus === 'delivered')) {
        cancelBtn.remove();
    }
    
    // Add visual feedback with appropriate color
    const feedbackColor = (newStatus === 'delivered') ? '#d4edda' : '#f8d7da';
    orderCard.style.backgroundColor = feedbackColor;
    setTimeout(() => {
        orderCard.style.backgroundColor = '';
    }, 2000);
}

function getStatusConfig(status) {
    const statusConfigs = {
        'pending': {class: 'warning', icon: 'clock', text: 'Pending'},
        'processing': {class: 'info', icon: 'cog', text: 'Processing'},
        'shipped': {class: 'primary', icon: 'truck', text: 'Shipped'},
        'delivered': {class: 'success', icon: 'check-circle', text: 'Delivered'},
        'cancelled': {class: 'danger', icon: 'times-circle', text: 'Cancelled'}
    };
    return statusConfigs[status.toLowerCase()] || {class: 'secondary', icon: 'question', text: 'Unknown'};
}

function downloadUserInvoice(orderId) {
    // First fetch order details
    fetch(`../../../app/controllers/OrderController.php?action=getOrderDetails&order_id=${orderId}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.items) {
            // Then fetch order summary
            fetch(`../../../app/controllers/OrderController.php?action=getOrderSummary&order_id=${orderId}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(orderData => {
                if (orderData.success && orderData.order) {
                    // Generate and print invoice
                    generateAndPrintInvoice(orderData.order, data.items);
                    Swal.close();
                } else {
                    throw new Error('Failed to fetch order details');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate invoice: ' + error.message
                });
            });
        } else {
            throw new Error('Failed to fetch order items');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to generate invoice: ' + error.message
        });
    });
}

function generateAndPrintInvoice(order, items) {
    // Determine payment status display
    const displayPaymentStatus = (order.status === 'delivered') ? 'Completed' : (order.payment_status || 'Pending');
    
    // Create invoice HTML with print-specific styles
    const invoiceHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Invoice ${order.order_number || order.id}</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px; 
                    color: #333; 
                    background: white;
                }
                @media print {
                    body { margin: 0; padding: 15mm; }
                    .no-print { display: none !important; }
                    .print-only { display: block !important; }
                    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 25px; 
                    border-bottom: 2px solid #007bff; 
                    padding-bottom: 15px; 
                }
                .logo { 
                    font-size: 24px; 
                    font-weight: bold; 
                    color: #007bff; 
                    margin-bottom: 10px; 
                }
                .invoice-title { 
                    font-size: 28px; 
                    font-weight: bold; 
                    margin: 10px 0; 
                }
                .section { 
                    margin-bottom: 20px; 
                }
                .section-title { 
                    font-size: 18px; 
                    font-weight: bold; 
                    color: #007bff; 
                    margin-bottom: 12px; 
                    border-bottom: 1px solid #eee; 
                    padding-bottom: 5px; 
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-bottom: 20px; 
                }
                th, td { 
                    padding: 10px; 
                    text-align: left; 
                    border-bottom: 1px solid #ddd; 
                }
                th { 
                    background-color: #f8f9fa; 
                    font-weight: bold; 
                }
                .text-right { 
                    text-align: right; 
                }
                .total-row { 
                    font-weight: bold; 
                    background-color: #f8f9fa; 
                }
                .footer { 
                    margin-top: 40px; 
                    padding-top: 15px; 
                    border-top: 2px solid #007bff; 
                    text-align: center; 
                    color: #666; 
                    font-size: 14px; 
                }
                .status-badge { 
                    padding: 4px 8px; 
                    border-radius: 4px; 
                    font-weight: bold; 
                    display: inline-block;
                    margin-left: 8px;
                    font-size: 12px;
                }
                .status-pending { background-color: #fff3cd; color: #856404; }
                .status-processing { background-color: #cce5ff; color: #004085; }
                .status-shipped { background-color: #d1ecf1; color: #0c5460; }
                .status-delivered { background-color: #d4edda; color: #155724; }
                .status-cancelled { background-color: #f8d7da; color: #721c24; }
                .status-completed { background-color: #d4edda; color: #155724; }
                .print-controls { 
                    position: fixed; 
                    bottom: 20px; 
                    right: 20px; 
                    background: white; 
                    padding: 15px; 
                    border-radius: 8px; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
                    z-index: 1000;
                }
                .btn-print {
                    background: #007bff;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .btn-close {
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-left: 10px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">E-COMMERCE STORE</div>
                <div class="invoice-title">INVOICE</div>
                <div>Order #${order.order_number || order.id}</div>
                <div>Date: ${new Date(order.created_at).toLocaleDateString()}</div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 25px;">
                <div style="flex: 1;">
                    <div class="section">
                        <div class="section-title">Billing Information</div>
                        <div><strong>Customer:</strong> ${order.user_name || 'N/A'}</div>
                        <div><strong>Phone:</strong> ${order.user_phone || 'N/A'}</div>
                        <div><strong>Address:</strong> ${order.user_address || 'N/A'}</div>
                    </div>
                </div>
                <div style="flex: 1;">
                    <div class="section">
                        <div class="section-title">Order Information</div>
                        <div><strong>Status:</strong> 
                            <span class="status-badge status-${order.status}">${order.status.toUpperCase()}</span>
                        </div>
                        <div><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</div>
                        <div><strong>Payment Status:</strong> 
                            <span class="status-badge status-${displayPaymentStatus.toLowerCase()}">${displayPaymentStatus.toUpperCase()}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Order Items</div>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>₱${parseFloat(item.product_price).toFixed(2)}</td>
                                <td>${item.quantity}</td>
                                <td class="text-right">₱${(parseFloat(item.product_price) * parseInt(item.quantity)).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td class="text-right">₱${(parseFloat(order.total_amount) - parseFloat(order.delivery_fee || 0)).toFixed(2)}</td>
                        </tr>
                        ${order.delivery_fee > 0 ? `
                        <tr>
                            <td colspan="3" class="text-right"><strong>Delivery Fee:</strong></td>
                            <td class="text-right">₱${parseFloat(order.delivery_fee).toFixed(2)}</td>
                        </tr>
                        ` : ''}
                        <tr class="total-row">
                            <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                            <td class="text-right">₱${parseFloat(order.total_amount).toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="footer">
                <div>Thank you for your purchase!</div>
                <div>Generated on ${new Date().toLocaleDateString()}</div>
            </div>

            <div class="print-controls no-print">
                <button class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Invoice
                </button>
                <button class="btn-close" onclick="window.close()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>

            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                };
                
                window.onafterprint = function() {
                    setTimeout(function() {
                        window.close();
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `;

    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.write(invoiceHtml);
    printWindow.document.close();
    printWindow.focus();
}

function cancelUserOrder(orderId) {
    Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel this order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const cancelBtn = document.querySelector(`button[onclick="cancelUserOrder(${orderId})"]`);
            const originalHtml = cancelBtn?.innerHTML;
            
            if (cancelBtn) {
                cancelBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                cancelBtn.disabled = true;
            }

            fetch('../../../app/controllers/OrderController.php?action=cancelOrder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Cancelled',
                        text: 'Your order has been cancelled successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    updateOrderStatusUI(orderId, 'cancelled', 'cancelled');
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to cancel order'
                    });
                    
                    if (cancelBtn) {
                        cancelBtn.innerHTML = originalHtml;
                        cancelBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while canceling the order.'
                });
                
                if (cancelBtn) {
                    cancelBtn.innerHTML = originalHtml;
                    cancelBtn.disabled = false;
                }
            });
        }
    });
}

// AUTO-REFRESH ORDER STATUSES WHEN MODAL OPENS
document.addEventListener('DOMContentLoaded', function() {
    const ordersModal = document.getElementById('ordersModal');
    
    if (ordersModal) {
        ordersModal.addEventListener('show.bs.modal', function() {
            // Refresh order statuses when modal opens
            refreshOrderStatuses();
        });
    }
    
    // Filter functionality
    const statusFilter = document.getElementById('userOrderStatusFilter');
    const dateFilter = document.getElementById('userOrderDateFilter');
    const searchInput = document.getElementById('userOrderSearch');
    const orderCards = document.querySelectorAll('.order-card');
    const noOrdersDiv = document.querySelector('.no-orders-found');

    function filterOrders() {
        const statusValue = statusFilter?.value || '';
        const dateValue = dateFilter?.value || '';
        const searchValue = searchInput?.value.toLowerCase() || '';
        
        let visibleCount = 0;

        orderCards.forEach(card => {
            const cardStatus = card.dataset.status || '';
            const cardDate = card.dataset.date ? card.dataset.date.split(' ')[0] : '';
            const cardSearch = card.dataset.search || '';

            let showCard = true;

            if (statusValue && cardStatus !== statusValue) {
                showCard = false;
            }

            if (dateValue && cardDate !== dateValue) {
                showCard = false;
            }

            if (searchValue && !cardSearch.includes(searchValue)) {
                showCard = false;
            }

            card.style.display = showCard ? '' : 'none';
            if (showCard) visibleCount++;
        });

        if (noOrdersDiv) {
            if (visibleCount === 0 && orderCards.length > 0) {
                noOrdersDiv.classList.remove('d-none');
            } else {
                noOrdersDiv.classList.add('d-none');
            }
        }

        const footerCount = document.querySelector('.modal-footer small');
        if (footerCount) {
            footerCount.innerHTML = `<i class="fas fa-info-circle me-1"></i>Showing ${visibleCount} order${visibleCount !== 1 ? 's' : ''}`;
        }
    }

    if (statusFilter) statusFilter.addEventListener('change', filterOrders);
    if (dateFilter) dateFilter.addEventListener('change', filterOrders);
    if (searchInput) searchInput.addEventListener('input', filterOrders);

    orderCards.forEach(card => {
        const hoverDiv = card.querySelector('.hover-bg-light');
        if (hoverDiv) {
            hoverDiv.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
            });
            
            hoverDiv.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }
    });

    const detailsButtons = document.querySelectorAll('.toggle-details');
    
    detailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const detailsDiv = document.getElementById(`details-${orderId}`);
            const icon = this.querySelector('.details-toggle-icon');
            const isVisible = !detailsDiv.classList.contains('d-none');
            
            if (isVisible) {
                hideOrderDetails(detailsDiv, icon, this);
            } else {
                showOrderDetails(orderId, detailsDiv, icon, this);
            }
        });
    });
});

// Function to refresh order statuses from server
function refreshOrderStatuses() {
    fetch('../../../app/controllers/OrderController.php?action=getUserOrders')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.orders) {
                updateOrderCards(data.orders);
            }
        })
        .catch(error => {
            console.error('Error refreshing order statuses:', error);
        });
}

// Update order cards with fresh data from server
function updateOrderCards(orders) {
    orders.forEach(order => {
        const orderCard = document.querySelector(`.order-card[data-id="${order.id}"]`);
        if (!orderCard) return;
        
        // Determine payment status
        const paymentStatus = (order.status === 'delivered') ? 'completed' : (order.payment_status || 'pending');
        
        // Update order status badge
        const statusBadge = orderCard.querySelector('.badge.bg-warning, .badge.bg-info, .badge.bg-primary, .badge.bg-success, .badge.bg-danger');
        if (statusBadge) {
            const statusConfig = getStatusConfig(order.status);
            statusBadge.className = `badge bg-${statusConfig.class} px-3 py-2 fs-6`;
            statusBadge.innerHTML = `<i class="fas fa-${statusConfig.icon} me-2"></i>${statusConfig.text}`;
        }
        
        // Update payment status
        const statCards = orderCard.querySelectorAll('.stat-card');
        if (statCards.length >= 4) {
            const paymentStatCard = statCards[3];
            const icon = paymentStatCard.querySelector('i');
            const statusText = paymentStatCard.querySelector('.fw-bold');
            
            if (paymentStatus === 'completed' || paymentStatus === 'paid') {
                if (icon) icon.className = 'fas fa-check-circle text-success fs-4 mb-2';
                if (statusText) statusText.textContent = 'Completed';
            } else {
                if (icon) icon.className = 'fas fa-clock text-warning fs-4 mb-2';
                if (statusText) statusText.textContent = ucfirst(paymentStatus);
            }
        }
        
        // Update data attributes
        orderCard.setAttribute('data-status', order.status);
        orderCard.setAttribute('data-payment-status', paymentStatus);
    });
}

// Add CSS for broken images
const additionalStyles = `
<style>
.order-details {
    background-color: #f8f9fa;
    border-radius: 8px;
    animation: slideDown 0.3s ease-out;
}

.order-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: white;
    transition: all 0.2s ease;
}

.order-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    background-color: #f8f9fa;
}

.toggle-details {
    transition: all 0.3s ease;
}

.details-toggle-icon {
    transition: transform 0.3s ease;
}

.details-toggle-icon.rotated {
    transform: rotate(180deg);
}

.img-error {
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 10px;
    text-align: center;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        max-height: 500px;
        transform: translateY(0);
    }
}

.order-summary {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', additionalStyles);
</script>

<script>
// Load order notifications when modal opens
document.getElementById('notificationsModal').addEventListener('shown.bs.modal', function () {
  loadOrderNotifications();
});

// Function to load order notifications
function loadOrderNotifications() {
  const ordersList = document.getElementById('orderNotificationsList');
  const loadingDiv = document.getElementById('orderNotificationsLoading');
  
  loadingDiv.style.display = 'block';
  
  fetch('../../../app/controllers/OrderController.php?action=getRecentOrders')
    .then(response => response.json())
    .then(data => {
      loadingDiv.style.display = 'none';
      
      if (data.success && data.orders && data.orders.length > 0) {
        displayOrderNotifications(data.orders);
      } else {
        ordersList.innerHTML = `
          <div class="text-center py-4">
            <i class="fas fa-box text-muted" style="font-size: 2rem;"></i>
            <p class="mt-2 text-muted">No order notifications</p>
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error loading order notifications:', error);
      loadingDiv.style.display = 'none';
      ordersList.innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Failed to load order notifications
        </div>
      `;
    });
}

// Function to display order notifications with product details
function displayOrderNotifications(orders) {
  const ordersList = document.getElementById('orderNotificationsList');
  
  let notificationsHtml = orders.map(order => {
    const statusIcon = getStatusIcon(order.status);
    const statusMessage = getStatusMessage(order.status);
    const timeAgo = getTimeAgo(order.updated_at || order.created_at);
    
    // Determine payment status based on order status
    const paymentStatus = (order.status === 'delivered') ? 'completed' : (order.payment_status || 'pending');
    const paymentStatusDisplay = ucfirst(paymentStatus);
    
    return `
      <div class="card border-0 shadow-sm mb-3 notification-item">
        <div class="card-body p-3">
          <div class="d-flex align-items-start justify-content-between mb-2">
            <div class="d-flex align-items-center">
              <div class="me-2">
                ${statusIcon}
              </div>
              <div>
                <h6 class="mb-1 fw-semibold">Order #${order.order_number || order.id}</h6>
                <small class="text-muted">${statusMessage}</small>
              </div>
            </div>
            <div class="text-end">
              <small class="text-muted">${timeAgo}</small>
              <div class="mt-1">
                <span class="badge ${paymentStatus === 'completed' ? 'bg-success' : 'bg-warning'} small">
                  ${paymentStatusDisplay}
                </span>
              </div>
            </div>
          </div>
          
          <div class="bg-light rounded p-2 mb-2">
            <div class="row align-items-center">
              <div class="col">
                <small class="text-muted">
                  <i class="fas fa-box me-1"></i>${order.item_count || 0} item${(order.item_count || 0) !== 1 ? 's' : ''}
                </small>
              </div>
              <div class="col-auto">
                <span class="fw-bold text-success">₱${parseFloat(order.total_amount || 0).toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
              </div>
            </div>
          </div>
          
          <div class="collapse" id="orderDetails${order.id}">
            <div class="border-top pt-2 mt-2">
              <div id="orderItems${order.id}" class="order-items-container">
                <div class="text-center py-2">
                  <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading items...</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <button class="btn btn-sm btn-link p-0 text-decoration-none toggle-order-details" 
                  type="button" 
                  data-bs-toggle="collapse" 
                  data-bs-target="#orderDetails${order.id}"
                  data-orderid="${order.id}"
                  data-loaded="false"
                  aria-expanded="false">
            <i class="fas fa-chevron-down me-1"></i>
            <span class="toggle-text">Show Details</span>
          </button>
        </div>
      </div>
    `;
  }).join('');
  
  ordersList.innerHTML = notificationsHtml;
  addNotificationEventListeners();
}

// Add event listeners for notification interactions
function addNotificationEventListeners() {
  document.querySelectorAll('.toggle-order-details').forEach(button => {
    button.addEventListener('click', function() {
      const orderId = this.dataset.orderid;
      const toggleText = this.querySelector('.toggle-text');
      const icon = this.querySelector('i');
      const isLoaded = this.dataset.loaded === 'true';
      
      setTimeout(() => {
        const collapseElement = document.getElementById(`orderDetails${orderId}`);
        const isExpanded = collapseElement.classList.contains('show') || collapseElement.classList.contains('showing');
        
        if (isExpanded && !isLoaded) {
          loadNotificationOrderItems(orderId);
          this.dataset.loaded = 'true';
        }
        
        if (isExpanded) {
          toggleText.textContent = 'Hide Details';
          icon.className = 'fas fa-chevron-up me-1';
        } else {
          toggleText.textContent = 'Show Details';
          icon.className = 'fas fa-chevron-down me-1';
        }
      }, 10);
    });
  });
  
  document.querySelectorAll('[id^="orderDetails"]').forEach(collapseElement => {
    collapseElement.addEventListener('shown.bs.collapse', function() {
      const orderId = this.id.replace('orderDetails', '');
      const toggleButton = document.querySelector(`[data-orderid="${orderId}"].toggle-order-details`);
      if (toggleButton) {
        const toggleText = toggleButton.querySelector('.toggle-text');
        const icon = toggleButton.querySelector('i');
        toggleText.textContent = 'Hide Details';
        icon.className = 'fas fa-chevron-up me-1';
        
        if (toggleButton.dataset.loaded !== 'true') {
          loadNotificationOrderItems(orderId);
          toggleButton.dataset.loaded = 'true';
        }
      }
    });
    
    collapseElement.addEventListener('hidden.bs.collapse', function() {
      const orderId = this.id.replace('orderDetails', '');
      const toggleButton = document.querySelector(`[data-orderid="${orderId}"].toggle-order-details`);
      if (toggleButton) {
        const toggleText = toggleButton.querySelector('.toggle-text');
        const icon = toggleButton.querySelector('i');
        toggleText.textContent = 'Show Details';
        icon.className = 'fas fa-chevron-down me-1';
      }
    });
  });
  
  document.querySelectorAll('.view-notification-order').forEach(button => {
    button.addEventListener('click', function() {
      const orderId = this.dataset.orderid;
      
      const notificationsModal = bootstrap.Modal.getInstance(document.getElementById('notificationsModal'));
      notificationsModal.hide();
      
      setTimeout(() => {
        loadOrderDetails(orderId);
        const orderDetailsModal = new bootstrap.Modal(document.getElementById('viewUserOrderModal'));
        orderDetailsModal.show();
      }, 300);
    });
  });
}

// Load order items for notification
function loadNotificationOrderItems(orderId) {
  const container = document.getElementById(`orderItems${orderId}`);
  
  fetch(`../../../app/controllers/OrderController.php?action=getOrderDetails&order_id=${orderId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success && data.items) {
        displayNotificationOrderItems(orderId, data.items);
      } else {
        container.innerHTML = `
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Could not load order items
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error loading order items:', error);
      container.innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Error loading order items
        </div>
      `;
    });
}

// Display order items in notification
function displayNotificationOrderItems(orderId, items) {
  const container = document.getElementById(`orderItems${orderId}`);
  
  if (!items || items.length === 0) {
    container.innerHTML = '<p class="text-muted text-center">No items found</p>';
    return;
  }
  
  const itemsHtml = items.map(item => `
    <div class="d-flex align-items-center py-2 border-bottom">
      <div class="flex-shrink-0 me-3">
        ${item.product_image 
          ? `<img src="/E-COMMERCE/public/uploads/${item.product_image}" 
                  alt="${item.product_name}" 
                  class="rounded border"
                  style="width: 50px; height: 50px; object-fit: cover;">`
          : `<div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted" 
                  style="width: 50px; height: 50px;">
                  <i class="fas fa-image"></i>
             </div>`
        }
      </div>
      
      <div class="flex-grow-1 min-width-0">
        <h6 class="mb-1 text-truncate fw-medium">${item.product_name}</h6>
        <div class="d-flex gap-3 small text-muted">
          <span><i class="fas fa-rulers me-1"></i>Size: ${item.size || 'N/A'}</span>
          <span><i class="fas fa-hashtag me-1"></i>Qty: ${item.quantity}</span>
        </div>
      </div>
      
      <div class="flex-shrink-0 text-end">
        <div class="fw-bold text-success">
          ₱${parseFloat(item.subtotal).toLocaleString('en-PH', {minimumFractionDigits: 2})}
        </div>
        <small class="text-muted">₱${parseFloat(item.product_price).toLocaleString('en-PH', {minimumFractionDigits: 2})} each</small>
      </div>
    </div>
  `).join('');
  
  container.innerHTML = itemsHtml;
}

// Helper functions
function getStatusIcon(status) {
  const icons = {
    'pending': '<i class="fas fa-clock text-warning"></i>',
    'processing': '<i class="fas fa-cogs text-info"></i>',
    'shipped': '<i class="fas fa-shipping-fast text-primary"></i>',
    'delivered': '<i class="fas fa-check-circle text-success"></i>',
    'cancelled': '<i class="fas fa-times-circle text-danger"></i>'
  };
  return icons[status] || '<i class="fas fa-box text-secondary"></i>';
}

function getStatusMessage(status) {
  const messages = {
    'pending': 'Your order is waiting for confirmation',
    'processing': 'Your order is being prepared',
    'shipped': 'Your order has been shipped',
    'delivered': 'Your order has been delivered',
    'cancelled': 'Your order was cancelled'
  };
  return messages[status] || 'Order status updated';
}

function getTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);
  
  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
  
  return date.toLocaleDateString('en-PH', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  });
}
</script>



</body>
</html>