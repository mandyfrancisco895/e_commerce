<?php
// Handle all product-related actions


function handleProductActions($product, $categoryController) {
    // Handle delete request
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        if ($product->delete($id)) {
            $_SESSION['success_message'] = "Product deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting product.";
        }
         $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
    }

    // Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    $sizes = isset($_POST['sizes']) ? implode(',', $_POST['sizes']) : null;

    // Check if category is inactive
    $category = $categoryController->getCategoryById($category_id);
    if ($category && $category['status'] === 'inactive') {
        $status = 'inactive';
    }

    // Upload image
    $targetDir = "../../../public/uploads/";
    $targetFile = $targetDir . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

if ($product->create($name, $description, $price, $image, $category_id, $stock, $status, $sizes)) {
    $_SESSION['success_message'] = "Product added successfully!";
    $_SESSION['refresh_shop_stock'] = true; // ✅ SET FLAG
   $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
}

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['action']) 
    && $_POST['action'] === 'update_stock') {

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        $product_id = intval($_POST['product_id']);
        $new_stock  = intval($_POST['new_stock']);
        $reason     = $_POST['reason'] ?? 'manual_update';
        $notes      = $_POST['notes'] ?? null;
        $user_id    = $_SESSION['user_id'] ?? null;

        // 1. Update stock
        $updated = $product->updateStock($product_id, $new_stock);

        if (!$updated) {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
            exit;
        }

        // 2. Log movement
        $product->logStockMovement($product_id, 'set', $new_stock, $reason, $notes, $user_id);

        echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
        exit;

    } catch (Exception $e) {
        error_log("Stock Update Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
        exit;
    }
}


// Handle stock adjustment (add/remove/set)
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['action']) 
    && $_POST['action'] === 'stock_adjustment') {

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        $product_id     = intval($_POST['product_id']);
        $movement_type  = $_POST['adjustment_type'] ?? null; // add | remove | set
        $quantity       = intval($_POST['quantity']);
        $reason         = $_POST['reason'] ?? null;
        $notes          = $_POST['notes'] ?? null;
        $user_id        = $_SESSION['user_id'] ?? null;

        // ✅ get current stock
        $currentProduct = $product->getById($product_id);
        if (!$currentProduct) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        $currentStock = (int)$currentProduct['stock'];

        // ✅ calculate new stock
        if ($movement_type === 'add') {
            $newStock = $currentStock + $quantity;
        } elseif ($movement_type === 'remove') {
            $newStock = max(0, $currentStock - $quantity);
        } elseif ($movement_type === 'set') {
            $newStock = $quantity;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid adjustment type']);
            exit;
        }

        // ✅ update stock in products
        $updated = $product->updateStock($product_id, $newStock);
        if (!$updated) {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
            exit;
        }

        // ✅ log into stock_movements
        $product->logStockMovement($product_id, $movement_type, $quantity, $reason, $notes, $user_id);

        echo json_encode([
            'success' => true,
            'message' => 'Stock adjustment applied successfully',
            'newStock' => $newStock
        ]);
        exit;

    } catch (Exception $e) {
        error_log("Stock Adjustment Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred']);
        exit;
    }
}


