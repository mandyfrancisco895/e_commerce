<?php
session_start();

if (ob_get_level()) ob_end_clean();
ob_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../../config/dbcon.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/category.php';
require_once __DIR__ . '/../../models/Order.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';
require_once __DIR__ . '/../../controllers/AdminController.php';

$database = new Database();
$db = $database->getConnection();

// Initialize Models once
$userModel = new User($db); 
$product = new Product($db);
$categoryController = new CategoryController($db);
$adminController = new AdminController($database);

// Load Actions after models are ready
require_once __DIR__ . '/../admin/actions/actions.php'; 

$adminController->validateAdminAccess();

// User Session Sync
$currentUser = [];
if (isset($_SESSION['user_id'])) {
    $currentUser = $userModel->getUserById($_SESSION['user_id']);
    if ($currentUser) {
        $_SESSION['username'] = $currentUser['username'];
        $_SESSION['email'] = $currentUser['email'];
        $_SESSION['role'] = $currentUser['role'] ?? 'staff';
        // ✅ FIXED: Store profile_pic full web path in session for easy access
        if (!empty($currentUser['profile_pic'])) {
            $_SESSION['profile_pic'] = '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
        }
    }
}

// ✅ DEBUG: Uncomment this to see what's in currentUser
// echo "<pre>DEBUG currentUser: "; print_r($currentUser); echo "</pre>";
// echo "<pre>DEBUG SESSION: "; print_r($_SESSION); echo "</pre>";


// ===================================================================
// ✅ AVATAR HELPER FUNCTIONS - For RBAC Profile Picture Display
// ===================================================================

/**
 * Get web-accessible path for avatar
 * @param string|null $profilePic The filename from database
 * @return string|null Web path to the avatar
 */
function getAvatarPath($profilePic) {
    if (empty($profilePic)) {
        return null;
    }
    return '/E-COMMERCE/public/uploads/' . $profilePic;
}

/**
 * Check if avatar file physically exists
 * @param string|null $profilePic The filename from database
 * @return bool True if file exists
 */
function avatarExists($profilePic) {
    if (empty($profilePic)) {
        return false;
    }
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . $profilePic;
    return file_exists($filePath) && is_file($filePath);
}

// ===================================================================

// Global Data Fetching
$topProducts = $adminController->getTopPerformingProducts(5); 
$orderStats = $adminController->getOrderStats();
$dashboardStats = $adminController->getDashboardStats();
$products = $adminController->getAllProducts();
$categories = $adminController->getAllCategories();
$users = $adminController->getAllCustomers();  
$orders = $adminController->getAllOrders();
$activeCategories = $adminController->getActiveCategories();

$stmtMaint = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode' LIMIT 1");
$stmtMaint->execute();
$isMaintActive = $stmtMaint->fetchColumn() ?: '0';

// ✅ FIXED: Include profile_pic column for avatar display
$userQuery = "SELECT id, username, email, role, status, profile_pic FROM users ORDER BY role ASC, id DESC";
$userStmt = $db->prepare($userQuery);
$userStmt->execute();
$allUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Handlers
handleProductActions($product, $categoryController);
handleCategoryActions($categoryController, $product);
handleUserActions($userModel);
handleOrderActions($adminController);
handleCustomerActions($userModel);


// 8. FLASH MESSAGES
if (isset($_SESSION['refresh_shop_stock']) && $_SESSION['refresh_shop_stock'] === true) {
    echo '<script>window.refreshShopStockNeeded = true;</script>';
    unset($_SESSION['refresh_shop_stock']);
}

$db = $database->getConnection();
$product = new Product($db);
$user = new User($db);
$categoryController = new CategoryController($db);
$orderStats = $adminController->getOrderStats();



handleProductActions($product, $categoryController);
handleCategoryActions($categoryController, $product);
handleUserActions($user);
handleOrderActions($adminController);
handleCustomerActions($user);



// Top of admin-dashboard.php
$maintQuery = "SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode' LIMIT 1";
$maintStmt = $db->prepare($maintQuery);
$maintStmt->execute();
$isMaintActive = $maintStmt->fetchColumn(); // This defines the initial badge state


$dashboardStats = $adminController->getDashboardStats();

$products = $adminController->getAllProducts();
$categories = $adminController->getAllCategories();
$users = $adminController->getAllCustomers();  
$orders = $adminController->getAllOrders();

$activeCategories = $adminController->getActiveCategories();




// Add this near the top of admin-dashboard.php
$stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode' LIMIT 1");
$stmt->execute();
$isMaintActive = $stmt->fetchColumn() ?: '0';


// Query to get every individual user from the database
// ✅ FIXED: Include profile_pic column
$userQuery = "SELECT id, username, email, role, status, profile_pic FROM users ORDER BY role ASC, username ASC";
$userStmt = $db->prepare($userQuery);
$userStmt->execute();
$allUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);
// Query to fetch every user individually
// ✅ FIXED: Include profile_pic column
$userQuery = "SELECT id, username, email, role, status, profile_pic FROM users ORDER BY id DESC";
$userStmt = $db->prepare($userQuery);
$userStmt->execute();
$allUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);

// Existing database connection
$database = new Database();
$db = $database->getConnection();

// --- ADD THIS LOGIC HERE TO DEFINE THE VARIABLE ---
$maintQuery = "SELECT setting_value FROM system_settings WHERE setting_key = 'maintenance_mode' LIMIT 1";
$maintStmt = $db->prepare($maintQuery);
$maintStmt->execute();
$isMaintActive = $maintStmt->fetchColumn(); 
// Now $isMaintActive is defined and can be used on line 675




// Calculate additional dashboard metrics
$lowStockThreshold = 10;
$lowStockProducts = array_filter($products, function($product) use ($lowStockThreshold) {
    return $product['stock'] <= $lowStockThreshold && $product['status'] === 'active';
});

$recentOrders = array_slice($orders, 0, 5); // Last 5 orders for recent activity

// Category performance analytics
$categoryPerformance = [];
foreach ($categories as $category) {
    $categoryProducts = array_filter($products, function($product) use ($category) {
        return $product['category_id'] == $category['id'];
    });
    
    $categoryPerformance[] = [
        'name' => $category['name'],
        'total_products' => count($categoryProducts),
        'active_products' => count(array_filter($categoryProducts, function($p) { 
            return $p['status'] === 'active'; 
        })),
        'status' => $category['status'],
        'total_stock' => array_sum(array_column($categoryProducts, 'stock'))
    ];
}

// Monthly revenue calculation (last 6 months)
$monthlyRevenue = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months"));
    
    $monthOrders = array_filter($orders, function($order) use ($month) {
        return strpos($order['created_at'], $month) === 0 && $order['status'] !== 'cancelled';
    });
    
    $monthlyRevenue[] = [
        'month' => $monthName,
        'revenue' => array_sum(array_column($monthOrders, 'total_amount')),
        'orders' => count($monthOrders)
    ];
}

// Order status distribution
$validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
foreach ($validStatuses as $status) {
    $orderStatusCount[$status] = count(array_filter($orders, function($order) use ($status) {
        return $order['status'] === $status;
    }));
}



// User registration trend (last 7 days)
$userRegistrationTrend = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('M j', strtotime("-$i days"));
    
    $dayUsers = array_filter($users, function($user) use ($date) {
        return strpos($user['created_at'], $date) === 0;
    });
    
    $userRegistrationTrend[] = [
        'date' => $dayName,
        'registrations' => count($dayUsers)
    ];
}

// Handle Create User and Log to Audit via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_user_with_log') {
    header('Content-Type: application/json');
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $password = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
    $admin_name = $_SESSION['username'] ?? 'Admin'; // Current logged-in admin

    try {
        $db->beginTransaction();

        // 1. Insert into Users Table
        $stmtUser = $db->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, 'Active')");
        $stmtUser->execute([$username, $email, $password, $role]);

        // 2. Insert into maintenance_logs (The Audit Log)
        $actionMessage = "Created new account: $username (Role: " . ucfirst($role) . ")";
        $stmtLog = $db->prepare("INSERT INTO maintenance_logs (admin_name, action_performed, status) VALUES (?, ?, 'Success')");
        $stmtLog->execute([$admin_name, $actionMessage]);

        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Fetch all users for the table
$userStmt = $db->prepare("SELECT id, username, email, role, status FROM users ORDER BY id DESC");
$userStmt->execute();
$allUsers = $userStmt->fetchAll(PDO::FETCH_ASSOC);


// ✅ DIRECT DATABASE QUERY: Calculate new customers today
$newCustomersToday = 0;
date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

try {
    $sql = "SELECT COUNT(*) as count 
            FROM users 
            WHERE role = 'user' 
            AND DATE(created_at) = :today";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $newCustomersToday = $result['count'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Error counting new customers: " . $e->getMessage());
    $newCustomersToday = 0;
}

// Continue with your existing code...
$alerts = [];

if (count($lowStockProducts) > 0) {
    $alerts[] = [
        'type' => 'warning',
        'title' => 'Low Stock Alert',
        'message' => count($lowStockProducts) . ' products are running low on stock',
        'action' => 'View Products',
        'count' => count($lowStockProducts)
    ];
}

$pendingOrdersCount = $orderStatusCount['pending'] ?? 0;
if ($pendingOrdersCount > 0) {
    $alerts[] = [
        'type' => 'info',
        'title' => 'Pending Orders',
        'message' => $pendingOrdersCount . ' orders are waiting for confirmation',
        'action' => 'View Orders',
        'count' => $pendingOrdersCount
    ];
}

$inactiveCategoriesWithProducts = array_filter($categories, function($cat) {
    return $cat['status'] === 'inactive' && $cat['total_product_count'] > 0;
});

if (count($inactiveCategoriesWithProducts) > 0) {
    $alerts[] = [
        'type' => 'warning',
        'title' => 'Inactive Categories',
        'message' => count($inactiveCategoriesWithProducts) . ' inactive categories still have products',
        'action' => 'Review Categories',
        'count' => count($inactiveCategoriesWithProducts)
    ];
}



$dashboardCards = [
    [
        'title' => 'Total Products',
        'value' => $dashboardStats['total_products'],
        'subtitle' => $dashboardStats['active_products'] . ' active',
        'icon' => 'fa-box',
        'color' => 'primary',
        'change' => '+' . count(array_filter($products, function($p) {
            return strtotime($p['created_at']) > strtotime('-7 days');
        })) . ' this week'
    ],
    [
        'title' => 'Total Categories',
        'value' => $dashboardStats['total_categories'],
        'subtitle' => $dashboardStats['active_categories'] . ' active',
        'icon' => 'fa-tags',
        'color' => 'success',
        'change' => ''
    ],
    [
        'title' => 'Total Users',
        'value' => $dashboardStats['total_users'],
        'subtitle' => $dashboardStats['active_users'] . ' active',
        'icon' => 'fa-users',
        'color' => 'info',
        'change' => '+' . count(array_filter($users, function($u) {
            return strtotime($u['created_at']) > strtotime('-7 days');
        })) . ' this week'
    ],
    [
        'title' => 'Total Orders',
        'value' => $dashboardStats['total_orders'],
        'subtitle' => '₱' . number_format($dashboardStats['total_revenue'], 2) . ' revenue',
        'icon' => 'fa-shopping-cart',
        'color' => 'warning',
        'change' => $pendingOrdersCount . ' pending'
    ]
];

$chartData = [
    'monthlyRevenue' => $monthlyRevenue,
    'orderStatus' => $orderStatusCount,
    'userRegistrations' => $userRegistrationTrend,
    'categoryPerformance' => $categoryPerformance
];



?>



