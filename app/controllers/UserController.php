<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../config/dbcon.php';



session_start();

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);


// -------------------- Update Profile --------------------
if (isset($_GET['action']) && $_GET['action'] === 'updateProfile') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $id       = $_SESSION['user_id'];
            $username = $_POST['username'];
            $email    = $_POST['email'];
            $phone    = $_POST['phone'];
            $address  = $_POST['address'];

            // Handle profile picture
            $profile_pic = $_POST['old_profile_pic'] ?? null;

            if (!empty($_FILES['profile_pic']['name'])) {
                $uploadDir  = __DIR__ . '/../../public/uploads/';
                $fileName   = time() . '_' . basename($_FILES['profile_pic']['name']);
                $targetPath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
                    $profile_pic = $fileName;
                } else {
                    $_SESSION['error'] = "Failed to upload profile picture.";
                    header("Location: ../../app/views/pages/shop.php");
                    exit;
                }
            }

            // Update in DB
            $success = $userModel->updateProfile($id, $username, $email, $phone, $address, $profile_pic);

            if ($success) {
                $_SESSION['success'] = "Profile updated successfully!";
            } else {
                $_SESSION['error'] = "Profile update failed!";
            }

            header("Location: ../../app/views/pages/shop.php");
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("Location: ../../app/views/pages/shop.php");
            exit;
        }
    }
}

// -------------------- Change Password --------------------
if (isset($_GET['action']) && $_GET['action'] === 'changePassword') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $id              = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'];
            $newPassword     = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Check if new passwords match
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "New passwords do not match.";
                header("Location: ../../app/views/pages/shop.php?modal=changePassword");
                exit;
            }

            // Call the model method
            $result = $userModel->changePassword($id, $currentPassword, $newPassword);

            if ($result) {
                $_SESSION['success'] = "Password updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update password.";
            }

            // Always redirect back with modal open
            header("Location: ../../app/views/pages/shop.php?modal=changePassword");
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header("Location: ../../app/views/pages/shop.php?modal=changePassword");
            exit;
        }
    }
}