// Handle bulk stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['action']) 
    && $_POST['action'] === 'bulk_stock_update') {

    error_log("=== BULK UPDATE REQUEST RECEIVED ===");
    error_log("POST data: " . print_r($_POST, true));
    
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    try {
        if (!isset($_POST['updates'])) {
            error_log("ERROR: updates parameter missing");
            echo json_encode(['success' => false, 'message' => 'Updates parameter missing']);
            exit;
        }
        
        $updatesJson = $_POST['updates'];
        error_log("Updates JSON: " . $updatesJson);
        
        $updates = json_decode($updatesJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
            exit;
        }
        
        error_log("Decoded updates: " . print_r($updates, true));
        
        $reason = $_POST['reason'] ?? 'Bulk update';
        $operation = $_POST['operation'] ?? '';
        $user_id = $_SESSION['user_id'] ?? null;
        
        error_log("Reason: " . $reason);
        error_log("Operation: " . $operation);
        error_log("User ID: " . $user_id);
        
        if (!$updates || !is_array($updates)) {
            error_log("ERROR: Invalid update data");
            echo json_encode(['success' => false, 'message' => 'Invalid update data']);
            exit;
        }
        
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($updates as $update) {
            $productId = intval($update['product_id']);
            $newStock = intval($update['new_stock']);
            $currentStock = intval($update['current_stock']);
            $value = floatval($update['value']);
            
            error_log("Processing product ID: {$productId}, Current: {$currentStock}, New: {$newStock}");
            
            // Update product stock using existing method
            $updated = $product->updateStock($productId, $newStock);
            
            if ($updated) {
                $successCount++;
                error_log("✅ Product {$productId} updated successfully");
                
                // Log stock movement with actual quantity changed
                $quantityChanged = abs($newStock - $currentStock);
                $movementType = 'bulk_' . $operation;
                
                // Use existing logStockMovement method
                $logged = $product->logStockMovement(
                    $productId, 
                    $movementType, 
                    $quantityChanged, 
                    $reason, 
                    "Bulk operation: {$operation} by {$value}", 
                    $user_id
                );
                
                error_log("Movement logged: " . ($logged ? 'YES' : 'NO'));
            } else {
                $failedCount++;
                error_log("❌ Failed to update product {$productId}");
            }
        }
        
        $response = [
            'success' => true,
            'message' => "Successfully updated {$successCount} products" . 
                        ($failedCount > 0 ? ", {$failedCount} failed" : ""),
            'updated_count' => $successCount,
            'failed_count' => $failedCount
        ];
        
        error_log("Final response: " . json_encode($response));
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        error_log("EXCEPTION in bulk update: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage()
        ]);
        exit;
    }
}







// Handle update product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['edit_id'];  // Changed from 'product_id'
    $name = $_POST['edit_name'];  // Changed from 'name'
    $description = $_POST['edit_description'];  // Changed from 'description'
    $price = $_POST['edit_price'];  // Changed from 'price'
    $category_id = $_POST['edit_category'];  // Changed from 'category_id'
    $stock = $_POST['edit_stock'];  // Changed from 'stock'
    $status = $_POST['edit_status'];  // Changed from 'status'

    // Handle sizes (checkbox array)
    $sizes = isset($_POST['edit_sizes']) ? implode(',', $_POST['edit_sizes']) : null;

    // Handle image upload
    $image = $_FILES['edit_image']['name'];
    if (!empty($image)) {
        $targetDir = "../../../public/uploads/";
        $targetFile = $targetDir . basename($image);
        move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetFile);
    } else {
        $image = $_POST['current_image_hidden'];  // Changed from 'existing_image'
    }
    if ($product->update($id, $name, $description, $price, $image, $category_id, $stock, $status, $sizes)) {
        $_SESSION['success_message'] = "Product updated successfully!";
        $_SESSION['refresh_shop_stock'] = true; // ✅ SET FLAG
        $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
    }

}




}