<?php
// Loop through the posted data and update each key specifically
// Siguraduhin na ang $db variable ay nakuha mula sa database connection
if (isset($_POST['save_settings'])) {
    foreach ($_POST as $key => $value) {
        if ($key === 'save_settings') continue; // Laktawan ang button name
        
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    $_SESSION['success_message'] = "System settings updated successfully!";
    header("Location: admin-dashboard.php#settings");
    exit();
}
?>








<?php

$currentUser = [
    'username' => $_SESSION['username'] ?? 'Admin',
    'email' => $_SESSION['email'] ?? 'admin@example.com',
    'role' => $_SESSION['role'] ?? 'admin',
    'avatar' => strtoupper(substr($_SESSION['username'] ?? 'AD', 0, 2))
];

$currentPage = 'Dashboard';
if (isset($_GET['page'])) {
    switch ($_GET['page']) {
        case 'products':
            $currentPage = 'Product Management';
            break;
        case 'categories':
            $currentPage = 'Category Management';
            break;
        case 'users':
            $currentPage = 'User Management';
            break;
        case 'orders':
            $currentPage = 'Order Management';
            break;
        default:
            $currentPage = 'Dashboard';
    }
}

$totalNotifications = count($alerts);

$notificationItems = [];
foreach ($alerts as $alert) {
    $icon = '';
    switch ($alert['type']) {
        case 'warning':
            $icon = 'bi-exclamation-triangle text-warning';
            break;
        case 'info':
            $icon = 'bi-info-circle text-info';
            break;
        case 'success':
            $icon = 'bi-check-circle text-success';
            break;
        case 'danger':
            $icon = 'bi-x-circle text-danger';
            break;
        default:
            $icon = 'bi-bell text-secondary';
    }
    
    $notificationItems[] = [
        'icon' => $icon,
        'title' => $alert['title'],
        'message' => $alert['message'],
        'action' => $alert['action'] ?? '',
        'count' => $alert['count'] ?? 0,
        'type' => $alert['type']
    ];
}

if (empty($notificationItems)) {
    if (!empty($recentOrders)) {
        $recentOrder = $recentOrders[0];
        $notificationItems[] = [
            'icon' => 'bi-cart-check text-success',
            'title' => 'Recent Order',
            'message' => 'Order #' . $recentOrder['id'] . ' received',
            'action' => 'View Orders',
            'count' => 1,
            'type' => 'info'
        ];
    }
    
    if (empty($notificationItems)) {
        $notificationItems[] = [
            'icon' => 'bi-check-circle text-success',
            'title' => 'All Good!',
            'message' => 'No new notifications',
            'action' => '',
            'count' => 0,
            'type' => 'success'
        ];
        $totalNotifications = 0;
    }
}

$notificationItems = array_slice($notificationItems, 0, 5);


require_once __DIR__ . '/../../../app/views/includes/sidebar.php';
require_once __DIR__ . '/../../../app/views/includes/adminmodal.php';


?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ecommerce</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"> 
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../../public/css/admin-dashboards.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

</head>

<body>

    <div class="alert-container" style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 300px;">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-dismissible fade show" role="alert" style="background: white; border: 2px solid #28a745; color: #155724; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2" style="color: #28a745; font-size: 1.2rem;"></i>
                    <span style="flex: 1;"><?= $_SESSION['success_message'] ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-dismissible fade show" role="alert" style="background: white; border: 2px solid #dc3545; color: #721c24; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2" style="color: #dc3545; font-size: 1.2rem;"></i>
                    <span style="flex: 1;"><?= $_SESSION['error_message'] ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.8rem;"></button>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Dynamic Top Bar -->
        <div class="top-bar">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle me-3" data-bs-toggle="collapse" data-bs-target="#sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h4 class="mb-0 fw-bold" id="page-title"><?php echo htmlspecialchars($currentPage); ?></h4>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Dynamic Notifications Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <?php if ($totalNotifications > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $totalNotifications > 99 ? '99+' : $totalNotifications; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 300px;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Notifications</span>
                            <?php if ($totalNotifications > 0): ?>
                                <small class="text-muted"><?php echo $totalNotifications; ?> new</small>
                            <?php endif; ?>
                        </li>
                        
                        <?php if ($totalNotifications > 0): ?>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <?php foreach ($notificationItems as $notification): ?>
                            <li>
                                <a class="dropdown-item py-2 px-3" href="#" 
                                <?php if (!empty($notification['action'])): ?>
                                    onclick="handleNotificationClick('<?php echo strtolower(str_replace(' ', '_', $notification['action'])); ?>')"
                                <?php endif; ?>>
                                    <div class="d-flex align-items-start">
                                        <i class="<?php echo $notification['icon']; ?> me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($notification['message']); ?></div>
                                            <?php if ($notification['count'] > 0): ?>
                                                <span class="badge bg-<?php echo $notification['type'] === 'warning' ? 'warning' : 'primary'; ?> rounded-pill small">
                                                    <?php echo $notification['count']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <?php if (!empty($notification['action'])): ?>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if ($totalNotifications > 5): ?>
                            <li>
                                <a class="dropdown-item text-center small text-primary" href="#" onclick="viewAllNotifications()">
                                    View All Notifications
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>



                <!-- Admin User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center position-relative"
                            style="width: 40px; height: 40px; font-weight: 600;">
                            <?php 
                            // Get avatar data - Enhanced approach with database priority
                            $avatarPath = '';
                            $webPath = ''; 
                            $avatarInitial = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1));
                            
                            // Priority order: Database -> Session -> Default
                            // 1. First check current user data from database (most reliable)
                            if (!empty($currentUser['profile_pic'])) {
                                $webPath = '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
                                $avatarPath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . $currentUser['profile_pic'];
                                
                                // Update session with database value for consistency
                                $_SESSION['profile_pic'] = $webPath;
                            }
                            // 2. Fallback to session data
                            elseif (!empty($_SESSION['profile_pic'])) {
                                $webPath = $_SESSION['profile_pic'];
                                $avatarPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['profile_pic'];
                            } 
                            elseif (!empty($_SESSION['avatar'])) {
                                $webPath = $_SESSION['avatar'];
                                $avatarPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['avatar'];
                            }
                            
                            // Verify the file actually exists on the server
                            $showImage = !empty($avatarPath) && file_exists($avatarPath) && is_file($avatarPath);
                            
                            // Debug output (remove this after testing)
                            if (isset($_GET['debug'])) {
                                echo "<!-- DEBUG: User ID: " . ($_SESSION['user_id'] ?? 'None') . " -->";
                                echo "<!-- DEBUG: DB Profile Pic: " . htmlspecialchars($currentUser['profile_pic'] ?? 'None') . " -->";
                                echo "<!-- DEBUG: Web Path: " . htmlspecialchars($webPath) . " -->";
                                echo "<!-- DEBUG: Avatar Path: " . htmlspecialchars($avatarPath) . " -->";
                                echo "<!-- DEBUG: File Exists: " . ($showImage ? 'Yes' : 'No') . " -->";
                                echo "<!-- DEBUG: Session Profile Pic: " . htmlspecialchars($_SESSION['profile_pic'] ?? 'None') . " -->";
                            }
                            ?>
                            
                            <?php if ($showImage): ?>
                                <img src="<?php echo htmlspecialchars($webPath); ?>?v=<?php echo time(); ?>"
                                    class="rounded-circle"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                    alt="Profile Picture"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <span style="font-size: 16px; font-weight: 600; display: none;"><?php echo htmlspecialchars($avatarInitial); ?></span>
                            <?php else: ?>
                                <span style="font-size: 16px; font-weight: 600;"><?php echo htmlspecialchars($avatarInitial); ?></span>
                            <?php endif; ?>
                        </div>
                    </button>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <!-- Enhanced dropdown header with profile info -->
                        <li class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                    style="width: 32px; height: 32px; font-weight: 600; font-size: 14px;">
                                    <?php if ($showImage): ?>
                                        <img src="<?php echo htmlspecialchars($webPath); ?>?v=<?php echo time(); ?>"
                                            class="rounded-circle"
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                            alt="Profile Picture"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <span style="display: none;"><?php echo htmlspecialchars($avatarInitial); ?></span>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($avatarInitial); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($_SESSION['email'] ?? 'email@example.com'); ?></small>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="openProfileModal()">
                                <i class="bi bi-person me-2"></i>View Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../../../app/controllers/AdminAuthController.php?action=admin_logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>


        <!-- Content Area    maintenance -->
       <div class="content-area">
    <div class="content">
    <div id="maintenance-section" class="content-section fade-in">
        <div class="container-fluid p-0">
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <div>
                    <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-tools me-2 text-primary"></i>System Operations Control</h4>
                    <p class="text-muted mb-0">Manage global site availability and system-wide messaging.</p>
                </div>
                <div id="live-status-badge">
                    <?php echo ($isMaintActive == '1') ? 
                        '<span class="badge bg-danger p-3 px-4 shadow-sm animate-pulse"><i class="fas fa-exclamation-triangle me-2"></i>Maintenance Mode Active</span>' : 
                        '<span class="badge bg-success p-3 px-4 shadow-sm"><i class="fas fa-check-circle me-2"></i>System Operational</span>'; 
                    ?>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-cog me-2"></i>Master Configuration</h6>
                            <a href="../maintenance_screen.php" target="_blank" class="btn btn-link btn-sm text-decoration-none"><i class="fas fa-eye me-1"></i> Preview Screen</a>
                        </div>
                        <div class="card-body p-4">
                            <form id="settingsForm">
                                <div class="p-4 border rounded-3 mb-4 bg-light shadow-inner">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark">Global Maintenance Mode</h6>
                                            <p class="small text-muted mb-0">Redirect all public traffic to the optimization screen immediately.</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="maintSwitch" name="maintenance_mode" value="1" 
                                            <?php echo ($isMaintActive == '1') ? 'checked' : ''; ?> 
                                            style="width: 4rem; height: 2rem; cursor: pointer;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold small text-muted">PUBLIC NOTICE MESSAGE</label>
                                        <textarea class="form-control border-2 shadow-none" id="maint_message_input" name="maint_message" rows="3" placeholder="Describe the current optimization..."><?php echo htmlspecialchars($settings['maint_message'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">TARGET RECOVERY TIME</label>
                                        <input type="datetime-local" 
                                            class="form-control border-2 shadow-none" 
                                            id="recovery_time_input" 
                                            name="recovery_time" 
                                            value="<?php echo htmlspecialchars($settings['recovery_time'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-muted">QUICK ACTIONS</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-secondary w-100" onclick="setQuickTime(1)">+1 Hr</button>
                                            <button type="button" class="btn btn-outline-secondary w-100" onclick="setQuickTime(3)">+3 Hr</button>
                                            <button type="button" class="btn btn-outline-secondary w-100" onclick="setQuickTime(24)">+24 Hr</button>
                                        </div>
                                    </div>
                                </div>

                                <div id="maint-error-msg" class="text-danger small mt-2 d-none">
                                    <i class="fas fa-info-circle me-1"></i> Public notice and recovery time are required to enable maintenance.
                                </div>

                                <div class="mt-5 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <div id="validation-notice" class="text-muted small">
                                        </div>
                                    <button type="submit" class="btn btn-primary px-5 py-3 fw-bold shadow-sm" id="saveSystemBtn">
                                        Synchronize & Update System Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold"><i class="fas fa-history me-2"></i>Recent System Operations Audit Log</h6>
                            <button class="btn btn-sm btn-light border" onclick="loadMaintLogs()">Refresh Logs</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                            <th class="ps-4">Operation</th>
                            <th>Admin</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                            <th class="text-end pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody id="maintAuditLog">
                        <?php
                                                try {
                                                    // We use $db which you already defined at the top of admin-dashboard.php
                                                    $logQuery = "SELECT * FROM system_audit_logs ORDER BY created_at DESC LIMIT 10";
                                                    $stmt = $db->prepare($logQuery);
                                                    $stmt->execute();
                                                    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                    if ($logs): 
                                                        foreach ($logs as $log): ?>
                                                        <tr>
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="bg-light text-primary me-3 px-2 py-1 rounded">
                                                                        <i class="fas fa-tools small"></i>
                                                                    </div>
                                                                    <span class="fw-bold small"><?= htmlspecialchars($log['operation']) ?></span>
                                                                </div>
                                                            </td>
                                                            <td class="small"><?= htmlspecialchars($log['admin_user']) ?></td>
                                                            <td>
                                                                <span class="text-muted small text-truncate d-inline-block" style="max-width: 300px;">
                                                                    <?= htmlspecialchars($log['details']) ?>
                                                                </span>
                                                            </td>
                                                            <td class="small text-muted">
                                                                <i class="bi bi-clock me-1"></i><?= date('M d, g:i A', strtotime($log['created_at'])) ?>
                                                            </td>
                                                            <td class="text-end pe-4">
                                                                <span class="badge bg-success-subtle text-success border border-success px-3">
                                                                    <?= htmlspecialchars($log['status'] ?? 'Completed') ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; 
                                                    else: ?>
                                                        <tr><td colspan="5" class="text-center py-5 text-muted small">No recent operations found.</td></tr>
                                                    <?php endif; 
                                                } catch (PDOException $e) {
                                                    echo '<tr><td colspan="5">Error: ' . $e->getMessage() . '</td></tr>';
                                                }
                                                ?>
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function loadMaintLogs() {
    fetch('get_maint_logs.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('maintAuditLog').innerHTML = data;
        });
}

// Update every 30 seconds
setInterval(loadMaintLogs, 30000);

// Initialize immediately
document.addEventListener('DOMContentLoaded', loadMaintLogs);


</script>


<div class="container-fluid py-1">
    <div id="backup-section" class="row justify-content-center content-section fade-in">
        <div class="col-lg-10">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="fw-bold text-dark">System Database </h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item active">Database Backup</li>
                        </ol>
                    </nav>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i> System Secure
                </span>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-8 p-5">
                            <h4 class="fw-bold mb-3">Database Snapshot</h4>
                            <p class="text-muted mb-4">
                                Securely export your entire system database including <strong>Products, Users, Orders, and Stock Movements</strong>. 
                                This process generates a professional SQL dump compatible with MySQL.
                            </p>
                            
                            <div class="d-flex gap-3 align-items-center">
                                <button id="btnBackup" class="btn btn-primary btn-lg px-4 py-2 shadow-sm" onclick="handleBackupGeneration()">
                                    <i class="bi bi-cloud-download me-2"></i>Generate & Download Backup
                                </button>
                                <div id="backupStatus" class="flex-grow-1"></div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i> 
                                    Last backup performed: <span id="lastBackupDate" class="text-dark fw-medium">Checking...</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 bg-light border-start p-4 d-none d-md-block">
                            <h6 class="text-uppercase fw-bold text-muted mb-4" style="font-size: 0.7rem; letter-spacing: 1px;">
                                Security Overview
                            </h6>
                            
                            <div class="p-3 mb-3 rounded-3 bg-white border shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success rounded-circle" style="width: 10px; height: 10px; box-shadow: 0 0 8px rgba(25, 135, 84, 0.6);"></div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-0 fw-bold small text-dark">Database Engine</p>
                                        <span class="text-success fw-medium" style="font-size: 0.7rem;">Status: Online & Encrypted</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 mb-3 rounded-3 bg-white border shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-arrow-repeat text-primary me-3 fs-5"></i>
                                    <div>
                                        <h6 class="mb-1 small fw-bold text-dark">Snapshot Rotation</h6>
                                        <p class="mb-0 text-muted" style="font-size: 0.7rem; line-height: 1.4;">
                                            Retention active for <strong class="text-dark">5 archives</strong>. Oldest files are automatically cycled.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 bg-white border shadow-sm">
                                <p class="text-uppercase fw-bold text-muted mb-2" style="font-size: 0.6rem; letter-spacing: 0.5px;">Authorized Operator</p>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-subtle rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="bi bi-person-badge text-primary" style="font-size: 1rem;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 small fw-bold text-dark"><?php echo $_SESSION['username'] ?? 'Admin'; ?></p>
                                        <span class="text-muted" style="font-size: 0.65rem;">Active Session Secure</span>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box mb-3 text-primary"><i class="bi bi-clock-history fs-4"></i></div>
                            <h6 class="fw-bold">Automatic Naming</h6>
                            <p class="small text-muted mb-0">Backups follow the convention: <code>backup_YYYY-MM-DD_HH-MM.sql</code></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box mb-3 text-primary"><i class="bi bi-lock fs-4"></i></div>
                            <h6 class="fw-bold">RBAC Protected</h6>
                            <p class="small text-muted mb-0">Only high-level Administrators can trigger or access database snapshots.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="icon-box mb-3 text-primary"><i class="bi bi-journal-text fs-4"></i></div>
                            <h6 class="fw-bold">Audit Logging</h6>
                            <p class="small text-muted mb-0">Every backup action is recorded internally for security monitoring.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-shield-check text-primary me-2"></i>Database Backup History
                    </h5>
                    <span id="lastBackupBadge" class="badge rounded-pill bg-light text-secondary border px-3">
                        Loading status...
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small text-uppercase">
                                    <th class="border-0 ps-0">Backup Date</th>
                                    <th class="border-0">Filename</th>
                                    <th class="border-0">File Size</th>
                                    <th class="border-0 text-end pe-0">Action</th>
                                </tr>
                            </thead>
                            <tbody id="backupHistoryBody">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card { transition: transform 0.2s ease; }
    .table thead th { font-weight: 600; letter-spacing: 0.5px; }
    #backupHistoryBody tr:hover { background-color: rgba(0,0,0,0.01); }
    .btn-outline-primary:hover { background-color: #f0f7ff; color: #0d6efd; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
</style>



<div id="rbac-section" class="content-section fade-in d-none">
    <div class="container-fluid p-0">   
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-lock me-2 text-primary"></i>Role-Based Access Control</h4>
                <p class="text-muted mb-0">Manage system-wide permissions and administrative distribution.</p>
            </div>
            <div class="d-flex gap-2">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="userSearchInput" class="form-control bg-light border-start-0" placeholder="Search users...">
                </div>
                <select class="form-select bg-light" id="roleFilter" style="width: 150px;">
                    <option value="all">All Management</option>
                    <option value="admin">Admins Only</option>
                    <option value="staff">Staff Only</option>
                </select>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="bi bi-plus-lg me-1"></i> Create New Role
                </button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 fw-bold"><i class="bi bi-people me-2"></i>Active Administrator Roles</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Administrator / User</th>
                                        <th>Email Address</th>
                                        <th>Assigned Role</th>
                                        <th>Account Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rbacTableBody">
                                    <?php 
                                    if (!empty($allUsers)): 
                                        foreach ($allUsers as $user): 
                                            $user_role = strtolower($user['role'] ?? '');
                                            $user_id = $user['id'] ?? 0;
                                            $current_admin_id = $_SESSION['user_id'] ?? 0;

                                            if ($user_role === 'user' && $user_id != $current_admin_id) {
                                                continue;
                                            }

                                            $is_admin = ($user_role === 'admin' || $user_role === 'administrator');
                                            $status = $user['status'] ?? 'Active';
                                            $status_class = ($status === 'Active') ? 'bg-success' : 'bg-danger';
                                            
                                            // ✅ FIXED: Use helper functions for avatar handling
                                            $profilePic = $user['profile_pic'] ?? null;
                                            $hasAvatar = avatarExists($profilePic);
                                            $avatarPath = $hasAvatar ? getAvatarPath($profilePic) : null;
                                    ?>
                                        <tr class="user-row" data-role="<?php echo $user_role; ?>">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($hasAvatar): ?>
                                                        <img src="<?php echo htmlspecialchars($avatarPath); ?>" 
                                                            alt="<?php echo htmlspecialchars($user['username']); ?>" 
                                                            class="rounded-circle me-3 object-fit-cover shadow-sm" 
                                                            style="width: 40px; height: 40px; border: 2px solid #fff;"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <!-- Fallback to initials if image fails to load -->
                                                        <div class="bg-primary text-white rounded-circle d-none align-items-center justify-content-center me-3 shadow-sm" 
                                                            style="width: 40px; height: 40px; font-weight: 600; font-size: 1.1rem;">
                                                            <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" 
                                                            style="width: 40px; height: 40px; font-weight: 600; font-size: 1.1rem;">
                                                            <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold text-dark">
                                                            <?php echo htmlspecialchars($user['username']); ?>
                                                            <?php if($user_id == $current_admin_id): ?>
                                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;">YOU</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted">ID: #<?php echo $user_id; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="text-muted"><?php echo htmlspecialchars($user['email']); ?></span></td>
                                            <td>
                                                <span class="badge rounded-pill <?php echo $is_admin ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info'; ?> px-3">
                                                    <?php echo ucfirst(htmlspecialchars($user_role)); ?>
                                                </span>
                                            </td>
                                            <td><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                            <td class="text-end pe-4">
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots-vertical"></i> actions
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                        <?php if($user_id != $current_admin_id): ?>
                                                            <li>
                                                                <form method="POST">
                                                                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                                    <input type="hidden" name="status" value="<?= ($status === 'Active') ? 'Blocked' : 'Active'; ?>">
                                                                    <button type="submit" name="update_user_status" class="dropdown-item <?= ($status === 'Active') ? 'text-warning' : 'text-success'; ?>">
                                                                        <i class="bi bi-<?= ($status === 'Active') ? 'lock' : 'unlock'; ?> me-2"></i><?= ($status === 'Active') ? 'Revoke Access' : 'Restore Access'; ?>
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST">
                                                                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                                                    <button type="submit" name="delete_user" class="dropdown-item text-danger" onclick="return confirm('Delete permanently?')">
                                                                        <i class="bi bi-trash me-2"></i>Delete Account
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <tr><td colspan="5" class="text-center py-4 text-muted">No accounts found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    const roleFilter = document.getElementById('roleFilter');
    const userRows = document.querySelectorAll('.user-row');

    if (roleFilter) {
        roleFilter.addEventListener('change', function() {
            const selectedRole = this.value.toLowerCase(); // 'all', 'admin', o 'staff'

            userRows.forEach(row => {
                const rowRole = row.getAttribute('data-role').toLowerCase();

                if (selectedRole === 'all') {
                    
                    row.style.display = '';
                } else if (rowRole === selectedRole) {
                    
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');
    const roleFilter = document.getElementById('roleFilter');
    const rows = document.querySelectorAll('.user-row');

    function performFilter() {
        const query = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value.toLowerCase();

        rows.forEach(row => {
            const name = row.querySelector('.user-name-text').textContent.toLowerCase();
            const email = row.querySelector('.user-email-text').textContent.toLowerCase();
            const role = row.getAttribute('data-role');

            const matchesSearch = name.includes(query) || email.includes(query);
            const matchesRole = (selectedRole === 'all') || (role === selectedRole);

            row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', performFilter);
    roleFilter.addEventListener('change', performFilter);
});

document.addEventListener('DOMContentLoaded', function() {
    // 1. Target the correct Input and Table Body
    const searchInput = document.getElementById('userSearchInput');
    const tableBody = document.getElementById('rbacTableBody'); 

    if (!searchInput || !tableBody) return; // Safety check

    searchInput.addEventListener('keyup', function() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = tableBody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            
            // Skip the "No results" row if it exists
            if (row.id === 'no-results-row') continue;

            // Get text content from the row
            const text = row.textContent.toLowerCase();

            if (text.includes(searchTerm)) {
                row.style.display = ""; // Show row
            } else {
                row.style.display = "none"; // Hide row
            }
        }
        
        handleNoResults(rows, searchTerm);
    });

    function handleNoResults(rows, term) {
        let visibleCount = 0;
        // Check how many data rows are visible
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== "none" && rows[i].id !== 'no-results-row') {
                visibleCount++;
            }
        }

        const existingMsg = document.getElementById('no-results-row');
        if (existingMsg) existingMsg.remove();

        if (visibleCount === 0 && term !== "") {
            const noResultsRow = document.createElement('tr');
            noResultsRow.id = 'no-results-row';
            // Match the colspan to your table (which is 5 columns)
            noResultsRow.innerHTML = `<td colspan="5" class="text-center py-4 text-muted">No users matching "${term}"</td>`;
            tableBody.appendChild(noResultsRow);
        }
    }
});

    </script>


            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section fade-in">
                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Dashboard Overview</h4>
                        <p class="text-muted mb-0">Real-time business insights and key performance indicators</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" data-period="today" onclick="changeDashboardPeriod('today', this)">Today</button>
                            <button class="btn btn-outline-primary" data-period="week" onclick="changeDashboardPeriod('week', this)">This Week</button>
                            <button class="btn btn-outline-primary" data-period="month" onclick="changeDashboardPeriod('month', this)">This Month</button>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="refreshDashboard()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Key Performance Indicators -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-currency-dollar display-6 text-primary"></i>
                            </div>
                            <div class="metric-value text-primary" id="dashboardRevenue">₱<?php
                                    // Calculate actual total revenue from users
                                    if (is_array($users) && count($users) > 0) {
                                        $actualTotalRevenue = array_sum(array_column($users, 'total_spent'));
                                        echo number_format($actualTotalRevenue, 2);
                                    } else {
                                        echo '0.00';
                                    }
                                ?></div>
                            <div class="metric-label">Total Revenue</div>
                            <div class="trend-indicator trend-up" id="revenueTrend">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+12.5% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="dashboardRevenueSparkline" width="200" height="40"></canvas>
                            </div>
                            <div class="metric-footer">
                                <small class="text-muted">Last updated: <span id="revenueLastUpdate">Just now</span></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-bag-check display-6 text-success"></i>
                            </div>
                            <div class="metric-value text-success" id="dashboardOrders"><?= $dashboardStats['total_orders'] ?? 0 ?></div>
                            <div class="metric-label">Total Orders</div>
                            <div class="trend-indicator trend-up" id="ordersTrend">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+8.2% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="dashboardOrdersSparkline" width="200" height="40"></canvas>
                            </div>
                            <div class="metric-footer">
                                <small class="text-muted">Pending: <span id="pendingOrders"><?= $orderStats['pending_orders'] ?? 0 ?></span></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-box-seam display-6 text-info"></i>
                            </div>
                            <div class="metric-value text-info" id="dashboardProducts"><?= $dashboardStats['total_products'] ?? 0 ?></div>
                            <div class="metric-label">Total Products</div>
                            <div class="trend-indicator trend-neutral" id="productsTrend">
                                <i class="bi bi-dash me-1"></i>
                                <span>No change</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="dashboardProductsSparkline" width="200" height="40"></canvas>
                            </div>
                            <div class="metric-footer">
                                <small class="text-muted">Active: <span id="activeProducts"><?= $dashboardStats['active_products'] ?? 0 ?></span></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="dashboard-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-people display-6 text-warning"></i>
                            </div>
                            <div class="metric-value text-warning" id="dashboardCustomers"><?= $dashboardStats['total_users'] ?? 0 ?></div>
                            <div class="metric-label">Total Customers</div>
                            <div class="trend-indicator trend-up" id="customersTrend">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+15.3% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="dashboardCustomersSparkline" width="200" height="40"></canvas>
                            </div>
                            <div class="metric-footer">
                            <small class="text-muted">New today: <span class=""><?= $newCustomersToday ?></span>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert System -->
                <div id="dashboardAlerts" class="mb-4">
                    <?php if (!empty($alerts)): ?>
                        <?php foreach ($alerts as $alert): ?>
                            <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show d-flex align-items-center" role="alert">
                                <i class="bi <?= $alert['type'] === 'warning' ? 'bi-exclamation-triangle' : 'bi-info-circle' ?> me-2"></i>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($alert['title']) ?>:</strong> 
                                    <?= htmlspecialchars($alert['message']) ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-<?= $alert['type'] ?> me-2" 
                                        onclick="handleAlertAction('<?= strtolower(str_replace(' ', '_', $alert['action'])) ?>')">
                                    <?= htmlspecialchars($alert['action']) ?>
                                </button>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Main Dashboard Charts -->
                <div class="row g-4 mb-4">
                    <!-- Sales Overview Chart -->
                    <div class="col-lg-8">
                        <div class="dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-graph-up me-2"></i>Sales Overview
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active" onclick="toggleSalesChart('daily', this)">Daily</button>
                                    <button class="btn btn-outline-primary" onclick="toggleSalesChart('weekly', this)">Weekly</button>
                                    <button class="btn btn-outline-primary" onclick="toggleSalesChart('monthly', this)">Monthly</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="dashboardSalesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats & Order Status -->
                    <div class="col-lg-4">
                        <div class="analytics-card">
                            <div class="card-header">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-speedometer2 me-2"></i>Quick Stats
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Order Status Mini Chart -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Order Status</span>
                                        <small class="text-muted">Today</small>
                                    </div>
                                    <div class="chart-container-mini">
                                        <canvas id="dashboardOrderStatusMini"></canvas>
                                    </div>
                                </div>

                                <!-- Quick Metrics -->
                                <div class="quick-metrics">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: var(--bs-success);">
                                                <i class="bi bi-check-circle-fill text-white" style="font-size: 12px;"></i>
                                            </div>
                                            <small>Completed Orders</small>
                                        </div>
                                        <span class="badge bg-light text-dark fw-bold" id="completedOrders"><?= $orderStats['delivered_orders'] ?? 0 ?></span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: var(--bs-warning);">
                                                <i class="bi bi-clock-fill text-white" style="font-size: 12px;"></i>
                                            </div>
                                            <small>Pending Orders</small>
                                        </div>
                                        <span class="badge bg-light text-dark fw-bold" id="pendingOrdersQuick"><?= $orderStats['pending_orders'] ?? 0 ?></span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: var(--bs-info);">
                                                <i class="bi bi-truck text-white" style="font-size: 12px;"></i>
                                            </div>
                                            <small>Shipped Orders</small>
                                        </div>
                                        <span class="badge bg-light text-dark fw-bold" id="shippedOrders"><?= $orderStats['shipped_orders'] ?? 0 ?></span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: var(--bs-info);">
                                                <i class="bi bi-gear-fill text-white" style="font-size: 12px;"></i>
                                            </div>
                                            <small>Processing Orders</small>
                                        </div>
                                        <span class="badge bg-light text-dark fw-bold" id="processingOrders"><?= $orderStats['processing_orders'] ?? 0 ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Activity Feed & Top Performers -->
                <div class="row g-4 mb-4">
                    <!-- Recent Activity -->
                    <div class="col-lg-6">
                        <div class="dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-activity me-2"></i>Recent Activity
                                </h6>
                                <button class="btn btn-sm btn-outline-secondary" onclick="refreshActivity()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="activity-feed" id="activityFeed">
                                    <!-- Recent orders -->
                                    <?php if (!empty($recentOrders)): ?>
                                        <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                                            <div class="activity-item d-flex align-items-center p-3 border-bottom">
                                                <div class="activity-icon bg-primary text-white rounded-circle me-3">
                                                    <i class="bi bi-bag-plus"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold">New Order #<?= $order['order_number'] ?></div>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($order['username']) ?> • 
                                                        ₱<?= number_format($order['total_amount'], 2) ?>
                                                    </small>
                                                </div>
                                                <small class="text-muted"><?= date('H:i', strtotime($order['created_at'])) ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="bi bi-clock-history fs-4 d-block mb-2"></i>
                                            No recent activity
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="col-lg-6">
                        <div class="dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-trophy me-2"></i>Top Products Today
                                </h6>
                                <small class="text-muted">By sales</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="top-products-list" id="topProductsList">
                                    <?php if (!empty($topProducts)): ?>
                                        <?php foreach (array_slice($topProducts, 0, 5) as $index => $product): ?>
                                            <div class="product-item d-flex align-items-center p-3 border-bottom">
                                                <div class="rank-badge me-3">
                                                    <span class="badge <?= $index < 3 ? 'bg-warning' : 'bg-secondary' ?> rounded-pill">
                                                        #<?= $index + 1 ?>
                                                    </span>
                                                </div>
                                                <div class="product-image me-3">
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img src="../../../public/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                                            alt="<?= htmlspecialchars($product['name']) ?>"
                                                            class="rounded" 
                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                            style="width: 40px; height: 40px;">
                                                            <i class="bi bi-box text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold"><?= htmlspecialchars($product['name']) ?></div>
                                                    <small class="text-muted">
                                                        Sales: <?= $product['total_sold'] ?? 0 ?> • 
                                                        ₱<?= number_format($product['total_revenue'] ?? 0, 0) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="bi bi-graph-up fs-4 d-block mb-2"></i>
                                            No sales data yet
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Performance Overview -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Category Performance
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3" id="categoryPerformanceGrid">
                                    <?php if (!empty($categoryPerformance)): ?>
                                        <?php foreach ($categoryPerformance as $category): ?>
                                            <div class="col-lg-3 col-md-6">
                                                <div class="category-performance-card">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($category['name']) ?></h6>
                                                        <span class="badge <?= $category['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                            <?= ucfirst($category['status']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="category-metrics">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <small class="text-muted">Products:</small>
                                                            <small class="fw-semibold"><?= $category['total_products'] ?></small>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <small class="text-muted">Active:</small>
                                                            <small class="fw-semibold text-success"><?= $category['active_products'] ?></small>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <small class="text-muted">Stock:</small>
                                                            <small class="fw-semibold"><?= number_format($category['total_stock']) ?></small>
                                                        </div>
                                                    </div>
                                                    <div class="category-progress mt-2">
                                                        <?php 
                                                        $activePercentage = $category['total_products'] > 0 ? 
                                                            ($category['active_products'] / $category['total_products']) * 100 : 0;
                                                        ?>
                                                        <div class="progress" style="height: 4px;">
                                                            <div class="progress-bar bg-success" style="width: <?= $activePercentage ?>%"></div>
                                                        </div>
                                                        <small class="text-muted"><?= number_format($activePercentage, 1) ?>% active</small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12">
                                            <div class="text-center py-4 text-muted">
                                                <i class="bi bi-tags fs-4 d-block mb-2"></i>
                                                No categories found
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Products Section -->
            <div id="products-section" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Products Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle me-2"></i>Add Product
                    </button>
            </div>
                <div class="table-container card-hover">
                    <div class="table-header">
                        <div class="row align-items-center w-100">
                            <div class="col-md-6">
                                <input type="search" class="form-control" placeholder="Search products..." id="productSearch">
                            </div>
                            <div class="col-md-6 text-end">
                                <select class="form-select d-inline-block w-auto me-2" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>    
                                    <th>Product</th>
                                    <th>Id</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
            <tr class="product-row" 
                data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>" 
                data-category="<?= $p['category_id'] ?>" 
                data-status="<?= strtolower($p['status']) ?>">
                <td>
                    <div class="d-flex align-items-center">
                        <div class="product-img">
                            <?php if (!empty($p['image'])): ?>
                                <img src="../../../public/uploads/<?= htmlspecialchars($p['image']) ?>"  alt="<?= htmlspecialchars($p['name']) ?>"  style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <i class="bi bi-box-seam text-primary"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                        </div>
                    </div>
                </td>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td class="fw-bold">₱<?= number_format($p['price'], 2) ?></td>
                <td>
                    <span class="badge <?= ($p['stock'] > 10) ? 'bg-success' : (($p['stock'] > 0) ? 'bg-warning' : 'bg-danger') ?>">
                        <?= $p['stock'] ?? 'N/A' ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?= 
                        ($p['status_display'] === 'active') ? 'bg-success' : 
                        (($p['status_display'] === 'pending') ? 'bg-warning' : 'bg-danger') 
                    ?>">
                        <?= ucfirst($p['status_display']) ?>
                    </span>
                </td>
                <td>
    <div class="btn-group btn-group-sm">
        <!-- Actions Dropdown -->
        <button type="button" 
                class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                data-bs-toggle="dropdown" 
                aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i> Actions
        </button>

        <ul class="dropdown-menu dropdown-menu-end">
            <!-- View Details -->
            <li>
                <button type="button" 
                        class="dropdown-item btn-view"
                        data-bs-toggle="modal" 
                        data-bs-target="#viewProductModal"
                        data-id="<?= $p['id']; ?>"
                        data-name="<?= htmlspecialchars($p['name']); ?>"
                        data-description="<?= htmlspecialchars($p['description']); ?>"
                        data-price="<?= $p['price']; ?>"
                        data-stock="<?= $p['stock']; ?>"
                        data-category="<?= $p['category_id']; ?>"
                        data-status="<?= $p['status']; ?>"
                        data-image="<?= $p['image']; ?>"
                        data-sizes="<?= htmlspecialchars($p['sizes'] ?? ''); ?>">
                    <i class="bi bi-eye me-2 text-secondary"></i> View Details
                </button>
            </li>


            <!-- Edit Product -->
            <li>
                <button type="button" 
                        class="dropdown-item btn-edit"
                        data-bs-toggle="modal" 
                        data-bs-target="#editProductModal"
                        data-id="<?= $p['id']; ?>"
                        data-name="<?= htmlspecialchars($p['name']); ?>"
                        data-description="<?= htmlspecialchars($p['description']); ?>"
                        data-price="<?= $p['price']; ?>"
                        data-stock="<?= $p['stock']; ?>"
                        data-category="<?= $p['category_id']; ?>"
                        data-status="<?= $p['status']; ?>"
                        data-image="<?= $p['image']; ?>"
                        data-sizes="<?= htmlspecialchars($p['sizes']); ?>">
                    <i class="bi bi-pencil me-2 text-primary"></i> Edit Product
                </button>
            </li>

            <!-- Delete Product -->
            <li>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    <input type="hidden" name="delete_id" value="<?= $p['id']; ?>">
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-trash me-2"></i> Delete Product
                    </button>
                </form>
            </li>
        </ul>
    </div>
</td>

            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">No products found</td>
        </tr>
    <?php endif; ?>
</tbody>

                                        
                                        </table>
                                    </div>
                                </div>
            </div>

            

            <!-- Categories Section -->
            <div id="category-section" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Categories Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle me-2"></i>Add Category
                    </button>
                </div>

                <!-- Display Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error_message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="row g-3">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <?php
                            $productCount = $categoryController->countProducts($category['id']);
                            $statusClass = $category['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                            $iconClass = match($category['name']) {
                                'Tops' => 'bi-tshirt text-primary',
                                'Bottoms' => 'bi-bag text-success',
                                'Outwear' => 'bi-jacket text-warning',
                                'Footwear' => 'bi-shoe text-info',
                                'Accessories' => 'bi-watch text-danger',
                                default => 'bi-tag text-secondary'
                            };
                            ?>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="stat-card card-hover">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="fw-bold"><?= htmlspecialchars($category['name']) ?></h5>
                                            <p class="text-muted mb-2"><?= $productCount ?> active product<?= $productCount != 1 ? 's' : '' ?></p>
                                            <?php if (!empty($category['description'])): ?>
                                                <p class="text-muted small"><?= htmlspecialchars($category['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <i class="bi <?= $iconClass ?>" style="font-size: 2rem;"></i>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst($category['status']) ?>
                                        </span>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i> Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button class="dropdown-item btn-edit-category" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editCategoryModal"
                                                            data-id="<?= $category['id'] ?>"
                                                            data-name="<?= htmlspecialchars($category['name']) ?>"
                                                            data-description="<?= htmlspecialchars($category['description']) ?>"
                                                            data-status="<?= $category['status'] ?>">
                                                        <i class="bi bi-pencil-square me-2"></i>Edit
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="delete_category_id" value="<?= $category['id'] ?>">
                                                        <button type="submit" name="delete_category" class="dropdown-item text-danger" 
                                                                onclick="return confirm('Are you sure you want to delete this category?')">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="bi bi-tags text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No categories found. Add your first category!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


            <!-- Orders Section -->
            <div id="orders-section" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Orders Management</h4>
                </div>

                <!-- Order Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-value" style="color: var(--warning);">
                                <?= $orderStats['pending_orders'] ?? 0 ?>
                            </div>
                            <div class="stat-label">Pending Orders</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-value" style="color: var(--info);">
                                <?= $orderStats['processing_orders'] ?? 0 ?>
                            </div>
                            <div class="stat-label">Processing</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-value" style="color: var(--amazon-primary);">
                                <?= $orderStats['shipped_orders'] ?? 0 ?>
                            </div>
                            <div class="stat-label">Shipped</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card">
                            <div class="stat-value" style="color: var(--success);">
                                <?= $orderStats['delivered_orders'] ?? 0 ?>
                            </div>
                            <div class="stat-label">Delivered</div>
                        </div>
                    </div>
                </div>

                <div class="table-container card-hover">
    <div class="table-header">
        <div class="row align-items-center w-100">
            <div class="col-md-6">
                <input type="search" class="form-control" placeholder="Search orders..." id="orderSearch">
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex gap-2 justify-content-end align-items-center">
                    <select class="form-select" style="width: auto;" id="orderStatusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <input type="date" class="form-control" style="width: auto;" id="orderDateFilter">

                    <button class="btn btn-outline-secondary" id="clearOrderFilter">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <?php if (!empty($orders) && is_array($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $statusClass = match($order['status']) {
                            'pending' => 'bg-warning',
                            'processing' => 'bg-info',
                            'shipped' => 'bg-primary',
                            'delivered' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        
                        $initials = strtoupper(substr($order['username'], 0, 2));
                        $orderDate = date('M j, Y', strtotime($order['created_at']));
                        
                        // Enhanced profile picture handling
                        $profileImagePath = null;
                        $showInitials = true;
                        
                        if (!empty($order['profile_pic'])) {
                            // Clean the profile_pic path - remove any duplicate path segments
                            $profilePicFilename = basename($order['profile_pic']);
                            $profileImagePath = '/E-COMMERCE/public/uploads/' . $profilePicFilename;
                            
                            // Check if file exists on server
                            $serverPath = $_SERVER['DOCUMENT_ROOT'] . $profileImagePath;
                            if (file_exists($serverPath)) {
                                $showInitials = false;
                            }
                        }
                        ?>
                        
                        <tr class="order-row" 
                            data-id="<?= $order['id'] ?>"
                            data-customer="<?= htmlspecialchars(strtolower($order['username'])) ?>"
                            data-status="<?= $order['status'] ?>"
                            data-date="<?= $order['created_at'] ?>">
                            <td class="fw-bold text-primary">#<?= $order['order_number'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="customer-avatar me-2 position-relative" style="width: 40px; height: 40px;">
                                        <?php if (!empty($order['profile_pic_url'])): ?>
                                        <img src="<?= htmlspecialchars($order['profile_pic_url']) ?>" 
                                            alt="<?= htmlspecialchars($order['username']) ?>" 
                                            class="rounded-circle" 
                                            width="40" height="40"
                                            style="object-fit: cover; border: 2px solid #e9ecef;">
                                    <?php else: ?>
                                        <div class="d-flex justify-content-center align-items-center rounded-circle text-white fw-bold"
                                            style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 14px;">
                                            <?= $initials ?>
                                        </div>
                                 <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($order['username']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($order['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $order['item_count'] ?> items</span>
                            </td>
                            <td class="fw-bold">₱<?= number_format($order['total_amount'], 2) ?></td>
                          
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst($order['status']) ?></span>
                            </td>
                            <td><?= $orderDate ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                            type="button" 
                                            id="orderActionsDropdown<?= $order['id'] ?>" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i> Actions
                                    </button>

                                    <ul class="dropdown-menu" aria-labelledby="orderActionsDropdown<?= $order['id'] ?>">
                                        <!-- View Order -->
                                        <li>
                                            <button type="button" class="dropdown-item view-order"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewOrderModal"
                                                    data-orderid="<?= $order['id'] ?>"
                                                    data-customer-phone="<?= htmlspecialchars($order['phone'] ?? 'Not provided') ?>"
                                                    data-customer-address="<?= htmlspecialchars($order['address'] ?? 'Not provided') ?>">
                                                <i class="bi bi-eye text-primary me-2"></i>View Details
                                            </button>
                                        </li>

                                        <li><hr class="dropdown-divider"></li>

                                        <!-- Status Updates Submenu -->
                                        <li class="dropend submenu">
                                            <a class="dropdown-item dropdown-toggle" href="#">
                                                <i class="bi bi-arrow-repeat text-info me-2"></i>Status Updates
                                            </a>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'pending')"><i class="bi bi-hourglass-split text-warning me-2"></i>Mark as Pending</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'processing')"><i class="bi bi-gear text-info me-2"></i>Mark as Processing</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'shipped')"><i class="bi bi-truck text-primary me-2"></i>Mark as Shipped</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'delivered')"><i class="bi bi-check2-circle text-success me-2"></i>Mark as Delivered</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(<?= $order['id'] ?>, 'cancelled')"><i class="bi bi-x-circle text-danger me-2"></i>Mark as Cancelled</a></li>
                                            </ul>
                                        </li>

                                        <li><hr class="dropdown-divider"></li>

                                        <!-- Delete Order -->
                                        <li>
                                            <button type="button" class="dropdown-item text-danger delete-order" 
                                                    data-order-id="<?= $order['id'] ?>"
                                                    onclick="return confirm('Are you sure you want to delete this order?')">
                                                <i class="bi bi-trash me-2"></i>Delete Order
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <style>
                        /* Submenu aligned to the left */
                        .dropdown-menu .submenu {
                            position: relative;
                        }

                        .dropdown-menu .submenu .dropdown-menu {
                            top: 0;
                            right: 100%;              /* Push it to the left */
                            left: auto;               /* Reset right override */
                            margin-right: -1px;       /* Close the gap */
                            border-top-right-radius: 0;   /* Blend with parent */
                            border-bottom-right-radius: 0;
                            display: none;
                            position: absolute;
                            min-width: 180px;
                        }

                        .dropdown-menu .submenu:hover > .dropdown-menu {
                            display: block;
                        }
                    </style>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-receipt text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No orders found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
            </div>


            <!-- Customers Section -->
            <div id="customers-section" class="content-section d-none">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Customers Management</h4>
                </div>

                <!-- Customer Stats -->
                <div class="row g-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card card-hover">
                            <div class="stat-value" style="color: var(--amazon-primary);">
                                <?= is_array($users) ? count($users) : '0' ?>
                            </div>
                            <div class="stat-label">Total Customers</div>
                            <div class="stat-change text-success" style="background: rgba(0, 166, 80, 0.1);">
                                <i class="bi bi-arrow-up"></i>
                                <?php
                                $currentMonthCount = is_array($users) ? count(array_filter($users, function($user) {
                                    $created = $user['created_at'] ?? date('Y-m-d H:i:s');
                                    return date('Y-m', strtotime($created)) === date('Y-m');
                                })) : 0;
                                
                                $lastMonthCount = is_array($users) ? count(array_filter($users, function($user) {
                                    $created = $user['created_at'] ?? date('Y-m-d H:i:s');
                                    return date('Y-m', strtotime($created)) === date('Y-m', strtotime('last month'));
                                })) : 0;
                                
                                $percentageChange = $lastMonthCount > 0 ? 
                                    (($currentMonthCount - $lastMonthCount) / $lastMonthCount) * 100 : 
                                    ($currentMonthCount > 0 ? 100 : 0);
                                
                                echo '+' . number_format($percentageChange, 1) . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
    <div class="stat-card card-hover">
        <div class="stat-value" style="color: var(--success);">
            ₱<?php
            if (is_array($users) && count($users) > 0) {
                $totalRevenue = array_sum(array_column($users, 'total_spent'));
                echo number_format($totalRevenue, 2);
            } else {
                echo '0.00';
            }
            ?>
        </div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-change text-success" style="background: rgba(0, 166, 80, 0.1);">
            <i class="bi bi-arrow-up"></i>
            <?php
            // Calculate revenue growth from last month
            $currentMonthRevenue = 0;
            $lastMonthRevenue = 0;
            
            if (is_array($users)) {
                // You would need order data with dates to calculate this accurately
                // For now, using a placeholder percentage
                echo '+12.5%';
            }
            ?>
        </div>
    </div>
</div>
                    <div class="col-lg-3 col-md-6">
    <div class="stat-card card-hover">
        <div class="stat-value" style="color: var(--amazon-orange);">
            ₱<?php
            if (is_array($users) && count($users) > 0) {
                $totalSpent = array_sum(array_column($users, 'total_spent'));
                $totalOrders = array_sum(array_column($users, 'order_count'));
                
                // Prevent division by zero
                if ($totalOrders > 0) {
                    echo number_format($totalSpent / $totalOrders, 2);
                } else {
                    echo '0.00';
                }
            } else {
                echo '0.00';
            }
            ?>
        </div>
        <div class="stat-label">Average Order Value</div>
        <div class="stat-change text-success" style="background: rgba(0, 166, 80, 0.1);">
            <i class="bi bi-arrow-up"></i>
            <?php echo '+5.2%'; ?>
        </div>
    </div>
</div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card card-hover">
                            <div class="stat-value" style="color: var(--info);">
                                <?= is_array($users) ? count(array_filter($users, function($user) {
                                    $created = $user['created_at'] ?? date('Y-m-d H:i:s');
                                    return date('Y-m', strtotime($created)) === date('Y-m');
                                })) : '0' ?>
                            </div>
                            <div class="stat-label">New This Month</div>
                            <div class="stat-change text-success" style="background: rgba(0, 166, 80, 0.1);">
                                <i class="bi bi-arrow-up"></i>
                                <?php
                                $newThisMonth = is_array($users) ? count(array_filter($users, function($user) {
                                    $created = $user['created_at'] ?? date('Y-m-d H:i:s');
                                    return date('Y-m', strtotime($created)) === date('Y-m');
                                })) : 0;
                                
                                $newLastMonth = is_array($users) ? count(array_filter($users, function($user) {
                                    $created = $user['created_at'] ?? date('Y-m-d H:i:s');
                                    return date('Y-m', strtotime($created)) === date('Y-m', strtotime('last month'));
                                })) : 0;
                                
                                $growthPercentage = $newLastMonth > 0 ? 
                                    (($newThisMonth - $newLastMonth) / $newLastMonth) * 100 : 
                                    ($newThisMonth > 0 ? 100 : 0);
                                
                                echo '+' . number_format($growthPercentage, 1) . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Table -->
                <div class="table-container card-hover">
                    <div class="table-header">
                        <div class="row align-items-center w-100">
                            <div class="col-md-6">
                                <input type="search" class="form-control" placeholder="Search customers..." id="customerSearch">
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="d-flex gap-2 justify-content-end align-items-center">
                                    <select class="form-select" style="width: auto;" id="customerStatusFilter">
                                        <option value="">All Status</option>
                                        <option value="Active">Active</option>
                                        <option value="Deactivated">Deactivated</option>
                                        <option value="Blocked">Blocked</option>
                                    </select>
                                    <input type="date" class="form-control" style="width: auto;" id="customerDateFilter" placeholder="Filter by date">
                                    <button class="btn btn-outline-secondary" id="clearCustomerFilter" title="Clear Filters">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="customersTableBody">
                                <?php if (!empty($users) && is_array($users)): ?>
                                    <?php
                        // Updated Customer Table Row with Deactivated Status
                        foreach ($users as $user): 
                            // Ensure status has a default value and is properly handled
                            $userStatus = $user['status'] ?? 'Active';
                            
                            // Debug: Log the actual status from database
                            error_log("🔍 User ID {$user['id']} status from DB: '{$userStatus}'");
                            
                            // Updated status class mapping to include Deactivated
                            $statusClass = match($userStatus) {
                                'Active' => 'bg-success',
                                'Deactivated' => 'bg-warning', // Changed from 'Inactive' to 'Deactivated'
                                'Blocked' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            
                            // Safe initials generation
                            $initials = 'NA';
                            if (!empty($user['username'])) {
                                $initials = strtoupper(substr($user['username'], 0, 2));
                            }
                            
                            // Safe date handling
                            $joinedDate = 'N/A';
                            if (!empty($user['created_at']) && $user['created_at'] != '0000-00-00 00:00:00') {
                                try {
                                    $joinedDate = date('M j, Y', strtotime($user['created_at']));
                                } catch (Exception $e) {
                                    $joinedDate = 'Invalid Date';
                                }
                            }
                        ?>

                        <tr class="customer-row" 
                            data-name="<?= htmlspecialchars(strtolower($user['username'])) ?>" 
                            data-email="<?= htmlspecialchars(strtolower($user['email'])) ?>"
                            data-status="<?= htmlspecialchars($userStatus) ?>"
                            data-date="<?= $user['created_at'] ?>">

                            <!-- Customer Info Column -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($user['profile_pic'])): ?>
                                        <img src="/E-COMMERCE/public/uploads/<?= htmlspecialchars($user['profile_pic']) ?>" 
                                            alt="<?= htmlspecialchars($user['username']) ?>" 
                                            class="avatar me-3 rounded-circle"
                                            style="width: 40px; height: 40px; object-fit: cover;"
                                            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['username']) ?>&background=random&size=40&rounded=true'">
                                    <?php else: ?>
                                        <div class="avatar me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; font-size: 14px;">
                                            <?= $initials ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($user['username']) ?></div>
                                    </div>
                                </div>
                            </td>

                            <td class="fw-bold text-primary">#U<?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['phone'] ? htmlspecialchars($user['phone']) : 'N/A' ?></td>
                            <td>
                                <span class="badge bg-info"><?= $user['order_count'] ?> orders</span>
                            </td>
                            <td class="fw-bold">₱<?= number_format($user['total_spent'], 2) ?></td>
                            
                            <!-- Status Column -->
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($userStatus) ?></span>
                            </td>
                            
                            <td><?= $joinedDate ?></td>
                            
                            <!-- Actions Column with Dropdown -->
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                            type="button" 
                                            id="actionsDropdown<?= $user['id'] ?>" 
                                            data-bs-toggle="dropdown" 
                                            aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i> Actions
                                    </button>
                                    
                                    <ul class="dropdown-menu" aria-labelledby="actionsDropdown<?= $user['id'] ?>">
                                        <!-- View Action -->
                                        <li>
                                            <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewCustomerModal"
                                                    data-id="U<?= $user['id'] ?>"
                                                    data-name="<?= htmlspecialchars($user['username']) ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($user['phone'] ?? 'N/A') ?>"
                                                    data-orders="<?= $user['order_count'] ?>"
                                                    data-spent="<?= $user['total_spent'] ?>"
                                                    data-status="<?= htmlspecialchars($userStatus) ?>"
                                                    data-joined="<?= $joinedDate ?>"
                                                    data-address="<?= htmlspecialchars($user['address'] ?? 'N/A') ?>"
                                                    data-profile-pic="<?= htmlspecialchars($user['profile_pic'] ?? '') ?>">
                                                <i class="bi bi-eye text-primary me-2"></i>View Details
                                            </button>
                                        </li>
                                        
                                        <!-- Edit Action -->
                                        <!-- <li>
                                            <button type="button" class="dropdown-item" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCustomerModal"
                                                    data-userid="<?= $user['id'] ?>"
                                                    data-username="<?= htmlspecialchars($user['username']) ?>"
                                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                                    data-address="<?= htmlspecialchars($user['address'] ?? '') ?>"
                                                    data-status="<?= htmlspecialchars($userStatus) ?>">
                                                <i class="bi bi-pencil text-secondary me-2"></i>Edit Customer
                                            </button>
                                        </li> -->
                                        
                                        <li><hr class="dropdown-divider"></li>
                                        
                                        <!-- Status Actions based on current status -->
                                        <?php if ($userStatus === 'Active'): ?>
                                            <!-- Deactivate Action -->
                                            <li>
                                                <form method="POST" style="display:inline; width: 100%;">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="status" value="Deactivated">
                                                    <button type="submit" name="update_user_status" 
                                                            class="dropdown-item text-warning"
                                                            onclick="return confirm('Are you sure you want to DEACTIVATE this customer? They will not be able to place orders but can still login.')">
                                                        <i class="bi bi-person-dash me-2"></i>Deactivate
                                                    </button>
                                                </form>
                                            </li>
                                            
                                            <!-- Block Action -->
                                            <li>
                                                <form method="POST" style="display:inline; width: 100%;">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="status" value="Blocked">
                                                    <button type="submit" name="update_user_status" 
                                                            class="dropdown-item text-danger"
                                                            onclick="return confirm('Are you sure you want to BLOCK this customer? They will not be able to access their account.')">
                                                        <i class="bi bi-person-x me-2"></i>Block Customer
                                                    </button>
                                                </form>
                                            </li>
                                            
                                        <?php elseif ($userStatus === 'Deactivated'): ?>
                                            <!-- Activate Action -->
                                            <li>
                                                <form method="POST" style="display:inline; width: 100%;">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="status" value="Active">
                                                    <button type="submit" name="update_user_status" 
                                                            class="dropdown-item text-success"
                                                            onclick="return confirm('Are you sure you want to ACTIVATE this customer?')">
                                                        <i class="bi bi-person-check me-2"></i>Activate Customer
                                                    </button>
                                                </form>
                                            </li>
                                            
                                            <!-- Block Action -->
                                            <li>
                                                <form method="POST" style="display:inline; width: 100%;">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="status" value="Blocked">
                                                    <button type="submit" name="update_user_status" 
                                                            class="dropdown-item text-danger"
                                                            onclick="return confirm('Are you sure you want to BLOCK this customer? They will not be able to access their account.')">
                                                        <i class="bi bi-person-x me-2"></i>Block Customer
                                                    </button>
                                                </form>
                                            </li>
                                            
                                        <?php elseif ($userStatus === 'Blocked'): ?>
                                            <!-- Unblock Action -->
                                            <li>
                                                <form method="POST" style="display:inline; width: 100%;">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="status" value="Active">
                                                    <button type="submit" name="update_user_status" 
                                                            class="dropdown-item text-success"
                                                            onclick="return confirm('Are you sure you want to UNBLOCK this customer?')">
                                                        <i class="bi bi-person-check me-2"></i>Unblock Customer
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <li><hr class="dropdown-divider"></li>
                                        
                                        <!-- Delete Action -->
                                        <li>
                                            <form method="POST" style="display:inline; width: 100%;">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="delete_user" 
                                                        class="dropdown-item text-danger"
                                                        onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.')">
                                                    <i class="bi bi-trash me-2"></i>Delete Customer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <?php endforeach; ?>

                        <style>
                        /* Custom styles for better dropdown appearance */
                        .dropdown-item {
                            padding: 0.5rem 1rem;
                            font-size: 0.875rem;
                        }

                        .dropdown-item:hover {
                            background-color: #f8f9fa;
                        }

                        .dropdown-item form {
                            margin: 0;
                        }

                        .dropdown-item i {
                            width: 16px;
                        }

                        .dropdown-divider {
                            margin: 0.25rem 0;
                        }

                        /* Ensure dropdown button has consistent width */
                        .dropdown-toggle {
                            min-width: 100px;
                        }

                        /* Custom colors for different actions */
                        .dropdown-item.text-primary:hover {
                            background-color: rgba(13, 110, 253, 0.1);
                        }

                        .dropdown-item.text-secondary:hover {
                            background-color: rgba(108, 117, 125, 0.1);
                        }

                        .dropdown-item.text-success:hover {
                            background-color: rgba(25, 135, 84, 0.1);
                        }

                        .dropdown-item.text-warning:hover {
                            background-color: rgba(255, 193, 7, 0.1);
                        }

                        .dropdown-item.text-danger:hover {
                            background-color: rgba(220, 53, 69, 0.1);
                        }

                        .dropdown-item.text-muted {
                            cursor: not-allowed;
                            opacity: 0.6;
                        }

                        /* Updated status badge styles */
                        .badge.bg-warning {
                            color: #000 !important; /* Dark text for better readability on yellow background */
                        }
                        </style>

                                        <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-people text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No customers found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>        
            
            
            <!-- Analytics Section -->
            <div id="analytics-section" class="content-section d-none">
                <!-- Analytics Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Analytics & Reports</h4>
                        <p class="text-muted mb-0">Comprehensive business insights and performance metrics</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary active" data-period="7d" onclick="changePeriod('7d', this)">7 Days</button>
                            <button class="btn btn-outline-primary" data-period="30d" onclick="changePeriod('30d', this)">30 Days</button>
                            <button class="btn btn-outline-primary" data-period="90d" onclick="changePeriod('90d', this)">90 Days</button>
                            <button class="btn btn-outline-primary" data-period="1y" onclick="changePeriod('1y', this)">1 Year</button>
                        </div>
                        <button class="btn btn-success btn-sm" onclick="exportAnalytics()">
                            <i class="bi bi-download me-1"></i>Export Report
                        </button>
                    </div>
                </div>

                <!-- Key Metrics Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="analytics-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-currency-dollar display-6 text-primary"></i>
                            </div>
                            <div class="metric-value text-primary" id="totalRevenue">₱<?= number_format($dashboardStats['total_revenue'] ?? 0, 2) ?></div>
                            <div class="metric-label">Total Revenue</div>
                            <div class="trend-indicator trend-up">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+12.5% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="revenueSparkline" width="200" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="analytics-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-bag-check display-6 text-success"></i>
                            </div>
                            <div class="metric-value text-success" id="totalOrders"><?= $dashboardStats['total_orders'] ?? 0 ?></div>
                            <div class="metric-label">Total Orders</div>
                            <div class="trend-indicator trend-up">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+8.2% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="ordersSparkline" width="200" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="analytics-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-calculator display-6 text-warning"></i>
                            </div>
                            <div class="metric-value text-warning" id="avgOrderValue">
    ₱<?php 
    // Use the correct calculation from users data
    if (is_array($users) && count($users) > 0) {
        $totalSpent = array_sum(array_column($users, 'total_spent'));
        $totalOrders = array_sum(array_column($users, 'order_count'));
        
        if ($totalOrders > 0) {
            echo number_format($totalSpent / $totalOrders, 2);
        } else {
            echo '0.00';
        }
    } else {
        echo '0.00';
    }
    ?>
</div>
                            <div class="metric-label">Avg Order Value</div>
                            <div class="trend-indicator trend-up">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+5.7% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="avgSparkline" width="200" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="analytics-metric-card position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                                <i class="bi bi-people display-6 text-info"></i>
                            </div>
                            <div class="metric-value text-info" id="totalCustomers"><?= $dashboardStats['total_users'] ?? 0 ?></div>
                            <div class="metric-label">Total Customers</div>
                            <div class="trend-indicator trend-up">
                                <i class="bi bi-arrow-up me-1"></i>
                                <span>+15.3% vs last period</span>
                            </div>
                            <div class="metric-chart mt-2">
                                <canvas id="customersSparkline" width="200" height="40"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Charts Row -->
                <div class="row g-4 mb-4">
                    <!-- Revenue Trend Chart -->
                    <div class="col-lg-8">
                        <div class="analytics-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-graph-up me-2"></i>Revenue & Orders Trends
                                </h6>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active" onclick="toggleChart('revenue', this)">Revenue</button>
                                    <button class="btn btn-outline-primary" onclick="toggleChart('orders', this)">Orders</button>
                                    <button class="btn btn-outline-primary" onclick="toggleChart('both', this)">Both</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="mainTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                   <!-- Order Status Distribution -->
                    <div class="col-lg-4">
                        <div class="analytics-card">
                            <div class="card-header">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-pie-chart me-2"></i>Order Status Distribution
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-small">
                                    <canvas id="orderStatusChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <?php 
                                    // ✅ Define labels and colors
                                    $orderStatusLabels = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                                    $orderStatusColors = ['warning', 'info', 'primary', 'success', 'danger'];

                                    // ✅ Add cancelled orders to the array
                                    $orderCounts = [
                                        ($orderStats['pending_orders'] ?? 0),
                                        ($orderStats['processing_orders'] ?? 0), 
                                        ($orderStats['shipped_orders'] ?? 0),
                                        ($orderStats['delivered_orders'] ?? 0),
                                        ($orderStats['cancelled_orders'] ?? 0), // ✅ Now included
                                    ];
                                    
                                    for ($i = 0; $i < count($orderStatusLabels); $i++): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 rounded" style="width: 12px; height: 12px; background-color: var(--bs-<?= $orderStatusColors[$i] ?>);"></div>
                                            <small><?= $orderStatusLabels[$i] ?></small>
                                        </div>
                                        <span class="badge bg-light text-dark"><?= $orderCounts[$i] ?></span>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secondary Analytics Row -->
                <div class="row g-4 mb-4">
                    <!-- Category Performance -->
                    <div class="col-lg-6">
                        <div class="analytics-card">
                            <div class="card-header">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-tags me-2"></i>Category Performance
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-small">
                                    <canvas id="categoryChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Registration Trend -->
                    <div class="col-lg-6">
                        <div class="analytics-card">
                            <div class="card-header">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-person-plus me-2"></i>Customer Registration Trend
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-small">
                                    <canvas id="registrationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Performance Tables -->
                <div class="row g-4 mb-4">
                    <!-- Top Products -->
                    <div class="col-lg-6">
                        <div class="analytics-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-trophy me-2"></i>Top Performing Products
                                </h6>
                                <small class="text-muted">Last 30 days</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0" style="width: 60px;">Rank</th>
                                                <th class="border-0">Product</th>
                                                <th class="border-0 text-center" style="width: 70px;">Sales</th>
                                                <th class="border-0 text-end" style="width: 90px;">Revenue</th>
                                                <th class="border-0 text-center" style="width: 80px;">Growth</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($topProducts)): ?>
                                                <?php foreach ($topProducts as $product): ?>
                                                <tr>
                                                <td class="border-0">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <?php if ($product['rank'] <= 3): ?>
                                                            <?php 
                                                            $badgeClass = $product['rank'] === 1 ? 'bg-warning' : 
                                                                        ($product['rank'] === 2 ? 'bg-secondary' : 'bg-danger');
                                                            ?>
                                                            <span class="badge <?= $badgeClass ?>  d-flex align-items-center justify-content-center" 
                                                                style="width: 28px; height: 28px; font-size: 11px;">
                                                                <?= $product['rank'] ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="fw-bold text-muted fs-6">#<?= $product['rank'] ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                                    <td class="border-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-2">
                                                                <?php if (!empty($product['image'])): ?>
                                                                    <img src="../../../public/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                                                        class="rounded border" 
                                                                        style="width: 36px; height: 36px; object-fit: cover;"
                                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                    <div class="bg-light rounded border d-none align-items-center justify-content-center" 
                                                                        style="width: 36px; height: 36px;">
                                                                        <i class="bi bi-box text-muted fs-6"></i>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="bg-light rounded border d-flex align-items-center justify-content-center" 
                                                                        style="width: 36px; height: 36px;">
                                                                        <i class="bi bi-box text-muted fs-6"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-semibold text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($product['name']) ?>">
                                                                    <?= htmlspecialchars($product['name']) ?>
                                                                </div>
                                                                <small class="text-muted">
                                                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                                                    <?php if ($product['stock'] > 0): ?>
                                                                        • Stock: <?= $product['stock'] ?>
                                                                    <?php else: ?>
                                                                        • <span class="text-danger">Out of stock</span>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="border-0 text-center">
                                                        <?php if (($product['total_sold'] ?? 0) > 0): ?>
                                                            <span class="badge bg-primary rounded-pill fw-bold"><?= $product['total_sold'] ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">No sales</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="border-0 text-end">
                                                        <div class="fw-bold <?= ($product['total_revenue'] ?? 0) > 0 ? 'text-success' : 'text-muted' ?>">
                                                            ₱<?= number_format($product['total_revenue'] ?? 0, 0) ?>
                                                        </div>
                                                    </td>
                                                    <td class="border-0 text-center">
                                                        <?php if (($product['total_sold'] ?? 0) > 0): ?>
                                                            <span class="badge <?= ($product['growth'] ?? 0) >= 0 ? 'bg-success' : 'bg-danger' ?> rounded-pill small">
                                                                <?= ($product['growth'] ?? 0) >= 0 ? '+' : '' ?><?= $product['growth'] ?? 0 ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-muted rounded-pill small">New</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="border-0 text-center py-5">
                                                        <div class="text-center">
                                                            <i class="bi bi-graph-up text-muted mb-3" style="font-size: 3rem; display: block;"></i>
                                                            <div class="text-muted mb-2">No product performance data yet</div>
                                                            <small class="text-muted">
                                                                Products will appear here once orders are processed.<br>
                                                                <a href="#" onclick="showSection('products-section')" class="text-decoration-none">
                                                                    Add products
                                                                </a> or 
                                                                <a href="#" onclick="showSection('orders-section')" class="text-decoration-none">
                                                                    process orders
                                                                </a> to see analytics.
                                                            </small>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <?php if (!empty($topProducts) && count($topProducts) > 0): ?>
                                    <div class="card-footer bg-light border-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Showing top <?= count($topProducts) ?> products
                                            </small>
                                           
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    



                    <!-- Category Analytics -->
                    <div class="col-lg-6">
                        <div class="analytics-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-grid-3x3-gap me-2"></i>Category Analytics
                                </h6>
                                <small class="text-muted">Current inventory</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="border-0">Category</th>
                                                <th class="border-0 text-center">Products</th>
                                                <th class="border-0 text-center">Stock</th>
                                                <th class="border-0">Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($categoryPerformance) && is_array($categoryPerformance)): ?>
                                                <?php 
                                                $maxStock = max(array_column($categoryPerformance, 'total_stock'));
                                                foreach ($categoryPerformance as $category): 
                                                    $performance = $maxStock > 0 ? ($category['total_stock'] / $maxStock) * 100 : 0;
                                                ?>
                                                <tr>
                                                    <td class="border-0">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-tag me-2 text-primary"></i>
                                                            <span class="fw-semibold"><?= htmlspecialchars($category['name']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="border-0 text-center">
                                                        <span class="text-success fw-bold"><?= $category['active_products'] ?></span>
                                                        <span class="text-muted">/ <?= $category['total_products'] ?></span>
                                                    </td>
                                                    <td class="border-0 text-center">
                                                        <span class="fw-bold"><?= number_format($category['total_stock']) ?></span>
                                                    </td>
                                                    <td class="border-0">
                                                        <div class="progress" style="height: 6px;">
                                                            <div class="progress-bar bg-primary" style="width: <?= $performance ?>%"></div>
                                                        </div>
                                                        <small class="text-muted"><?= number_format($performance, 1) ?>%</small>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="border-0 text-center text-muted py-3">
                                                        <i class="bi bi-inbox display-6"></i>
                                                        <div>No category data available</div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!--Inventory Section -->
            <div id="inventory-section" class="content-section d-none">
                    <!-- Inventory Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold mb-1">Inventory Management</h4>
                            <p class="text-muted mb-0">Monitor stock levels, track inventory movements, and manage product availability</p>
                        </div>
                        <div class="d-flex gap-2">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#stockAdjustmentModal">
        <i class="bi bi-plus-circle me-2"></i>Stock Adjustment
    </button>
    <button class="btn btn-primary" onclick="exportInventoryReport()">
        <i class="bi bi-download me-2"></i>Export Report
    </button>
</div>
                    </div>

                    <!-- Inventory Overview Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card card-hover">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value text-primary" id="totalInventoryValue">₱0</div>
                                        <div class="stat-label">Total Inventory Value</div>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> +2.5% from last month
                                        </small>
                                    </div>
                                    <div>
                                        <i class="bi bi-currency-dollar text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card card-hover">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value text-warning" id="lowStockCount">0</div>
                                        <div class="stat-label">Low Stock Items</div>
                                        <small class="text-warning">
                                            <i class="bi bi-exclamation-triangle"></i> Needs attention
                                        </small>
                                    </div>
                                    <div>
                                        <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card card-hover">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value text-danger" id="outOfStockCount">0</div>
                                        <div class="stat-label">Out of Stock</div>
                                        <small class="text-danger">
                                            <i class="bi bi-x-circle"></i> Immediate action required
                                        </small>
                                    </div>
                                    <div>
                                        <i class="bi bi-x-circle text-danger fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <div class="stat-card card-hover">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="stat-value text-success" id="wellStockedCount">0</div>
                                        <div class="stat-label">Well Stocked</div>
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> Good levels
                                        </small>
                                    </div>
                                    <div>
                                        <i class="bi bi-check-circle text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts Section -->
                    <div id="inventoryAlerts" class="mb-4"></div>

                    <!-- Filters and Search -->
                    <div class="row mb-4">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Search Products</label>
                                                <input type="search" class="form-control" id="inventorySearch" 
                                                    placeholder="Search by name or ID...">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Category Filter</label>
                                                <select class="form-select" id="inventoryCategoryFilter">
                                                    <option value="">All Categories</option>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Stock Status</label>
                                                <select class="form-select" id="inventoryStatusFilter">
                                                    <option value="">All Status</option>
                                                    <option value="out-of-stock">Out of Stock</option>
                                                    <option value="low-stock">Low Stock</option>
                                                    <option value="well-stocked">Well Stocked</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">&nbsp;</label>
                                                <button class="btn btn-outline-secondary w-100" onclick="clearInventoryFilters()">
                                                    <i class="bi bi-x-circle"></i> Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bi bi-graph-up me-2"></i>Quick Stats
                                        </h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Products:</span>
                                            <span class="fw-bold" id="totalProductsCount">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Active Products:</span>
                                            <span class="fw-bold text-success" id="activeProductsCount">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Inactive Products:</span>
                                            <span class="fw-bold text-muted" id="inactiveProductsCount">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <!-- Inventory Table -->
                    <div class="table-container card-hover">
                    <div class="table-header">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-boxes me-2"></i>Inventory Overview
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" onclick="bulkStockUpdate()">
                                <i class="bi bi-arrow-up-circle me-2"></i>Bulk Stock Update
                            </button>
                        </div>
                    </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th width="5%">
                                                <input type="checkbox" id="selectAllInventory" onchange="toggleAllInventorySelection()">
                                            </th>
                                            <th width="15%">Product</th>
                                            <th width="8%">ID</th>
                                            <th width="12%">Category</th>
                                            <th width="10%">Stock</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Price</th>
                                            <th width="12%">Value</th>
                                            <th width="10%">Last Updated</th>
                                            <th width="8%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inventoryTableBody">
                                        <!-- Inventory rows will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="table-footer p-3 d-flex justify-content-between align-items-center border-top">
                                <div class="text-muted">
                                    <span id="inventoryResultsInfo">Showing 0 products</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="prevInventoryPage()" id="prevPageBtn" disabled>
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </button>
                                    <span class="d-flex align-items-center px-3">
                                        Page <span id="currentPageNum">1</span> of <span id="totalPages">1</span>
                                    </span>
                                    <button class="btn btn-sm btn-outline-primary" onclick="nextInventoryPage()" id="nextPageBtn">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                    </div>

                    <!-- Stock Movement History -->
                    <div class="row mt-4">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="bi bi-clock-history me-2"></i>Recent Stock Movements
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Type</th>
                                                        <th>Quantity</th>
                                                        <th>Date</th>
                                                        <th>User</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="stockMovementHistory">
                                                    <tr>
                                                        <td colspan="5" class="text-center py-3 text-muted">
                                                            <i class="bi bi-clock-history fs-4 d-block mb-2"></i>
                                                            No recent stock movements
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="bi bi-graph-down me-2"></i>Top Low Stock Items
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="lowStockList" class="list-group list-group-flush">
                                            <div class="list-group-item text-center py-4 text-muted">
                                                <i class="bi bi-check-circle fs-4 d-block mb-2"></i>
                                                All items well stocked
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
            </div>


            <!-- Stock Adjustment Modal -->
            <div class="modal fade" id="stockAdjustmentModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-plus-circle me-2"></i>Stock Adjustment
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" id="stockAdjustmentForm">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Product</label>
                                        <select class="form-select" name="product_id" id="adjustmentProductId" required>
                                            <option value="">Select a product...</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= $product['id'] ?>" data-current-stock="<?= $product['stock'] ?>">
                                                    <?= htmlspecialchars($product['name']) ?> (Current: <?= $product['stock'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Adjustment Type</label>
                                        <select class="form-select" name="adjustment_type" id="adjustmentType" required onchange="updateAdjustmentInterface()">
                                            <option value="">Select adjustment type...</option>
                                            <option value="add">Add Stock (Restock)</option>
                                            <option value="remove">Remove Stock (Sale/Loss)</option>
                                            <option value="set">Set Exact Stock</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="quantitySection" style="display: none;">
                                        <label class="form-label fw-bold" id="quantityLabel">Quantity</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="quantity" id="adjustmentQuantity" min="1" required>
                                            <span class="input-group-text" id="quantityUnit">units</span>
                                        </div>
                                        <div class="form-text" id="quantityHelpText"></div>
                                    </div>

                                    <div class="mb-3" id="previewSection" style="display: none;">
                                        <div class="alert alert-info">
                                            <strong>Preview:</strong>
                                            <div>Current Stock: <span id="previewCurrentStock">0</span></div>
                                            <div>New Stock: <span id="previewNewStock">0</span></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Reason</label>
                                        <select class="form-select" name="reason" required>
                                            <option value="">Select reason...</option>
                                            <option value="restock">New Stock Arrival</option>
                                            <option value="sale">Product Sale</option>
                                            <option value="damaged">Damaged/Defective</option>
                                            <option value="theft">Theft/Loss</option>
                                            <option value="expired">Expired Product</option>
                                            <option value="return">Customer Return</option>
                                            <option value="correction">Stock Count Correction</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes about this stock adjustment..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="hidden" name="action" value="stock_adjustment">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg me-2"></i>Apply Adjustment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
            </div>

            <!-- Bulk Stock Update Modal -->
            <div class="modal fade" id="bulkStockModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-arrow-up-circle me-2"></i>Bulk Stock Update
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> This will update stock levels for multiple selected products at once.
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Selected Products</label>
                                <div id="bulkSelectedProducts" class="border rounded p-3 bg-light">
                                    <span class="text-muted">No products selected</span>
                                </div>
                            </div>

                            <form id="bulkStockForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Operation</label>
                                        <select class="form-select" id="bulkOperation" required>
                                            <option value="">Select operation...</option>
                                            <option value="add">Add to current stock</option>
                                            <option value="subtract">Subtract from current stock</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Value</label>
                                        <input type="number" class="form-control" id="bulkValue" min="0" step="0.01" required>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="mt-3">
                                <label class="form-label fw-bold">Reason</label>
                                <select class="form-select" id="bulkReason" name="reason" required>
                                    <option value="">Select reason...</option>
                                    <option value="restock">New Stock Arrival</option>
                                    <option value="sale">Product Sale</option>
                                    <option value="damaged">Damaged/Defective</option>
                                    <option value="theft">Theft/Loss</option>
                                    <option value="expired">Expired Product</option>
                                    <option value="return">Customer Return</option>
                                    <option value="correction">Stock Count Correction</option>
                                </select>
                            </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" onclick="executeBulkStockUpdate()">
                                <i class="bi bi-check-lg me-2"></i>Update Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>

<script>
function loadMaintLogs() {
    // Correct path to the fetcher file we created in Step 1
    fetch('includes/get_maint_logs.php')
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('maintAuditLog');
        
        if (!data || data.length === 0) {
            container.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No recent operations found.</td></tr>';
            return;
        }

        container.innerHTML = data.map(log => `
            <tr>
                <td class="ps-4">
                    <span class="badge ${log.action_details.includes('ACTIVATED') ? 'bg-danger' : 'bg-success'} bg-opacity-10 ${log.action_details.includes('ACTIVATED') ? 'text-danger' : 'text-success'} border">
                        ${log.action_type}
                    </span>
                </td>
                <td><div class="fw-bold text-dark">${log.performed_by}</div></td>
                <td><small class="text-muted">${log.action_details}</small></td>
                <td><span class="text-nowrap">${new Date(log.created_at).toLocaleString()}</span></td>
                <td class="text-end pe-4">
                    <span class="text-success small fw-bold"><i class="fas fa-check-circle me-1"></i>COMPLETED</span>
                </td>
            </tr>
        `).join('');
    })
    .catch(err => {
        console.error('Log Load Error:', err);
        document.getElementById('maintAuditLog').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading logs.</td></tr>';
    });
}

// Call this when the page loads
document.addEventListener('DOMContentLoaded', loadMaintLogs);

function setQuickTime(hours) {
    const now = new Date();
    // Add the requested hours
    now.setHours(now.getHours() + hours);
    
    // Adjust for Local Timezone offset to prevent UTC shifting
    const offset = now.getTimezoneOffset() * 60000; // offset in milliseconds
    const localISOTime = new Date(now - offset).toISOString().slice(0, 16);
    
    // Update the input field
    const timeInput = document.getElementById('recovery_time_input');
    if (timeInput) {
        timeInput.value = localISOTime;
        
        // Visual feedback: Add a small highlight effect to show it changed
        timeInput.classList.add('is-valid');
        setTimeout(() => timeInput.classList.remove('is-valid'), 1000);
    }
}

// Load logs immediately when page opens
document.addEventListener('DOMContentLoaded', loadMaintLogs);
</script>


            <script>

                // Settings JavaScript
function showSettingsTab(tabName, button) {
    // Update active button
    document.querySelectorAll('.list-group-item').forEach(item => {
        item.classList.remove('active');
    });
    button.classList.add('active');
    
    // Show selected tab
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.add('d-none');
    });
    document.getElementById(tabName + '-tab').classList.remove('d-none');
    
    // Update page title
    const titles = {
        'general': 'General Settings',
        'store': 'Store Configuration', 
        'inventory': 'Inventory Settings',
        'notifications': 'Notification Settings',
        'security': 'Security Settings',
        'maintenance': 'Maintenance & Cleanup'
    };
    document.getElementById('page-title').textContent = titles[tabName] || 'Settings';
}

function saveAllSettings() {
    showLoading('Saving all settings...');
    
    // Collect all form data
    const formData = new FormData();
    formData.append('action', 'save_all_settings');
    
    // Get all form inputs
    document.querySelectorAll('#settings-section input, #settings-section select, #settings-section textarea').forEach(input => {
        if (input.type === 'checkbox') {
            formData.append(input.name || input.id, input.checked ? '1' : '0');
        } else if (input.value) {
            formData.append(input.name || input.id, input.value);
        }
    });
    
    // Simulate save (replace with actual POST request)
    setTimeout(() => {
        hideLoading();
        showToast('success', 'All settings saved successfully!');
    }, 1500);
}

function createBackup() {
    if (confirm('Create a database backup? This may take a few minutes.')) {
        showLoading('Creating backup...');
        
        // Simulate backup creation
        setTimeout(() => {
            hideLoading();
            showToast('success', 'Database backup created successfully!');
            
            // Simulate download
            const blob = new Blob(['-- Database Backup --'], { type: 'text/sql' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'ecommerce_backup_' + new Date().toISOString().split('T')[0] + '.sql';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        }, 3000);
    }
}

function exportData() {
    showLoading('Preparing data export...');
    
    setTimeout(() => {
        // Create CSV data from your PHP arrays
        const csvData = [
            ['Type', 'Name', 'Value', 'Date'],
            ['Product', 'Total Products', '<?= $dashboardStats['total_products'] ?? 0 ?>', new Date().toISOString().split('T')[0]],
            ['Order', 'Total Orders', '<?= $dashboardStats['total_orders'] ?? 0 ?>', new Date().toISOString().split('T')[0]],
            ['User', 'Total Users', '<?= $dashboardStats['total_users'] ?? 0 ?>', new Date().toISOString().split('T')[0]]
        ];
        
        const csvContent = csvData.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ecommerce_data_export_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        hideLoading();
        showToast('success', 'Data exported successfully!');
    }, 2000);
}

function clearAllSessions() {
    if (confirm('Force logout all users? This will end all active sessions.')) {
        showLoading('Clearing sessions...');
        
        setTimeout(() => {
            hideLoading();
            showToast('warning', 'All user sessions have been cleared');
        }, 1000);
    }
}

function confirmDangerousAction(action) {
    const actions = {
        'CLEAR_ALL_DATA': 'delete ALL data including products, orders, and users'
    };
    
    const actionText = actions[action];
    if (confirm(`Are you absolutely sure you want to ${actionText}?\n\nThis action CANNOT be undone!\n\nType 'DELETE' to confirm.`)) {
        const confirmation = prompt('Type DELETE to confirm:');
        if (confirmation === 'DELETE') {
            showToast('error', 'Feature disabled for safety. Contact developer to enable.');
        } else {
            showToast('info', 'Action cancelled - incorrect confirmation');
        }
    }
}

function resetAllSettings() {
    if (confirm('Reset all settings to default values?')) {
        showLoading('Resetting settings...');
        setTimeout(() => {
            hideLoading();
            showToast('success', 'Settings reset to defaults');
            location.reload();
        }, 1500);
    }
}

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Checkbox logic: if unchecked, explicitly send '0'
    if (!formData.has('maintenance_mode')) {
        formData.append('maintenance_mode', '0');
    }

    fetch('actions/update_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Professional notification
            alert('Maintenance Status Updated. Non-admins are now ' + (formData.get('maintenance_mode') == '1' ? 'Blocked' : 'Allowed'));
            location.reload(); 
        }
    });
});

// Auto-save functionality
document.addEventListener('DOMContentLoaded', function() {
    let saveTimeout;
    
    // Auto-save when settings change
    document.querySelectorAll('#settings-section input, #settings-section select').forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                // Auto-save individual setting
                console.log('Auto-saving setting:', this.name || this.id, '=', this.value);
            }, 2000);
        });
    });
});
            
            </script>

            <!--Inventory CHARTS Section -->
            <script>
                // Inventory Management JavaScript - FIXED VERSION
            let currentPage = 1;
            let itemsPerPage = 10;
            let filteredProducts = [];
            let allProducts = [];

            // Initialize inventory management when section is shown
            document.addEventListener('DOMContentLoaded', function() {
                initializeInventoryManagement();
                setupInventoryEventListeners();
            });

            function initializeInventoryManagement() {
                // Get products from PHP - FIXED: Use proper JSON encoding
                allProducts = <?php echo isset($products) ? json_encode($products) : '[]'; ?>;
                filteredProducts = [...allProducts];
                
                updateInventoryStats();
                renderInventoryTable();
                renderLowStockAlerts();
                setupInventorySearch();
            }

            function setupInventoryEventListeners() {
                // Search functionality - FIXED: Check if element exists
                const searchInput = document.getElementById('inventorySearch');
                if (searchInput) {
                    searchInput.addEventListener('input', debounce(filterInventory, 300));
                }

                // Filter functionality - FIXED: Check if elements exist
                const categoryFilter = document.getElementById('inventoryCategoryFilter');
                const statusFilter = document.getElementById('inventoryStatusFilter');
                
                if (categoryFilter) categoryFilter.addEventListener('change', filterInventory);
                if (statusFilter) statusFilter.addEventListener('change', filterInventory);

                // Stock adjustment form - FIXED: Check if element exists
                const stockForm = document.getElementById('stockAdjustmentForm');
                if (stockForm) {
                    stockForm.addEventListener('submit', handleStockAdjustment);
                }

                // Product selection for adjustment preview - FIXED: Check if elements exist
                const productSelect = document.getElementById('adjustmentProductId');
                const quantityInput = document.getElementById('adjustmentQuantity');
                
                if (productSelect) productSelect.addEventListener('change', updateAdjustmentPreview);
                if (quantityInput) quantityInput.addEventListener('input', updateAdjustmentPreview);
            }

            function updateInventoryStats() {
                let totalValue = 0;
                let lowStockCount = 0;
                let outOfStockCount = 0;
                let wellStockedCount = 0;
                
                const lowStockThreshold = 10; // You can make this configurable
                
                allProducts.forEach(product => {
                    const stock = parseInt(product.stock) || 0;
                    const price = parseFloat(product.price) || 0;
                    
                    totalValue += stock * price;
                    
                    if (stock === 0) {
                        outOfStockCount++;
                    } else if (stock <= lowStockThreshold) {
                        lowStockCount++;
                    } else {
                        wellStockedCount++;
                    }
                });

                // Update stat cards - FIXED: Check if elements exist before updating
                const totalInventoryValue = document.getElementById('totalInventoryValue');
                const lowStockCountElement = document.getElementById('lowStockCount');
                const outOfStockCountElement = document.getElementById('outOfStockCount');
                const wellStockedCountElement = document.getElementById('wellStockedCount');
                
                if (totalInventoryValue) animateValue('totalInventoryValue', totalValue, true, '₱');
                if (lowStockCountElement) animateValue('lowStockCount', lowStockCount);
                if (outOfStockCountElement) animateValue('outOfStockCount', outOfStockCount);
                if (wellStockedCountElement) animateValue('wellStockedCount', wellStockedCount);
                
                // Update quick stats - FIXED: Check if elements exist
                const totalProductsCount = document.getElementById('totalProductsCount');
                const activeProductsCount = document.getElementById('activeProductsCount');
                const inactiveProductsCount = document.getElementById('inactiveProductsCount');
                
                if (totalProductsCount) totalProductsCount.textContent = allProducts.length;
                if (activeProductsCount) {
                    activeProductsCount.textContent = allProducts.filter(p => p.status === 'active').length;
                }
                if (inactiveProductsCount) {
                    inactiveProductsCount.textContent = allProducts.filter(p => p.status !== 'active').length;
                }
            }

            function renderInventoryTable() {
                const tbody = document.getElementById('inventoryTableBody');
                if (!tbody) return;

                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const pageProducts = filteredProducts.slice(startIndex, endIndex);

                if (pageProducts.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <span class="text-muted">No products found</span>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = pageProducts.map(product => {
                    const stock = parseInt(product.stock) || 0;
                    const price = parseFloat(product.price) || 0;
                    const value = stock * price;
                    const stockStatus = getStockStatus(stock);
                    
                    return `
                        <tr class="inventory-row" data-product-id="${product.id}">
                            <td>
                                <input type="checkbox" class="inventory-checkbox" value="${product.id}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="/E-COMMERCE/public/uploads/${product.image || 'placeholder.jpg'}" 
                                        alt="${htmlEscape(product.name)}" 
                                        class="inventory-product-img me-2"
                                        onerror="this.src='/E-COMMERCE/public/uploads/placeholder.jpg'">
                                    <div>
                                        <div class="fw-semibold">${htmlEscape(product.name)}</div>
                                        <small class="text-muted">${htmlEscape(product.description || '').substring(0, 50)}${product.description && product.description.length > 50 ? '...' : ''}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-primary">#${product.id}</td>
                            <td>${htmlEscape(product.category_name || 'N/A')}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="stock-status-indicator ${stockStatus.class}"></span>
                                    <span class="fw-bold ${stockStatus.textClass}">${stock}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge ${stockStatus.badgeClass}">${stockStatus.label}</span>
                            </td>
                            <td class="fw-bold">₱${price.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                            <td class="fw-bold">₱${value.toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                            <td>
                                <small class="text-muted">${formatDate(product.updated_at || product.created_at)}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" onclick="quickStockAdjustment(${product.id})" title="Quick Adjust">
                                    <i class="bi bi-plus-circle"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');

                updatePagination();
                updateResultsInfo();
            }

            function renderLowStockAlerts() {
                const alertsContainer = document.getElementById('inventoryAlerts');
                const lowStockList = document.getElementById('lowStockList');
                
                const lowStockThreshold = 10;
                const outOfStockProducts = allProducts.filter(p => parseInt(p.stock) === 0);
                const lowStockProducts = allProducts.filter(p => {
                    const stock = parseInt(p.stock);
                    return stock > 0 && stock <= lowStockThreshold;
                });

                // Render alerts
                let alertsHtml = '';
                
                if (outOfStockProducts.length > 0) {
                    alertsHtml += `
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Critical:</strong> ${outOfStockProducts.length} product(s) are out of stock and need immediate attention.
                            <button class="btn btn-outline-danger btn-sm ms-2" onclick="viewOutOfStockProducts()">View Details</button>
                        </div>
                    `;
                }
                
                if (lowStockProducts.length > 0) {
                    alertsHtml += `
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i>
                            <strong>Warning:</strong> ${lowStockProducts.length} product(s) are running low on stock.
                            <button class="btn btn-outline-warning btn-sm ms-2" onclick="viewLowStockProducts()">View Details</button>
                        </div>
                    `;
                }
                
                if (alertsContainer) {
                    alertsContainer.innerHTML = alertsHtml;
                }

                // Render low stock list sidebar
                if (lowStockList) {
                    if (lowStockProducts.length === 0 && outOfStockProducts.length === 0) {
                        lowStockList.innerHTML = `
                            <div class="list-group-item text-center py-4 text-muted">
                                <i class="bi bi-check-circle fs-4 d-block mb-2"></i>
                                All items well stocked
                            </div>
                        `;
                    } else {
                        const combinedProducts = [...outOfStockProducts, ...lowStockProducts].slice(0, 5);
                        lowStockList.innerHTML = combinedProducts.map(product => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">${htmlEscape(product.name)}</div>
                                    <small class="text-muted">Stock: ${product.stock}</small>
                                </div>
                                <span class="badge ${getStockStatus(parseInt(product.stock)).badgeClass}">
                                    ${getStockStatus(parseInt(product.stock)).label}
                                </span>
                            </div>
                        `).join('');
                    }
                }
            }

            function getStockStatus(stock) {
                const lowStockThreshold = 10;
                
                if (stock === 0) {
                    return {
                        class: 'out-of-stock',
                        textClass: 'text-danger',
                        badgeClass: 'bg-danger',
                        label: 'Out of Stock'
                    };
                } else if (stock <= lowStockThreshold) {
                    return {
                        class: 'low-stock',
                        textClass: 'text-warning',
                        badgeClass: 'bg-warning',
                        label: 'Low Stock'
                    };
                } else {
                    return {
                        class: 'well-stocked',
                        textClass: 'text-success',
                        badgeClass: 'bg-success',
                        label: 'Well Stocked'
                    };
                }
            }

            function filterInventory() {
    const searchTerm = document.getElementById('inventorySearch')?.value.toLowerCase() || '';
    const categoryFilter = document.getElementById('inventoryCategoryFilter')?.value || '';
    const statusFilter = document.getElementById('inventoryStatusFilter')?.value || '';
    
    const lowStockThreshold = 10;
    
    filteredProducts = allProducts.filter(product => {
        // Search filter
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) ||
                            product.id.toString().includes(searchTerm) ||
                            (product.description && product.description.toLowerCase().includes(searchTerm));
        
        // Category filter - FIXED: Convert both to string for comparison
        const matchesCategory = !categoryFilter || product.category_id.toString() === categoryFilter.toString();
        
        // Status filter
        let matchesStatus = true;
        if (statusFilter) {
            const stock = parseInt(product.stock) || 0;
            switch (statusFilter) {
                case 'out-of-stock':
                    matchesStatus = stock === 0;
                    break;
                case 'low-stock':
                    matchesStatus = stock > 0 && stock <= lowStockThreshold;
                    break;
                case 'well-stocked':
                    matchesStatus = stock > lowStockThreshold;
                    break;
            }
        }
        
        return matchesSearch && matchesCategory && matchesStatus;
    });
    
    currentPage = 1;
    renderInventoryTable();
}

            function clearInventoryFilters() {
                document.getElementById('inventorySearch').value = '';
                document.getElementById('inventoryCategoryFilter').value = '';
                document.getElementById('inventoryStatusFilter').value = '';
                
                filteredProducts = [...allProducts];
                currentPage = 1;
                renderInventoryTable();
            }

            function setupInventorySearch() {
                const searchInput = document.getElementById('inventorySearch');
                if (searchInput) {
                    searchInput.addEventListener('input', debounce(filterInventory, 300));
                }
            }

            function updatePagination() {
                const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
                
                updateElement('currentPageNum', currentPage);
                updateElement('totalPages', totalPages);
                
                const prevBtn = document.getElementById('prevPageBtn');
                const nextBtn = document.getElementById('nextPageBtn');
                
                if (prevBtn) prevBtn.disabled = currentPage <= 1;
                if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
            }

            function updateResultsInfo() {
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, filteredProducts.length);
                const total = filteredProducts.length;
                
                let infoText;
                if (total === 0) {
                    infoText = 'No products found';
                } else if (total === 1) {
                    infoText = 'Showing 1 product';
                } else if (total <= itemsPerPage) {
                    infoText = `Showing ${total} products`;
                } else {
                    infoText = `Showing ${startIndex + 1}-${endIndex} of ${total} products`;
                }
                
                updateElement('inventoryResultsInfo', infoText);
            }

            function nextInventoryPage() {
                const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderInventoryTable();
                }
            }

            function prevInventoryPage() {
                if (currentPage > 1) {
                    currentPage--;
                    renderInventoryTable();
                }
            }

            function refreshInventory() {
                showLoading('Refreshing inventory data...');
                
                // Simulate API call to refresh data
                setTimeout(() => {
                    updateInventoryStats();
                    renderInventoryTable();
                    renderLowStockAlerts();
                    hideLoading();
                    showToast('success', 'Inventory data refreshed successfully!');
                }, 1000);
            }

            function handleStockAdjustment(event) {
                event.preventDefault();

                const formData = new FormData(event.target);
                const productId = formData.get('product_id');
                const adjustmentType = formData.get('adjustment_type');
                const quantityRaw = formData.get('quantity');
                const reason = formData.get('reason');
                const notes = formData.get('notes');

                // Parse quantity safely
                const quantity = quantityRaw !== null && quantityRaw !== '' ? parseInt(quantityRaw) : null;

                // Validate inputs
                if (!productId || !adjustmentType || quantity === null || isNaN(quantity) || !reason) {
                    showToast('error', 'Please fill in all required fields');
                    return;
                }

                showLoading('Processing stock adjustment...');

                // Send to server
                fetch('staff-dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Find the product in JS array
                        const productIndex = allProducts.findIndex(p => p.id == productId);
                        if (productIndex !== -1) {
                            let newStock = parseInt(allProducts[productIndex].stock) || 0;

                            // Apply adjustment based on type
                            switch(adjustmentType) {
                                case 'add':
                                    newStock += quantity;
                                    break;
                                case 'remove':
                                    newStock = Math.max(0, newStock - quantity);
                                    break;
                                case 'set':
                                    newStock = quantity;
                                    break;
                            }

                            // Update local array using DB-confirmed stock if returned
                            allProducts[productIndex].stock = data.new_stock ?? newStock;
                            filteredProducts = [...allProducts];

                            // Refresh inventory table, stats, and alerts
                            updateInventoryStats();
                            renderInventoryTable();
                            renderLowStockAlerts();

                            // Add to stock movement history
                            addStockMovementRecord(
                                productId,
                                adjustmentType,
                                quantity,
                                reason,
                                notes || ''
                            );
                        }

                        // Close modal & reset form
                        const modal = bootstrap.Modal.getInstance(document.getElementById('stockAdjustmentModal'));
                        if (modal) modal.hide();
                        event.target.reset();

                        showToast('success', data.message || 'Stock adjustment applied successfully!');
                    } else {
                        showToast('error', data.message || 'Failed to adjust stock');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast('error', 'Network error occurred');
                })
                .finally(() => hideLoading());
            }



            function updateAdjustmentInterface() {
                const adjustmentType = document.getElementById('adjustmentType').value;
                const quantitySection = document.getElementById('quantitySection');
                const quantityLabel = document.getElementById('quantityLabel');
                const quantityHelpText = document.getElementById('quantityHelpText');
                
                if (adjustmentType && quantitySection && quantityLabel && quantityHelpText) {
                    quantitySection.style.display = 'block';
                    
                    switch (adjustmentType) {
                        case 'add':
                            quantityLabel.textContent = 'Quantity to Add';
                            quantityHelpText.textContent = 'Enter the amount to add to current stock';
                            break;
                        case 'remove':
                            quantityLabel.textContent = 'Quantity to Remove';
                            quantityHelpText.textContent = 'Enter the amount to remove from current stock';
                            break;
                        case 'set':
                            quantityLabel.textContent = 'New Stock Level';
                            quantityHelpText.textContent = 'Enter the exact stock level to set';
                            break;
                    }
                    
                    updateAdjustmentPreview();
                } else {
                    if (quantitySection) quantitySection.style.display = 'none';
                }
            }

            function updateAdjustmentPreview() {
                const productSelect = document.getElementById("adjustmentProductId");
                const adjustmentType = document.getElementById("adjustmentType").value;
                const quantityInput = document.getElementById("adjustmentQuantity");
                const previewSection = document.getElementById("previewSection");

                if (!productSelect || !adjustmentType || !quantityInput) return;

                // ✅ Use latest stock from allProducts
                const productId = productSelect.value;
                const product = allProducts.find(p => p.id == productId);
                const currentStock = product ? parseInt(product.stock) : 0;

                const quantity = quantityInput.value !== "" ? parseInt(quantityInput.value) : null;

                let newStock = currentStock;
                if (quantity !== null && !isNaN(quantity)) {
                    if (adjustmentType === "add") {
                        newStock = currentStock + quantity;
                    } else if (adjustmentType === "remove") {
                        newStock = Math.max(0, currentStock - quantity);
                    } else if (adjustmentType === "set") {
                        newStock = quantity;
                    }
                }

                const previewCurrentStock = document.getElementById("previewCurrentStock");
                const previewNewStock = document.getElementById("previewNewStock");

                if (previewCurrentStock) previewCurrentStock.textContent = currentStock;
                if (previewNewStock) previewNewStock.textContent = newStock;

                if (previewSection) {
                    if (adjustmentType && quantity !== null && quantity > 0) {
                        previewSection.style.display = "block";
                    } else {
                        previewSection.style.display = "none";
                    }
                }
            }




            function quickStockAdjustment(productId) {
                const product = allProducts.find(p => p.id == productId);
                if (!product) return;
                
                const adjustment = prompt(`Quick stock adjustment for "${product.name}"\nCurrent stock: ${product.stock}\n\nEnter new stock level:`);
                
                if (adjustment !== null && adjustment !== '') {
                    const newStock = parseInt(adjustment);
                    
                    if (isNaN(newStock) || newStock < 0) {
                        showToast('error', 'Please enter a valid stock number');
                        return;
                    }
                    
                    showLoading('Updating stock...');
                    
                    // Make AJAX call to update database
                    const formData = new FormData();
                    formData.append('action', 'update_stock');
                    formData.append('product_id', productId);
                    formData.append('new_stock', newStock);
                    formData.append('reason', 'quick_adjustment');
                    formData.append('notes', 'Quick adjustment via inventory table');
                    
                    fetch('staff-dashboard.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update local array only after successful database update
                            const productIndex = allProducts.findIndex(p => p.id == productId);
                            if (productIndex !== -1) {
                                allProducts[productIndex].stock = newStock;
                                filteredProducts = [...allProducts];
                                
                                updateInventoryStats();
                                renderInventoryTable();
                                renderLowStockAlerts();
                                
                                hideLoading();
                                showToast('success', `Stock updated! ${product.name} now has ${newStock} units`);
                                
                                addStockMovementRecord(productId, 'set', newStock, 'quick_adjustment', 'Quick adjustment via inventory table');
                            }
                        } else {
                            hideLoading();
                            showToast('error', data.message || 'Failed to update stock');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        showToast('error', 'Network error occurred');
                    });
                }
            }

            function toggleAllInventorySelection() {
                const selectAllCheckbox = document.getElementById('selectAllInventory');
                const checkboxes = document.querySelectorAll('.inventory-checkbox');
                
                if (selectAllCheckbox) {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    
                    updateBulkActionsVisibility();
                }
            }

            function toggleColumnVisibility() {
                // This would show a dropdown or modal to toggle table column visibility
                showToast('info', 'Column visibility feature coming soon!');
            }

            function bulkStockUpdate() {
                const selectedProducts = getSelectedProducts();
                
                if (selectedProducts.length === 0) {
                    showToast('warning', 'Please select at least one product');
                    return;
                }
                
                // Populate the bulk update modal
                const selectedProductsContainer = document.getElementById('bulkSelectedProducts');
                if (selectedProductsContainer) {
                    selectedProductsContainer.innerHTML = selectedProducts.map(product => 
                        `<span class="badge bg-primary me-1 mb-1">${product.name}</span>`
                    ).join('');
                }
                
                const bulkModal = new bootstrap.Modal(document.getElementById('bulkStockModal'));
                bulkModal.show();
            }

            function executeBulkStockUpdate() {
    const selectedProducts = getSelectedProducts();
    const operation = document.getElementById('bulkOperation').value;
    const value = parseFloat(document.getElementById('bulkValue').value);
    const reason = document.getElementById('bulkReason').value;
    
    console.log('=== BULK UPDATE DEBUG ===');
    console.log('Selected Products:', selectedProducts);
    console.log('Operation:', operation);
    console.log('Value:', value);
    console.log('Reason:', reason);
    
    if (!operation || isNaN(value) || !reason) {
        showToast('error', 'Please fill in all required fields');
        return;
    }
    
    if (selectedProducts.length === 0) {
        showToast('warning', 'No products selected');
        return;
    }
    
    showLoading('Updating stock for selected products...');
    
    // Prepare data for server
    const updates = selectedProducts.map(product => {
        const currentStock = parseInt(product.stock) || 0;
        let newStock = currentStock;
        
        switch (operation) {
            case 'add':
                newStock = currentStock + value;
                break;
            case 'subtract':
                newStock = Math.max(0, currentStock - value);
                break;
            case 'multiply':
                newStock = Math.floor(currentStock * value);
                break;
        }
        
        return {
            product_id: product.id,
            current_stock: currentStock,
            new_stock: newStock,
            operation: operation,
            value: value
        };
    });
    
    console.log('Updates to send:', updates);
    
    // Send to server
    const formData = new FormData();
    formData.append('action', 'bulk_stock_update');
    formData.append('updates', JSON.stringify(updates));
    formData.append('reason', reason);
    formData.append('operation', operation);
    
    fetch('staff-dashboard.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            if (data.success) {
                // Update local arrays with confirmed values from server
                updates.forEach(update => {
                    const productIndex = allProducts.findIndex(p => p.id == update.product_id);
                    if (productIndex !== -1) {
                        allProducts[productIndex].stock = update.new_stock;
                        
                        // Add to stock movement history
                        addStockMovementRecord(
                            update.product_id,
                            'bulk_' + operation,
                            value,
                            'bulk_update',
                            reason
                        );
                    }
                });
                
                filteredProducts = [...allProducts];
                updateInventoryStats();
                renderInventoryTable();
                renderLowStockAlerts();
                
                // Close modal and reset form
                const bulkModal = bootstrap.Modal.getInstance(document.getElementById('bulkStockModal'));
                if (bulkModal) bulkModal.hide();
                
                const bulkForm = document.getElementById('bulkStockForm');
                if (bulkForm) bulkForm.reset();
                
                // Clear selections
                const selectAllCheckbox = document.getElementById('selectAllInventory');
                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                
                document.querySelectorAll('.inventory-checkbox').forEach(cb => cb.checked = false);
                
                hideLoading();
                showToast('success', `Bulk stock update completed for ${selectedProducts.length} products`);
            } else {
                hideLoading();
                showToast('error', data.message || 'Failed to update stock');
            }
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response was:', text);
            hideLoading();
            showToast('error', 'Invalid server response');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        hideLoading();
        showToast('error', 'Network error occurred during bulk update');
    });
}
            function getSelectedProducts() {
                const checkboxes = document.querySelectorAll('.inventory-checkbox:checked');
                return Array.from(checkboxes).map(checkbox => {
                    const productId = checkbox.value;
                    return allProducts.find(p => p.id == productId);
                }).filter(Boolean);
            }

            function exportInventoryReport() {
                showLoading('Generating inventory report...');
                
                // Simulate report generation
                setTimeout(() => {
                    const csvContent = generateInventoryCSV();
                    downloadCSV(csvContent, 'inventory_report_' + new Date().toISOString().split('T')[0] + '.csv');
                    
                    hideLoading();
                    showToast('success', 'Inventory report exported successfully!');
                }, 1500);
            }

            function generateInventoryCSV() {
                const headers = ['Product ID', 'Product Name', 'Category', 'Stock', 'Price', 'Total Value', 'Status'];
                const rows = allProducts.map(product => {
                    const stock = parseInt(product.stock) || 0;
                    const price = parseFloat(product.price) || 0;
                    const value = stock * price;
                    const status = getStockStatus(stock).label;
                    
                    return [
                        product.id,
                        product.name,
                        product.category_name || 'N/A',
                        stock,
                        price.toFixed(2),
                        value.toFixed(2),
                        status
                    ];
                });
                
                return [headers, ...rows].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
            }

            function viewOutOfStockProducts() {
                const statusFilter = document.getElementById('inventoryStatusFilter');
                if (statusFilter) {
                    statusFilter.value = 'out-of-stock';
                    filterInventory();
                }
            }

            function viewLowStockProducts() {
                const statusFilter = document.getElementById('inventoryStatusFilter');
                if (statusFilter) {
                    statusFilter.value = 'low-stock';
                    filterInventory();
                }
            }

            function addStockMovementRecord(productId, type, quantity, reason, notes) {
                const product = allProducts.find(p => p.id == productId);
                if (!product) return;
                
                const movement = {
                    product_id: productId,
                    product_name: product.name,
                    type: type,
                    quantity: quantity,
                    reason: reason,
                    notes: notes,
                    date: new Date().toISOString(),
                    user: 'Current User' // This should come from authentication
                };
                
                // Add to movement history (in a real app, this would be sent to the server)
                updateStockMovementHistory(movement);
            }

            function updateStockMovementHistory(movement) {
                const historyTable = document.getElementById('stockMovementHistory');
                if (!historyTable) return;
                
                // Remove "no movements" message if it exists
                if (historyTable.querySelector('td[colspan="5"]')) {
                    historyTable.innerHTML = '';
                }
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${htmlEscape(movement.product_name)}</td>
                    <td>
                        <span class="badge bg-secondary">${movement.type.replace('_', ' ')}</span>
                    </td>
                    <td class="fw-bold">${movement.quantity}</td>
                    <td>${formatDateTime(movement.date)}</td>
                    <td>${htmlEscape(movement.user)}</td>
                `;
                
                // Insert at the beginning
                historyTable.insertBefore(row, historyTable.firstChild);
                
                // Keep only the last 10 entries
                while (historyTable.children.length > 10) {
                    historyTable.removeChild(historyTable.lastChild);
                }
            }

            // Utility functions
            function updateElement(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            }

            function animateValue(elementId, endValue, isCurrency = false, currencySymbol = '') {
                const element = document.getElementById(elementId);
                if (!element) return;
                
                const startValue = 0;
                const duration = 1000;
                const startTime = performance.now();
                
                function animate(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    const currentValue = Math.floor(startValue + (endValue - startValue) * progress);
                    
                    if (isCurrency) {
                        element.textContent = currencySymbol + currentValue.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    } else {
                        element.textContent = currentValue.toLocaleString();
                    }
                    
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                }
                
                requestAnimationFrame(animate);
            }

            function htmlEscape(str) {
                if (!str) return '';
                return str.replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
            }

            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-PH');
            }

            function formatDateTime(dateString) {
                if (!dateString) return 'N/A';
                const date = new Date(dateString);
                return date.toLocaleString('en-PH');
            }

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            function showLoading(message = 'Loading...') {
                // Implementation depends on your loading indicator system
                console.log('Loading:', message);
            }

            function hideLoading() {
                // Implementation depends on your loading indicator system
                console.log('Loading complete');
            }

            function showToast(type, message) {
                // Implementation depends on your toast notification system
                console.log(`${type.toUpperCase()}: ${message}`);
            }

            function downloadCSV(content, filename) {
                const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                if (link.download !== undefined) {
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', filename);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }

            // Add these missing functions that are referenced but not defined
            function updateBulkActionsVisibility() {
                // Implementation for showing/hiding bulk action buttons
                const selectedCount = document.querySelectorAll('.inventory-checkbox:checked').length;
                console.log(`${selectedCount} products selected for bulk actions`);
            }

            function viewStockHistory(productId) {
                showToast('info', `View stock history for product ${productId} - feature coming soon!`);
            }

            function setReorderPoint(productId) {
                showToast('info', `Set reorder point for product ${productId} - feature coming soon!`);
            }

            function restockProduct(productId) {
                showToast('info', `Restock product ${productId} - feature coming soon!`);
            }
            </script>

            <!--analytics CHARTS Section -->
            <script>
// Analytics JavaScript
let mainChart, statusChart, categoryChart, registrationChart;
let sparklineCharts = {};

// Initialize analytics when section is shown
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts when analytics section becomes visible
    const analyticsSection = document.getElementById('analytics-section');
    if (analyticsSection) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const isVisible = !analyticsSection.classList.contains('d-none');
                    if (isVisible && !mainChart) {
                        initializeAnalyticsCharts();
                    }
                }
            });
        });
        
        observer.observe(analyticsSection, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
});

function initializeAnalyticsCharts() {
    console.log('Initializing analytics charts...');
    
    // Initialize main charts
    createMainTrendChart();
    createOrderStatusChart();
    createCategoryChart();
    createRegistrationChart();
    
    // Initialize sparklines
    createSparklines();
    
    // Animate metric counters
    animateMetrics();
}

function createMainTrendChart() {
    const ctx = document.getElementById('mainTrendChart');
    if (!ctx) return;
    
    // Use the monthlyRevenue data that's already calculated in PHP
    const chartData = <?= json_encode($monthlyRevenue) ?>;
    
    console.log('Chart Data:', chartData); // Debug: check what data we have
    
    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.month),
            datasets: [
                {
                    label: 'Revenue',
                    data: chartData.map(item => item.revenue),
                    borderColor: 'rgb(20, 110, 180)',
                    backgroundColor: 'rgba(20, 110, 180, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: 'rgb(20, 110, 180)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    yAxisID: 'y'
                },
                {
                    label: 'Orders',
                    data: chartData.map(item => item.orders),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Revenue') {
                                return 'Revenue: ₱' + context.parsed.y.toLocaleString('en-PH', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            } else {
                                return 'Orders: ' + context.parsed.y;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000) {
                                return '₱' + (value / 1000) + 'k';
                            }
                            return '₱' + value;
                        },
                        font: { size: 12 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: function(value) {
                            return value + ' orders';
                        },
                        font: { size: 12 },
                        stepSize: 1
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}


function createOrderStatusChart() {
    const ctx = document.getElementById('orderStatusChart');
    if (!ctx) return;

    // ✅ Use actual order status data from PHP safely (default to 0)
    const statusData = {
        pending: <?= $orderStats['pending_orders'] ?? 0 ?>,
        processing: <?= $orderStats['processing_orders'] ?? 0 ?>,
        shipped: <?= $orderStats['shipped_orders'] ?? 0 ?>,
        delivered: <?= $orderStats['delivered_orders'] ?? 0 ?>,
        cancelled: <?= $orderStats['cancelled_orders'] ?? 0 ?>
    };

    // ✅ Destroy previous chart instance (avoid duplicates if function is called again)
    if (window.statusChart) {
        window.statusChart.destroy();
    }

    // ✅ Create chart with dynamic data
    window.statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#f2ca00',  // Pending
                    '#17a2b8',  // Processing
                    '#146eb4',  // Shipped
                    '#00a650',  // Delivered
                    '#ff4d4f'   // Cancelled
                ],
                borderWidth: 0,
                hoverBorderWidth: 3,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label;
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}


function createCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    
    // Use actual category data
    const categoryData = <?= json_encode($categoryPerformance ?? []) ?>;
    
    if (categoryData.length === 0) {
        // Fallback sample data
        categoryData = [
            { name: 'Tops', total_products: 45, active_products: 42 },
            { name: 'Bottoms', total_products: 35, active_products: 33 },
            { name: 'Footwear', total_products: 28, active_products: 25 },
            { name: 'Accessories', total_products: 22, active_products: 20 }
        ];
    }
    
    categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categoryData.map(item => item.name),
            datasets: [{
                label: 'Total Products',
                data: categoryData.map(item => item.total_products),
                backgroundColor: 'rgba(20, 110, 180, 0.8)',
                borderColor: 'rgb(20, 110, 180)',
                borderWidth: 1,
                borderRadius: 4
            }, {
                label: 'Active Products',
                data: categoryData.map(item => item.active_products),
                backgroundColor: 'rgba(0, 166, 80, 0.8)',
                borderColor: 'rgb(0, 166, 80)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    });
}

function createRegistrationChart() {
    const ctx = document.getElementById('registrationChart');
    if (!ctx) return;
    
    // Use actual user registration data or create sample
    const registrationData = <?= json_encode($userRegistrationTrend ?? []) ?>;
    
    const chartData = registrationData.length > 0 ? registrationData : [
        { date: 'Mon', registrations: 5 },
        { date: 'Tue', registrations: 8 },
        { date: 'Wed', registrations: 12 },
        { date: 'Thu', registrations: 7 },
        { date: 'Fri', registrations: 15 },
        { date: 'Sat', registrations: 20 },
        { date: 'Sun', registrations: 10 }
    ];
    
    registrationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [{
                label: 'New Registrations',
                data: chartData.map(item => item.registrations),
                borderColor: 'rgb(255, 153, 0)',
                backgroundColor: 'rgba(255, 153, 0, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: 'rgb(255, 153, 0)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5
                    }
                }
            }
        }
    });
}

function createSparklines() {
    // Revenue sparkline
    createSparkline('revenueSparkline', [15000, 18000, 22000, 25000, 28000, 35000], '#146eb4');
    
    // Orders sparkline
    createSparkline('ordersSparkline', [80, 95, 110, 120, 135, 165], '#00a650');
    
    // Average order value sparkline
    createSparkline('avgSparkline', [187, 189, 200, 208, 207, 212], '#f2ca00');
    
    // Customers sparkline
    createSparkline('customersSparkline', [450, 465, 480, 495, 510, 530], '#17a2b8');
}

function createSparkline(canvasId, data, color) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    
    sparklineCharts[canvasId] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: Array(data.length).fill(''),
            datasets: [{
                data: data,
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: { display: false },
                y: { display: false }
            },
            elements: {
                point: { radius: 0 }
            }
        }
    });
}

function animateMetrics() {
    // Calculate actual total revenue from users
    <?php
    $actualTotalRevenue = 0;
    if (is_array($users) && count($users) > 0) {
        $actualTotalRevenue = array_sum(array_column($users, 'total_spent'));
    }
    ?>
    
    // Animate the main metric values
    const metrics = [
        { id: 'totalRevenue', target: <?= $actualTotalRevenue ?>, prefix: '₱', decimals: 2 },
        { id: 'totalOrders', target: <?= $dashboardStats['total_orders'] ?? 0 ?> },
        { id: 'totalCustomers', target: <?= $dashboardStats['total_users'] ?? 0 ?> }
    ];
    
    metrics.forEach(metric => {
        animateCounter(metric.id, metric.target, metric.prefix || '', metric.decimals || 0);
    });
}

function animateCounter(elementId, targetValue, prefix = '', decimals = 0) {
    const element = document.getElementById(elementId);
    if (!element || targetValue === 0) {
        if (element) {
            element.textContent = prefix + '0' + (decimals > 0 ? '.00' : '');
        }
        return;
    }
    
    const duration = 2000;
    const increment = targetValue / (duration / 16);
    let currentValue = 0;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        
        let displayValue = currentValue;
        if (decimals > 0) {
            displayValue = displayValue.toFixed(decimals);
        } else {
            displayValue = Math.floor(displayValue);
        }
        
        if (prefix === '₱') {
            displayValue = parseFloat(displayValue).toLocaleString('en-PH', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }
        
        element.textContent = prefix + displayValue;
    }, 16);
}

// Chart control functions
function toggleChart(type, button) {
    if (!mainChart) return;
    
    // Update active button
    const buttons = button.parentNode.querySelectorAll('.btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    
    // Use the same monthlyRevenue data
    const chartData = <?= json_encode($monthlyRevenue) ?>;
    
    switch (type) {
        case 'revenue':
            mainChart.data.datasets = [{
                label: 'Revenue',
                data: chartData.map(item => item.revenue),
                borderColor: 'rgb(20, 110, 180)',
                backgroundColor: 'rgba(20, 110, 180, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(20, 110, 180)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }];
            mainChart.options.scales.y.ticks.callback = function(value) {
                if (value >= 1000) {
                    return '₱' + (value / 1000) + 'k';
                }
                return '₱' + value;
            };
            delete mainChart.options.scales.y1;
            break;
            
        case 'orders':
            mainChart.data.datasets = [{
                label: 'Orders',
                data: chartData.map(item => item.orders),
                borderColor: 'rgb(0, 166, 80)',
                backgroundColor: 'rgba(0, 166, 80, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: 'rgb(0, 166, 80)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }];
            mainChart.options.scales.y.ticks.callback = function(value) {
                return value;
            };
            delete mainChart.options.scales.y1;
            break;
            
        case 'both':
            mainChart.data.datasets = [{
                label: 'Revenue',
                data: chartData.map(item => item.revenue),
                borderColor: 'rgb(20, 110, 180)',
                backgroundColor: 'rgba(20, 110, 180, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.4,
                pointRadius: 4,
                yAxisID: 'y'
            }, {
                label: 'Orders',
                data: chartData.map(item => item.orders),
                borderColor: 'rgb(0, 166, 80)',
                backgroundColor: 'rgba(0, 166, 80, 0.1)',
                borderWidth: 3,
                fill: false,
                tension: 0.4,
                pointRadius: 4,
                yAxisID: 'y1'
            }];
            
            mainChart.options.scales.y1 = {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value + ' orders';
                    },
                    stepSize: 1
                },
                grid: {
                    drawOnChartArea: false,
                }
            };
            break;
    }
    
    mainChart.update();
}

function changePeriod(period, button) {
    // Update active button
    const buttons = button.parentNode.querySelectorAll('.btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    
    
// Here you would typically fetch new data based on the period
    console.log('Changing period to:', period);
    
    // Show loading state
    showLoadingToast('Loading analytics for ' + period + '...');
    
    // Simulate data refresh
    setTimeout(() => {
        // In a real app, you'd fetch new data here
        showSuccessToast('Analytics updated for ' + period);
    }, 1000);
}

function exportAnalytics() {
    showLoadingToast('Preparing analytics report...');
    
    <?php
    $actualTotalRevenue = 0;
    if (is_array($users) && count($users) > 0) {
        $actualTotalRevenue = array_sum(array_column($users, 'total_spent'));
    }
    ?>
    
    // Simulate export process
    setTimeout(() => {
        const data = [
            ['Metric', 'Value'],
            ['Total Revenue', '₱<?= number_format($actualTotalRevenue, 2) ?>'],
            ['Total Orders', '<?= $dashboardStats['total_orders'] ?? 0 ?>'],
            ['Total Customers', '<?= $dashboardStats['total_users'] ?? 0 ?>'],
            ['Active Products', '<?= $dashboardStats['active_products'] ?? 0 ?>']
        ];
        
        const csvContent = data.map(row => row.join(',')).join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'analytics-report-' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showSuccessToast('Analytics report downloaded successfully!');
    }, 1500);
}

// Utility functions
function showLoadingToast(message) {
    showToast(message, 'info');
}

function showSuccessToast(message) {
    showToast(message, 'success');
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.setAttribute('role', 'alert');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Cleanup function when leaving analytics
function destroyCharts() {
    if (mainChart) mainChart.destroy();
    if (statusChart) statusChart.destroy(); 
    if (categoryChart) categoryChart.destroy();
    if (registrationChart) registrationChart.destroy();
    
    Object.values(sparklineCharts).forEach(chart => chart.destroy());
    sparklineCharts = {};
    
    mainChart = statusChart = categoryChart = registrationChart = null;
}
            </script>

            <!--Dashboard CHARTS Section -->
            <script>
            // Enhanced Dashboard JavaScript
            let dashboardCharts = {};
            let dashboardData = {
                revenue: <?= json_encode($monthlyRevenue ?? []) ?>,
                orderStats: <?= json_encode($orderStats ?? []) ?>,
                categoryPerformance: <?= json_encode($categoryPerformance ?? []) ?>,
                topProducts: <?= json_encode($topProducts ?? []) ?>,
                recentOrders: <?= json_encode($recentOrders ?? []) ?>,
                alerts: <?= json_encode($alerts ?? []) ?>
            };

            // Initialize dashboard when section becomes visible
            document.addEventListener('DOMContentLoaded', function() {
                const dashboardSection = document.getElementById('dashboard-section');
                if (dashboardSection && !dashboardSection.classList.contains('d-none')) {
                    initializeDashboard();
                }
                
                // Observer for when dashboard becomes visible
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            const isVisible = !dashboardSection.classList.contains('d-none');
                            if (isVisible && Object.keys(dashboardCharts).length === 0) {
                                initializeDashboard();
                            }
                        }
                    });
                });
                
                if (dashboardSection) {
                    observer.observe(dashboardSection, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                }
            });

            function initializeDashboard() {
                console.log('Initializing enhanced dashboard...');
                
                // Create charts
                createDashboardSalesChart();
                createDashboardOrderStatusMini();
                createDashboardSparklines();
                
                // Update real-time data
                updateDashboardMetrics();
                updateActivityFeed();
                
                // Start auto-refresh
                startDashboardAutoRefresh();
            }

            function createDashboardSalesChart() {
                const ctx = document.getElementById('dashboardSalesChart');
                if (!ctx) return;
                
                const salesData = dashboardData.revenue.length > 0 ? dashboardData.revenue : [
                    { month: 'Jan', revenue: 25000, orders: 120 },
                    { month: 'Feb', revenue: 28000, orders: 135 },
                    { month: 'Mar', revenue: 32000, orders: 150 },
                    { month: 'Apr', revenue: 29000, orders: 140 },
                    { month: 'May', revenue: 35000, orders: 165 },
                    { month: 'Jun', revenue: 38000, orders: 180 }
                ];
                
                dashboardCharts.sales = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: salesData.map(item => item.month || item.date),
                        datasets: [
                            {
                                label: 'Revenue',
                                data: salesData.map(item => item.revenue),
                                backgroundColor: 'rgba(13, 110, 253, 0.8)',
                                borderColor: 'rgb(13, 110, 253)',
                                borderWidth: 1,
                                borderRadius: 4,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Orders',
                                data: salesData.map(item => item.orders),
                                type: 'line',
                                borderColor: 'rgb(25, 135, 84)',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                borderWidth: 3,
                                fill: false,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        if (context.dataset.label === 'Revenue') {
                                            return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                                        } else {
                                            return 'Orders: ' + context.parsed.y;
                                        }
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                position: 'left',
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + (value / 1000) + 'k';
                                    },
                                    font: { size: 11 }
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                ticks: {
                                    callback: function(value) {
                                        return value + ' orders';
                                    },
                                    font: { size: 11 }
                                },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            }

            function createDashboardOrderStatusMini() {
                const ctx = document.getElementById('dashboardOrderStatusMini');
                if (!ctx) return;
                
                const orderData = dashboardData.orderStats;
                const statusData = [
                    orderData.pending_orders || 0,
                    orderData.processing_orders || 0,
                    orderData.shipped_orders || 0,
                    orderData.delivered_orders || 0,
                    orderData.cancelled_orders || 0
                ];
                
                dashboardCharts.orderStatusMini = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'],
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                '#ffc107',  // Pending
                                '#17a2b8',  // Processing
                                '#0d6efd',  // Shipped
                                '#198754',  // Delivered
                                '#dc3545'   // Cancelled
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend
: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label;
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function createDashboardSparklines() {
                // Revenue sparkline
                const revenueData = dashboardData.revenue.map(item => item.revenue || 0);
                createSparklineChart('dashboardRevenueSparkline', revenueData, '#0d6efd');
                
                // Orders sparkline
                const ordersData = dashboardData.revenue.map(item => item.orders || 0);
                createSparklineChart('dashboardOrdersSparkline', ordersData, '#198754');
                
                // Products sparkline (simulate growth)
                const productsData = [45, 47, 46, 48, 50, 52];
                createSparklineChart('dashboardProductsSparkline', productsData, '#0dcaf0');
                
                // Customers sparkline (simulate growth)
                const customersData = [120, 125, 130, 135, 142, 150];
                createSparklineChart('dashboardCustomersSparkline', customersData, '#ffc107');
            }

            function createSparklineChart(canvasId, data, color) {
                const ctx = document.getElementById(canvasId);
                if (!ctx) return;
                
                dashboardCharts[canvasId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Array(data.length).fill(''),
                        datasets: [{
                            data: data,
                            borderColor: color,
                            backgroundColor: color + '20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHoverBackgroundColor: color,
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                enabled: true,
                                displayColors: false,
                                callbacks: {
                                    title: function() { return ''; },
                                    label: function(context) {
                                        return context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        },
                        elements: {
                            point: { radius: 0 }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }

            function updateDashboardMetrics() {
                // Animate metric values
                animateDashboardValue('dashboardRevenue', <?php
    if (is_array($users) && count($users) > 0) {
        echo array_sum(array_column($users, 'total_spent'));
    } else {
        echo 0;
    }
?>, '₱');
                animateDashboardValue('dashboardOrders', <?= $dashboardStats['total_orders'] ?? 156 ?>);
                animateDashboardValue('dashboardProducts', <?= $dashboardStats['total_products'] ?? 67 ?>);
                animateDashboardValue('dashboardCustomers', <?= $dashboardStats['total_users'] ?? 89 ?>);
                
                // Update supporting metrics
                updateElement('pendingOrders', <?= $orderStats['pending_orders'] ?? 0 ?>);
                updateElement('activeProducts', <?= $dashboardStats['active_products'] ?? 60 ?>);
                updateElement('completedOrders', <?= $orderStats['delivered_orders'] ?? 0 ?>);
                updateElement('pendingOrdersQuick', <?= $orderStats['pending_orders'] ?? 0 ?>);
                updateElement('shippedOrders', <?= $orderStats['shipped_orders'] ?? 0 ?>);
                updateElement('lowStockItems', <?= count($lowStockProducts ?? []) ?>);
                
                // Calculate and update new customers today
                const today = new Date().toISOString().split('T')[0];
                const newCustomersToday = dashboardData.recentOrders ? 
                    dashboardData.recentOrders.filter(order => order.created_at.startsWith(today)).length : 0;
                updateElement('newCustomers', newCustomersToday);
                
                // Update trend indicators
                updateTrendIndicators();
            }

            function updateTrendIndicators() {
                // Simulate trend calculations (in real app, calculate from historical data)
                const trends = {
                    revenue: { percentage: 12.5, direction: 'up' },
                    orders: { percentage: 8.2, direction: 'up' },
                    products: { percentage: 0, direction: 'neutral' },
                    customers: { percentage: 15.3, direction: 'up' }
                };
                
                updateTrendIndicator('revenueTrend', trends.revenue);
                updateTrendIndicator('ordersTrend', trends.orders);
                updateTrendIndicator('productsTrend', trends.products);
                updateTrendIndicator('customersTrend', trends.customers);
            }

            function updateTrendIndicator(elementId, trend) {
                const element = document.getElementById(elementId);
                if (!element) return;
                
                const icon = element.querySelector('i');
                const span = element.querySelector('span');
                
                // Remove existing classes
                element.classList.remove('trend-up', 'trend-down', 'trend-neutral');
                icon.classList.remove('bi-arrow-up', 'bi-arrow-down', 'bi-dash');
                
                // Add appropriate classes
                if (trend.direction === 'up') {
                    element.classList.add('trend-up');
                    icon.classList.add('bi-arrow-up');
                    span.textContent = `+${trend.percentage}% vs last period`;
                } else if (trend.direction === 'down') {
                    element.classList.add('trend-down');
                    icon.classList.add('bi-arrow-down');
                    span.textContent = `-${trend.percentage}% vs last period`;
                } else {
                    element.classList.add('trend-neutral');
                    icon.classList.add('bi-dash');
                    span.textContent = 'No change';
                }
            }

            function updateActivityFeed() {
                const activityFeed = document.getElementById('activityFeed');
                if (!activityFeed || !dashboardData.recentOrders) return;
                
                // Add simulated real-time activities
                const activities = [
                    {
                        type: 'order',
                        icon: 'bi-bag-plus',
                        iconBg: 'bg-primary',
                        title: 'New Order Received',
                        description: 'Order #' + (Math.floor(Math.random() * 1000) + 1000),
                        time: 'Just now'
                    },
                    {
                        type: 'customer',
                        icon: 'bi-person-plus',
                        iconBg: 'bg-success',
                        title: 'New Customer Registration',
                        description: 'Welcome new customer!',
                        time: '2 min ago'
                    },
                    {
                        type: 'inventory',
                        icon: 'bi-box-seam',
                        iconBg: 'bg-warning',
                        title: 'Low Stock Alert',
                        description: '3 products need restocking',
                        time: '5 min ago'
                    }
                ];
                
                // Update activity feed with real recent orders if available
                if (dashboardData.recentOrders.length > 0) {
                    activityFeed.innerHTML = dashboardData.recentOrders.slice(0, 5).map(order => `
                        <div class="activity-item d-flex align-items-center p-3 border-bottom">
                            <div class="activity-icon bg-primary text-white rounded-circle me-3">
                                <i class="bi bi-bag-plus"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">New Order #${order.order_number}</div>
                                <small class="text-muted">
                                    ${order.username} • ₱${parseFloat(order.total_amount).toLocaleString()}
                                </small>
                            </div>
                            <small class="text-muted">${formatTimeAgo(order.created_at)}</small>
                        </div>
                    `).join('');
                }
            }

            function animateDashboardValue(elementId, targetValue, prefix = '') {
                const element = document.getElementById(elementId);
                if (!element || targetValue === 0) return;
                
                const duration = 1500;
                const startValue = 0;
                const increment = targetValue / (duration / 16);
                let currentValue = startValue;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= targetValue) {
                        currentValue = targetValue;
                        clearInterval(timer);
                    }
                    
                    let displayValue = Math.floor(currentValue);
                    if (prefix === '₱') {
                        element.textContent = prefix + displayValue.toLocaleString();
                    } else {
                        element.textContent = displayValue.toLocaleString();
                    }
                }, 16);
            }

            function changeDashboardPeriod(period, button) {
                // Update active button
                const buttons = button.parentNode.querySelectorAll('.btn');
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                showDashboardLoading('Updating dashboard for ' + period + '...');
                
                // Simulate data refresh for different periods
                setTimeout(() => {
                    // In a real app, you'd fetch new data based on the period
                    updateDashboardMetrics();
                    hideDashboardLoading();
                    showDashboardToast('Dashboard updated for ' + period, 'success');
                    
                    // Update last updated time
                    updateElement('revenueLastUpdate', 'Just now');
                }, 1000);
            }

            function refreshDashboard() {
                showDashboardLoading('Refreshing dashboard data...');
                
                // Simulate data refresh
                setTimeout(() => {
                    updateDashboardMetrics();
                    updateActivityFeed();
                    
                    // Refresh charts
                    Object.values(dashboardCharts).forEach(chart => {
                        if (chart && chart.update) {
                            chart.update();
                        }
                    });
                    
                    hideDashboardLoading();
                    showDashboardToast('Dashboard refreshed successfully!', 'success');
                    
                    // Update timestamps
                    const now = new Date();
                    updateElement('revenueLastUpdate', now.toLocaleTimeString());
                }, 1500);
            }

            function toggleSalesChart(type, button) {
                if (!dashboardCharts.sales) return;
                
                // Update active button
                const buttons = button.parentNode.querySelectorAll('.btn');
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Update chart based on type (daily, weekly, monthly)
                let newData, newLabels;
                
                switch(type) {
                    case 'daily':
                        newLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        newData = {
                            revenue: [3500, 4200, 3800, 4500, 5200, 6100, 4800],
                            orders: [18, 22, 19, 24, 28, 32, 25]
                        };
                        break;
                    case 'weekly':
                        newLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                        newData = {
                            revenue: [28000, 32000, 29000, 35000],
                            orders: [145, 167, 152, 180]
                        };
                        break;
                    case 'monthly':
                    default:
                        newLabels = dashboardData.revenue.map(item => item.month || item.date);
                        newData = {
                            revenue: dashboardData.revenue.map(item => item.revenue),
                            orders: dashboardData.revenue.map(item => item.orders)
                        };
                        break;
                }
                
                dashboardCharts.sales.data.labels = newLabels;
                dashboardCharts.sales.data.datasets[0].data = newData.revenue;
                dashboardCharts.sales.data.datasets[1].data = newData.orders;
                dashboardCharts.sales.update();
            }

            function refreshActivity() {
                showDashboardLoading('Refreshing activity feed...');
                
                setTimeout(() => {
                    updateActivityFeed();
                    hideDashboardLoading();
                    showDashboardToast('Activity feed refreshed!', 'info');
                }, 800);
            }

            function startDashboardAutoRefresh() {
                // Auto-refresh every 5 minutes
                setInterval(() => {
                    updateDashboardMetrics();
                    updateActivityFeed();
                    
                    // Update "last updated" timestamps
                    const now = new Date();
                    updateElement('revenueLastUpdate', now.toLocaleTimeString());
                }, 300000); // 5 minutes
            }

            function handleAlertAction(action) {
                switch(action) {
                    case 'view_products':
                        showSection('products-section');
                        break;
                    case 'view_orders':
                        showSection('orders-section');
                        break;
                    case 'review_categories':
                        showSection('category-section');
                        break;
                    default:
                        showDashboardToast('Feature not implemented yet', 'info');
                }
            }

            // Utility functions
            function updateElement(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = value;
                }
            }

            function formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffInMinutes = Math.floor((now - date) / (1000 * 60));
                
                if (diffInMinutes < 1) return 'Just now';
                if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
                if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
                return `${Math.floor(diffInMinutes / 1440)}d ago`;
            }

            function showDashboardLoading(message) {
                // Create or update loading indicator
                let loadingEl = document.getElementById('dashboard-loading');
                if (!loadingEl) {
                    loadingEl = document.createElement('div');
                    loadingEl.id = 'dashboard-loading';
                    loadingEl.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
                    loadingEl.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    loadingEl.style.zIndex = '9999';
                    loadingEl.innerHTML = `
                        <div class="bg-white rounded p-4 text-center">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="loading-message">${message}</div>
                        </div>
                    `;
                    document.body.appendChild(loadingEl);
                } else {
                    loadingEl.querySelector('.loading-message').textContent = message;
                }
            }

            function hideDashboardLoading() {
                const loadingEl = document.getElementById('dashboard-loading');
                if (loadingEl) {
                    loadingEl.remove();
                }
            }

            function showDashboardToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed`;
                toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999;';
                toast.setAttribute('role', 'alert');
                
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (toast && toast.parentNode) {
                        toast.remove();
                    }
                }, 3000);
            }

            // Cleanup function
            function destroyDashboardCharts() {
                Object.values(dashboardCharts).forEach(chart => {
                    if (chart && chart.destroy) {
                        chart.destroy();
                    }
                });
                dashboardCharts = {};
            }
            </script>

            <script>
            // Auto-hide alerts after 5 seconds
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }, 5000);
                });

                // Scroll to specific section if needed
                <?php if (isset($_SESSION['scroll_to'])): ?>
                const scrollToSection = '<?= $_SESSION['scroll_to'] ?>';
                <?php unset($_SESSION['scroll_to']); ?>
                
                setTimeout(() => {
                    const section = document.getElementById(scrollToSection + '-section');
                    if (section) {
                        // If the section is hidden, show it first
                        if (section.classList.contains('d-none')) {
                            // Hide all sections
                            document.querySelectorAll('.content-section').forEach(sec => {
                                sec.classList.add('d-none');
                            });
                            // Show the target section
                            section.classList.remove('d-none');
                            
                            // Update active nav link
                            document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
                                link.classList.remove('active');
                                if (link.getAttribute('data-bs-target') === scrollToSection + '-section') {
                                    link.classList.add('active');
                                }
                            });
                        }
                        
                        // Scroll to the section
                        section.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 100);
                <?php endif; ?>
            });

            function runBackup() {
    const btn = document.getElementById('backupBtn');
    const msg = document.getElementById('backupMsg');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

    fetch('actions/backup_action.php?action=generate')
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                msg.innerHTML = '<div class="alert alert-success">Backup generated successfully! Downloading...</div>';
                // Direct the browser to the download action
                window.location.href = 'actions/backup_action.php?action=download&file=' + data.filename;
            } else {
                msg.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(err => {
            msg.innerHTML = '<div class="alert alert-danger">Server Error occurred.</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-download me-2"></i>Generate & Download Backup (.sql)';
        });
}                                                    

