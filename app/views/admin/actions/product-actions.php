<?php
// Handle all product-related actions
function handleProductActions($product, $categoryController) {
    // Handle delete request
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']);
        if ($product->delete($id)) {
            $_SESSION['success_message'] = "Product deleted successfully!";
            header("Location: admin-dashboard.php?deleted=1#products");
        } else {
            $_SESSION['error_message'] = "Error deleting product.";
            header("Location: admin-dashboard.php?error=1#products");
        }
        exit;
    }

    // Handle add product
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
        $name = trim(htmlspecialchars($_POST['name']));
        $description = trim(htmlspecialchars($_POST['description']));
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $stock = intval($_POST['stock']);
        
        // Status logic: if admin sets -> use it, else stock-based
        $status = isset($_POST['status']) ? $_POST['status'] : ($stock > 0 ? 'active' : 'inactive');

        // Upload image
        $targetDir = "../../../public/uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $image = time() . '_' . uniqid() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $image;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (in_array($imageFileType, $allowedTypes)) {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
        }

        if ($product->create($name, $description, $price, $image, $category_id, $stock, $status)) {
            $_SESSION['success_message'] = "Product added successfully!";
            header("Location: admin-dashboard.php?success=1#products");
            exit;
        } else {
            $_SESSION['error_message'] = "Error adding product.";
            header("Location: admin-dashboard.php?error=1#products");
            exit;
        }
    }

    // Handle update product
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
        $id = intval($_POST['edit_id']);
        $name = trim(htmlspecialchars($_POST['edit_name']));
        $description = trim(htmlspecialchars($_POST['edit_description']));
        $price = floatval($_POST['edit_price']);
        $stock = intval($_POST['edit_stock']);
        $category_id = intval($_POST['edit_category']);
        
        $current_product = $product->getById($id);
        
        // Status logic
        $status = isset($_POST['edit_status']) ? $_POST['edit_status'] : ($stock > 0 ? 'active' : 'inactive');
        
        $current_image = $_POST['current_image_hidden'];
        $image = $current_image;

        // Image upload
        if (!empty($_FILES['edit_image']['name']) && $_FILES['edit_image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../../../public/uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $image = time() . '_' . uniqid() . '_' . basename($_FILES['edit_image']['name']);
            $targetFile = $targetDir . $image;
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            if (in_array($imageFileType, $allowedTypes)) {
                move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetFile);
            }
        }

        if ($product->update($id, $name, $description, $price, $image, $category_id, $stock, $status, $current_product['sizes'])) {
            $_SESSION['success_message'] = "Product updated successfully!";
            header("Location: admin-dashboard.php?updated=1#products");
            exit;
        } else {
            $_SESSION['error_message'] = "Error updating product. Please try again.";
            header("Location: admin-dashboard.php?error=1#products");
            exit;
        }
    }

    // Handle manual status override
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_status_update'])) {
        $product_id = intval($_POST['product_id']);
        $new_status = $_POST['status'];
        
        if ($product->updateStatusManually($product_id, $new_status)) {
            $_SESSION['success_message'] = "Product status updated successfully!";
            header("Location: admin-dashboard.php?status_updated=1#products");
        } else {
            $_SESSION['error_message'] = "Error updating product status.";
            header("Location: admin-dashboard.php?error=1#products");
        }
        exit;
    }

    // Handle bulk stock update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
        $product_id = intval($_POST['product_id']);
        $new_stock = intval($_POST['new_stock']);
        
        $current_product = $product->getById($product_id);
        if ($current_product) {
            $new_status = ($new_stock > 0) ? 'active' : 'inactive';
            if ($product->update(
                $product_id,
                $current_product['name'],
                $current_product['description'],
                $current_product['price'],
                $current_product['image'],
                $current_product['category_id'],
                $new_stock,
                $new_status,
                $current_product['sizes']
            )) {
                $_SESSION['success_message'] = "Stock updated successfully!";
                header("Location: admin-dashboard.php?stock_updated=1#products");
            } else {
                $_SESSION['error_message'] = "Error updating stock.";
                header("Location: admin-dashboard.php?error=1#products");
            }
            exit;
        }
    }
}
?>