// Handle all category-related actions
function handleCategoryActions($categoryController, $product) {
    // Handle add category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
        $name = trim($_POST['category_name']);
        $description = trim($_POST['category_description']);
        $status = $_POST['category_status'];
        
        if ($categoryController->create($name, $description, $status)) {
            $_SESSION['success_message'] = "Category added successfully!";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        } else {
            $_SESSION['error_message'] = "Error adding category.";
        }
    }

    // Handle update category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
        $id = intval($_POST['edit_category_id']);
        $name = trim($_POST['edit_category_name']);
        $description = trim($_POST['edit_category_description']);
        $status = $_POST['edit_category_status'];
        
        // Get current category status before update
        $current_category = $categoryController->getCategoryById($id);
        $old_status = $current_category['status'];
        
        if ($categoryController->update($id, $name, $description, $status)) {
            $_SESSION['success_message'] = "Category updated successfully!";
            
            $products_in_category = $product->getProductsByCategory($id);
            
            foreach ($products_in_category as $product_item) {
                if ($status === 'inactive' && $old_status === 'active') {
                    $product->updateStatus($product_item['id'], 'pending');
                }
                elseif ($status === 'active' && $old_status === 'inactive') {
                    if ($product_item['status'] === 'pending') {
                        $product->updateStatus($product_item['id'], 'active');
                    }
                }
            }
            
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        } else {
            $_SESSION['error_message'] = "Error updating category.";
           $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
    }

    // Handle delete category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
        $id = intval($_POST['delete_category_id']);
        
        if ($categoryController->delete($id)) {
            $_SESSION['success_message'] = "Category deleted successfully!";
           $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        } else {
            $_SESSION['error_message'] = "Error deleting category. Make sure no products are using this category.";
           $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
    }
}

// Handle all user-related actions
// FIXED UPLOAD FUNCTION - Update the session storage part:
function handleUserActions($user) {
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $userId = intval($_POST['user_id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $existingProfilePic = trim($_POST['existing_profile_pic'] ?? '');
        $removeProfilePic = isset($_POST['remove_profile_pic']);
        
        // Handle profile picture upload
        $profilePicFilename = null;
        
        // Check if user wants to remove current profile picture
        if ($removeProfilePic && !empty($existingProfilePic)) {
            // Delete the old profile picture file using absolute path
            $fullExistingPath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . basename($existingProfilePic);
            
            if (file_exists($fullExistingPath)) {
                unlink($fullExistingPath);
            }
            $profilePicFilename = null;
        } else {
            // Keep existing profile pic filename if not removing
            $profilePicFilename = basename($existingProfilePic);
        }
        
        // Handle new profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['profile_pic']['type'];
            
            if (in_array($fileType, $allowedTypes)) {
                // Validate file size (max 2MB)
                if ($_FILES['profile_pic']['size'] <= 2 * 1024 * 1024) {
                    // Generate unique filename
                    $fileExtension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                    $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;
                    
                    // Move uploaded file
                    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {
                        // Delete old profile picture if it exists
                        if (!empty($existingProfilePic)) {
                            $fullExistingPath = $_SERVER['DOCUMENT_ROOT'] . '/E-COMMERCE/public/uploads/' . basename($existingProfilePic);
                            
                            if (file_exists($fullExistingPath) && $fullExistingPath !== $uploadPath) {
                                unlink($fullExistingPath);
                            }
                        }
                        $profilePicFilename = $fileName;
                    } else {
                        $_SESSION['error_message'] = "Failed to upload profile picture.";
                       $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
                    }
                } else {
                    $_SESSION['error_message'] = "Profile picture must be less than 2MB.";
                   $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
                }
            } else {
                $_SESSION['error_message'] = "Invalid file type. Please upload JPEG, PNG, GIF, or WEBP image.";
               $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
            }
        }
        
        // Update database with profile information
        $result = $user->updateProfile($userId, $username, $email, $phone, $address, $profilePicFilename);
        
        if ($result === true) {
            // Update session data
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;
            $_SESSION['address'] = $address;
            
            // *** FIXED: Store correct web paths in session ***
            if ($profilePicFilename) {
                $_SESSION['profile_pic'] = '/E-COMMERCE/public/uploads/' . $profilePicFilename;
                $_SESSION['avatar'] = '/E-COMMERCE/public/uploads/' . $profilePicFilename;
            } elseif ($removeProfilePic) {
                // Clear session avatar data if removed
                unset($_SESSION['profile_pic']);
                unset($_SESSION['avatar']);
            }
            
            $_SESSION['avatar_initial'] = strtoupper(substr($username, 0, 1));
            $_SESSION['success_message'] = "Profile updated successfully!";
            
           $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        } elseif ($result === 'username_taken') {
            $_SESSION['error_message'] = "Username is already taken. Please choose a different one.";
        } elseif ($result === 'email_taken') {
            $_SESSION['error_message'] = "Email is already in use. Please use a different email.";
        } else {
            $_SESSION['error_message'] = "Failed to update profile. Please try again.";
        }
        
        $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
    }
    
    
}