function runDatabaseBackup() {
    const btn = document.getElementById('btnBackup');
    const status = document.getElementById('backupStatus');

    // Disable button to prevent double-clicking
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating Snapshot...';

    // Call the handler to GENERATE the file
    fetch('actions/backup_handler.php?action=generate')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                status.innerHTML = '<div class="alert alert-success">Backup Ready! Starting download...</div>';
                
                // Now trigger the DOWNLOAD action using the filename returned
                window.location.href = 'actions/backup_handler.php?action=download&file=' + data.filename;
            } else {
                status.innerHTML = '<div class="alert alert-danger">Error: ' + (data.message || 'Check server logs') + '</div>';
            }
        })
        .catch(err => {
            console.error(err);
            status.innerHTML = '<div class="alert alert-danger">Server connection failed.</div>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-down-fill me-2"></i>Generate On-Demand Backup';
        });
}

function runDatabaseBackup() {
    const btn = document.getElementById('btnBackup');
    const status = document.getElementById('backupStatus');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing Snapshot...';

    fetch('../admin/actions/backup_action.php?action=generate')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                status.innerHTML = '<div class="text-success">Success! File generated. Starting download...</div>';
                // Trigger the secure download
                window.location.href = '../admin/actions/backup_action.php?action=download&file=' + data.filename;
            } else {
                status.innerHTML = '<div class="text-danger">Error: ' + (data.message || 'Check server permissions.') + '</div>';
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-down-fill me-2"></i>Generate On-Demand Backup';
        });
}

