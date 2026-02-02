<?php
// Handle all category-related actions
function handleCategoryActions($categoryController, $product) {
    // Handle add category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
        $name = trim($_POST['category_name']);
        $description = trim($_POST['category_description']);
        $status = $_POST['category_status'];
        
        if ($categoryController->create($name, $description, $status)) {
            $_SESSION['success_message'] = "Category added successfully!";
            header("Location: admin-dashboard.php#category");
            exit;
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
            
            // Handle product status changes based on category status change
            $products_in_category = $product->getProductsByCategory($id);
            
            foreach ($products_in_category as $product_item) {
                // Category is being deactivated
                if ($status === 'inactive' && $old_status === 'active') {
                    // Set products to pending
                    $product->updateStatus($product_item['id'], 'pending');
                }
                // Category is being activated
                elseif ($status === 'active' && $old_status === 'inactive') {
                    // If product was pending, check what status it should have
                    if ($product_item['status'] === 'pending') {
                        // For simplicity, set all to active, or you can implement logic to restore original status
                        $product->updateStatus($product_item['id'], 'active');
                    }
                }
            }
            
            header("Location: admin-dashboard.php#category");
            exit;
        } else {
            $_SESSION['error_message'] = "Error updating category.";
            header("Location: admin-dashboard.php#category");
            exit;
        }
    }

    // Handle delete category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
        $id = intval($_POST['delete_category_id']);
        
        if ($categoryController->delete($id)) {
            $_SESSION['success_message'] = "Category deleted successfully!";
            header("Location: admin-dashboard.php#category");
            exit;
        } else {
            $_SESSION['error_message'] = "Error deleting category. Make sure no products are using this category.";
            header("Location: admin-dashboard.php#category");
            exit;
        }
    }
}
?>