// Add this new function to your actions.php file

function handleOrderActions($adminController) {
    // Start output buffering to catch any accidental output
    ob_start();

    // Handle AJAX order deletion ✅ ADD THIS
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
        // Clean any previous output
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        
        try {
            $orderId = $_POST['order_id'] ?? null;
            
            if (!$orderId) {
                echo json_encode(['success' => false, 'message' => 'Order ID is required']);
                exit;
            }
            
            $result = $adminController->deleteOrder($orderId);
            echo json_encode($result);
            exit;
            
        } catch (Exception $e) {
            error_log("Delete Order Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while deleting order']);
            exit;
        }
    }

    // Handle order status updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
        // Clean any previous output
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        
        try {
            $orderId = $_POST['order_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$orderId || !$status) {
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                exit;
            }
            
            $result = $adminController->updateOrderStatus($orderId, $status);
            echo json_encode($result);
            exit;
            
        } catch (Exception $e) {
            error_log("Update Order Status Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while updating order status']);
            exit;
        }
    }

    // Handle order items loading
    if (isset($_POST['action']) && $_POST['action'] === 'get_order_items') {
        header('Content-Type: application/json');
        
        if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
            echo json_encode(['success' => false, 'message' => 'Order ID is required']);
            exit;
        }
        
        $orderId = (int)$_POST['order_id'];
        $items = $adminController->getOrderItems($orderId);
        
        if ($items !== false) {
            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to load order items'
            ]);
        }
        exit;
    }
    
    // Clean up if not an AJAX request
    ob_end_flush();
}