function triggerBackup() {
    const btn = document.getElementById('btnBackup');
    const status = document.getElementById('backupStatus');

    btn.disabled = true;
    btn.innerHTML = 'Generating...';

    // Step 1: Tell the server to generate the file
    fetch('actions/backup_handler.php?action=generate')
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                status.innerHTML = '<span class="text-success">Backup complete! Downloading...</span>';
                // Step 2: Redirect to the download action
                window.location.href = 'actions/backup_handler.php?action=download&file=' + data.filename;
            } else {
                status.innerHTML = '<span class="text-danger">Error: ' + data.message + '</span>';
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-download me-2"></i>Generate & Download SQL';
        });
}

// DATABASE BACKUP
// Load history when the page opens
document.addEventListener('DOMContentLoaded', function() {
    loadBackupHistory();
});

function loadBackupHistory() {
    const handlerUrl = 'actions/backup_handler.php';
    
    fetch(handlerUrl + '?action=list')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // UPDATE THE NEW LABELS
                document.getElementById('lastBackupDate').innerText = data.backups.length > 0 ? data.backups[0].date : "Never";
                document.getElementById('lastBackupAdmin').innerText = data.last_admin || "System";

                // UPDATE THE PROGRESS BAR & COUNT
                const count = data.backups.length;
                const maxBackups = 5;
                document.getElementById('backupCount').innerText = `${count} / ${maxBackups}`;
                
                const percentage = (count / maxBackups) * 100;
                document.getElementById('storageBar').style.width = percentage + '%';

                // Fill your table as usual...
                const tbody = document.getElementById('backupHistoryBody');
                // ... table logic here ...
            }
        });
}