function handleCustomerActions($user) {
    // Handle customer update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer'])) {
        // Debug: Log all POST data
        error_log("=== CUSTOMER UPDATE DEBUG ===");
        error_log("POST Data: " . print_r($_POST, true));
        
        // Get form data with proper validation
        $customerId = isset($_POST['edit_customer_id']) ? intval($_POST['edit_customer_id']) : 0;
        $firstName = isset($_POST['edit_customer_first_name']) ? trim($_POST['edit_customer_first_name']) : '';
        $lastName = isset($_POST['edit_customer_last_name']) ? trim($_POST['edit_customer_last_name']) : '';
        $email = isset($_POST['edit_customer_email']) ? trim($_POST['edit_customer_email']) : '';
        $phone = isset($_POST['edit_customer_phone']) ? trim($_POST['edit_customer_phone']) : '';
        $address = isset($_POST['edit_customer_address']) ? trim($_POST['edit_customer_address']) : '';
        $status = isset($_POST['edit_customer_status']) ? trim($_POST['edit_customer_status']) : 'Active';
        
        // *** IMPORTANT: Don't normalize status here - pass it as-is from the form ***
        error_log("Parsed Data:");
        error_log("Customer ID: " . $customerId);
        error_log("First Name: " . $firstName);
        error_log("Last Name: " . $lastName);
        error_log("Email: " . $email);
        error_log("Phone: " . $phone);
        error_log("Address: " . $address);
        error_log("Status: " . $status);
        
        // Validation with detailed error reporting
        if (empty($customerId) || $customerId <= 0) {
            error_log("ERROR: Invalid customer ID");
            $_SESSION['error_message'] = "Invalid customer ID. Please try again.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        if (empty($firstName)) {
            error_log("ERROR: First name is empty");
            $_SESSION['error_message'] = "First name is required.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        if (empty($lastName)) {
            error_log("ERROR: Last name is empty");
            $_SESSION['error_message'] = "Last name is required.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        if (empty($email)) {
            error_log("ERROR: Email is empty");
            $_SESSION['error_message'] = "Email is required.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("ERROR: Invalid email format");
            $_SESSION['error_message'] = "Please enter a valid email address.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        // Validate status - UPDATED to include Deactivated
        $validStatuses = ['Active', 'Deactivated', 'Blocked'];
        if (!in_array($status, $validStatuses)) {
            error_log("ERROR: Invalid status: " . $status);
            $_SESSION['error_message'] = "Invalid status selected.";
           $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        // Combine first and last name
        $fullName = trim($firstName . ' ' . $lastName);
        error_log("Full Name: " . $fullName);
        
        // Check if customer exists before updating
        $existingCustomer = $user->getUserById($customerId);
        if (!$existingCustomer) {
            error_log("ERROR: Customer not found with ID: " . $customerId);
            $_SESSION['error_message'] = "Customer not found.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        error_log("Existing customer found: " . $existingCustomer['username']);
        
        // Update customer
        try {
            $result = $user->updateCustomer($customerId, $fullName, $email, $phone, $address, $status);
            
            error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result === true) {
                $_SESSION['success_message'] = "Customer updated successfully!";
                error_log("SUCCESS: Customer updated successfully");
                header("Location: admin-dashboard.php#customers");
                exit;
            } elseif ($result === 'username_taken') {
                $_SESSION['error_message'] = "This name is already taken by another customer.";
                error_log("ERROR: Username taken");
            } elseif ($result === 'email_taken') {
                $_SESSION['error_message'] = "This email is already used by another customer.";
                error_log("ERROR: Email taken");
            } else {
                $_SESSION['error_message'] = "Failed to update customer. Database error occurred.";
                error_log("ERROR: Database operation failed");
            }
        } catch (Exception $e) {
            error_log("EXCEPTION: " . $e->getMessage());
            $_SESSION['error_message'] = "An unexpected error occurred. Please try again.";
        }
        
        $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
    }
    
    // Handle customer status updates (activate/deactivate/block) - UPDATED
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_status'])) {
        $userId = intval($_POST['user_id']);
        $status = trim($_POST['status']); // Don't normalize here either
        
        error_log("🔄 STATUS UPDATE REQUEST:");
        error_log("   User ID: " . $userId);
        error_log("   New Status: " . $status);
        
        // Validate status against the actual database values - UPDATED
        $validStatuses = ['Active', 'Deactivated', 'Blocked'];
        if (!in_array($status, $validStatuses)) {
            error_log("ERROR: Invalid status: " . $status);
            $_SESSION['error_message'] = "Invalid status selected.";
            $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
        }
        
        // Pass status as-is to the updateStatus method (let it handle normalization)
        if ($user->updateStatus($userId, $status)) {
            // Custom success messages based on status - UPDATED
            if ($status === 'Deactivated') {
                $_SESSION['success_message'] = "Customer deactivated successfully!";
            } elseif ($status === 'Active') {
                $_SESSION['success_message'] = "Customer activated successfully!";
            } elseif ($status === 'Blocked') {
                $_SESSION['success_message'] = "Customer blocked successfully!";
            } else {
                $_SESSION['success_message'] = "Customer status updated to {$status} successfully!";
            }
            error_log("✅ Status update successful");
        } else {
            $_SESSION['error_message'] = "Failed to update customer status.";
            error_log("❌ Status update failed");
        }
        
       $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
    }
    
    // Handle customer deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
        $userId = intval($_POST['user_id']);
        
        error_log("🗑️ DELETE REQUEST:");
        error_log("   User ID: " . $userId);
        
        if ($user->deleteCustomer($userId)) {
            $_SESSION['success_message'] = "deleted successfully!";
            error_log("✅ Customer deletion successful");
        } else {
            $_SESSION['error_message'] = "Failed to delete customer. This customer may have existing orders.";
            error_log("❌ Customer deletion failed");
        }
        
        $redirect_to = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';
header("Location: " . $redirect_to);
exit();
    }
}


?>