// Update your button function to refresh the table
function handleBackupGeneration() {
    const btn = document.getElementById('btnBackup');
    const handlerUrl = 'actions/backup_handler.php';

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    fetch(handlerUrl + '?action=generate')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Refresh the table and download
                loadBackupHistory();
                window.location.href = handlerUrl + '?action=download&file=' + data.filename;
                
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cloud-download me-2"></i>Backup Database';
                }, 2000);
            }
        });
}

// Located inside admin-dashboard.php <script> block
function handleBackupGeneration() {
    const btn = document.getElementById('btnBackup');
    const status = document.getElementById('backupStatus');
    
    // This is the path we just verified works!
    const handlerUrl = 'actions/backup_handler.php'; 

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Downloading...';

    fetch(handlerUrl + '?action=generate')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                status.innerHTML = '<div class="alert alert-success py-2 mt-2">Backup Successful!</div>';
                
                // This triggers the actual file download to your computer
                window.location.href = handlerUrl + '?action=download&file=' + data.filename;
                
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-cloud-download me-2"></i>Backup Database';
                }, 3000);
            } else {
                alert("Error: " + data.message);
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert("Connection error. Try pressing CTRL+F5 to refresh.");
            btn.disabled = false;
        });
}

// Specific RBAC for Backup Section

document.addEventListener('DOMContentLoaded', loadBackupHistory);

function loadBackupHistory() {
    const handlerUrl = 'actions/backup_handler.php';
    const tbody = document.getElementById('backupHistoryBody');
    const badge = document.getElementById('lastBackupBadge');

    fetch(handlerUrl + '?action=list')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.backups.length > 0) {
                badge.innerHTML = `<i class="bi bi-clock-history me-1"></i> Last activity: ${data.backups[0].date}`;
                tbody.innerHTML = '';
                
                data.backups.forEach(file => {
                    tbody.innerHTML += `
                        <tr>
                            <td class="ps-0 py-3 fw-bold text-dark">${file.date}</td>
                            <td><span class="text-muted font-monospace small">${file.name}</span></td>
                            <td><span class="badge bg-light text-dark border-0">${file.size}</span></td>
                            <td class="pe-0 text-end">
                                <a href="${handlerUrl}?action=download&file=${file.name}" 
                                   class="btn btn-sm btn-outline-primary border-0 rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i> Download
                                </a>
                            </td>
                        </tr>`;
                });
            } else {
                badge.innerText = "No backups found";
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted">No backups found. Click generate to create one.</td></tr>';
            }
        });
}

$canAccessBackup = false;
$authorized_roles = ['Administrator', 'System Owner', 'IT Manager', 'admin'];

if (isset($_SESSION['role']) && in_array($_SESSION['role'], $authorized_roles)) {
    $canAccessBackup = true;
}
<?php if ($_SESSION['role'] === 'Administrator'): ?>
    <?php endif; ?>

    function loadMaintLogs() {
    const tableBody = document.getElementById('maintAuditLog');
    
    // Add a loading pulse effect
    tableBody.style.opacity = '0.6';

    fetch('get_maint_logs.php') // You can create a small file to echo the table rows
        .then(response => response.text())
        .then(html => {
            tableBody.innerHTML = html;
            tableBody.style.opacity = '1';
        })
        .catch(err => console.error('Failed to refresh logs:', err));
}

// Handle Form Submission - ALLOWS EMPTY FIELDS
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const saveBtn = document.getElementById('saveSystemBtn');
    const maintSwitch = document.getElementById('maintSwitch');
    const badgeContainer = document.getElementById('live-status-badge'); // The container for the status badge

    const formData = new FormData(this);

    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Syncing...';

    fetch('actions/update_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Update the badge visually without refreshing the page
            if (maintSwitch.checked) {
                badgeContainer.innerHTML = `
                    <span class="badge bg-danger p-3 px-4 shadow-sm animate-pulse">
                        <i class="fas fa-exclamation-triangle me-2"></i>Maintenance Mode Active
                    </span>`;
            } else {
                badgeContainer.innerHTML = `
                    <span class="badge bg-success p-3 px-4 shadow-sm">
                        <i class="fas fa-check-circle me-2"></i>System Operational
                    </span>`;
            }

            loadMaintLogs(); // Refresh the audit logs table
            alert('System status updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Connection failed.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Synchronize & Update System Status';
    });
});

document.getElementById('maintSwitch').addEventListener('change', function () {
    const notice = document.getElementById('public_notice').value.trim();
    const recoveryTime = document.getElementById('target_recovery_time').value.trim();

    if (this.checked) {
        if (!notice || !recoveryTime) {
            alert('Public Notice Message and Target Recovery Time are required before enabling Maintenance Mode.');
            this.checked = false;
        }
    }
});


            </script>


              <!-- RBAC CREATE NEW ROLE -->
            <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-shield-lock-fill me-2"></i>Create Management Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <form id="addManagementForm" action="" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="add_management_role" value="1">
                
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Fill in the details below to create a new staff or admin access.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="e.g. admin_juandelacruz" required minlength="4">
                            <div class="invalid-feedback">Username is required (min. 4 characters).</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" id="mgmtPassword" name="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('mgmtPassword')">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                            <div class="invalid-feedback">Password must be at least 8 characters.</div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark">Assign Role</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-briefcase"></i></span>
                            <select name="role" class="form-select" required>
                                <option value="" selected disabled>Choose a role...</option>
                                <option value="staff">Staff (Limited Access)</option>
                                <option value="admin">Administrator (Full Access)</option>
                            </select>
                            <div class="invalid-feedback">Please select a role.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-4 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Bootstrap Validation Logic
(function () {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()

// Password Toggle Function
function togglePasswordVisibility(id) {
    const pwd = document.getElementById(id);
    const icon = document.querySelector('#toggleIcon');
    if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = "password";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
// maintenance to pre
document.addEventListener('DOMContentLoaded', function() {
    const maintSwitch = document.getElementById('maintSwitch');
    const msgInput = document.getElementById('maint_message_input');
    const timeInput = document.getElementById('recovery_time_input');
    const saveBtn = document.getElementById('saveSystemBtn');
    const errorMsg = document.getElementById('maint-error-msg');

    function validateMaintenance() {
        const hasMessage = msgInput.value.trim() !== "";
        const hasTime = timeInput.value.trim() !== "";
        const isSwitchOn = maintSwitch.checked;

        // If the switch is being turned ON, check for message and time
        if (isSwitchOn && (!hasMessage || !hasTime)) {
            maintSwitch.classList.add('is-invalid');
            errorMsg.classList.remove('d-none');
            saveBtn.disabled = true;
            saveBtn.classList.replace('btn-primary', 'btn-secondary');
            return false;
        } else {
            maintSwitch.classList.remove('is-invalid');
            errorMsg.classList.add('d-none');
            saveBtn.disabled = false;
            saveBtn.classList.replace('btn-secondary', 'btn-primary');
            return true;
        }
    }

    // Listen for changes on all relevant fields
    [maintSwitch, msgInput, timeInput].forEach(el => {
        el.addEventListener('change', validateMaintenance);
        el.addEventListener('input', validateMaintenance);
    });

    // Final check on form submission
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        if (!validateMaintenance()) {
            e.preventDefault();
            alert("Error: You must provide a Public Notice Message and Target Recovery Time before activating Maintenance Mode.");
        }
    });

    // Run initial check
    validateMaintenance();
});

// maintenance to pre
if (isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] == '1') {
    $message = trim($_POST['maint_message'] ?? '');
    $time = trim($_POST['recovery_time'] ?? '');

    if (empty($message) || empty($time)) {
        // Redirect back with an error if they bypassed the JavaScript
        header("Location: admin-dashboard.php?error=missing_fields");
        exit();
    }
}


</script>

    <!-- ===================================================================
         ✅ RBAC AUTO-SCROLL SCRIPT
         =================================================================== -->
    <script>
    // Auto-navigate to RBAC section if hash is present (after form submission)
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#rbac') {
            // Click the RBAC nav link to show the section
            const rbacLink = document.querySelector('a[href="#"][onclick*="rbac"]');
            if (rbacLink) {
                rbacLink.click();
            }
            
            // Scroll to the top of the section
            setTimeout(function() {
                const rbacSection = document.getElementById('rbac-section');
                if (rbacSection) {
                    rbacSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 300);
        }
    });
    </script>
    <!-- =================================================================== -->

    <!-- ===================================================================
         ✅ AVATAR UPLOAD MODAL
         =================================================================== -->
    <div class="modal fade" id="avatarUploadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-camera-fill me-2"></i>Change Avatar
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form id="avatarUploadForm" action="upload-avatar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="avatar_user_id">
                        
                        <div class="text-center mb-4">
                            <p class="mb-2">Changing avatar for:</p>
                            <h6 class="fw-bold text-primary" id="avatar_username_display"></h6>
                        </div>
                        
                        <div class="mb-3">
                            <label for="avatar_file" class="form-label fw-semibold">
                                <i class="bi bi-image me-1"></i>Select New Avatar
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="avatar_file" 
                                   name="avatar_file" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                   required>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Recommended: Square image, max 2MB (JPG, PNG, GIF, WEBP)
                            </small>
                        </div>
                        
                        <!-- Preview Section -->
                        <div id="avatarPreview" class="text-center d-none mt-4">
                            <p class="text-muted small mb-2">Preview:</p>
                            <img id="previewImage" 
                                 src="" 
                                 alt="Avatar Preview" 
                                 class="rounded-circle shadow" 
                                 style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #e9ecef;">
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Upload Avatar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- =================================================================== -->
    
    </body>
</html>


            <!-- Bootstrap JS -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

            <!-- Custom Js For Admin -->
            <script src="../../../public/js/sidebar.js"></script>
            <script src="../../../public/js/ProductsAction.js"></script>
            <script src="../../../public/js/CategoryAction.js"></script>
            <script src="../../../public/js/OrderAction.js"></script>
            <script src="../../../public/js/CustomersAction.js"></script>
            <script src="../../../public/js/AdminProfile.js"></script>


            <script>
        // Trigger para sa Profile Modal
        function openProfileModal() {
            const modalEl = document.getElementById('profileModal');
            if (modalEl) {
                const myModal = new bootstrap.Modal(modalEl);
                myModal.show();
            }
        }

// ===================================================================
// ✅ AVATAR UPLOAD FUNCTIONALITY
// ===================================================================

/**
 * Open avatar upload modal for a specific user
 */
function uploadAvatar(userId, username) {
    document.getElementById('avatar_user_id').value = userId;
    document.getElementById('avatar_username_display').textContent = username;
    
    // Reset the form
    document.getElementById('avatarUploadForm').reset();
    document.getElementById('avatarPreview').classList.add('d-none');
    
    const modal = new bootstrap.Modal(document.getElementById('avatarUploadModal'));
    modal.show();
}

/**
 * Preview avatar before upload
 */
document.addEventListener('DOMContentLoaded', function() {
    const avatarFileInput = document.getElementById('avatar_file');
    
    if (avatarFileInput) {
        avatarFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    this.value = '';
                    document.getElementById('avatarPreview').classList.add('d-none');
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    this.value = '';
                    document.getElementById('avatarPreview').classList.add('d-none');
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('avatarPreview').classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
    }
});