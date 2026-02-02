<?php
error_log("=== FORM DATA RECEIVED ===");
foreach ($_POST as $key => $value) {
    error_log("$key: $value");
}
error_log("===========================");
// Handle all user-related actions
function handleUserActions($user) {
    
    // DEBUG: Log all POST data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("=== POST REQUEST DEBUG ===");
        error_log("All POST keys: " . implode(', ', array_keys($_POST)));
        foreach ($_POST as $key => $value) {
            error_log("POST[$key] = '$value'");
        }
        error_log("=== END POST DEBUG ===");
    }

    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $userId = intval($_POST['user_id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        $result = $user->updateProfile($userId, $username, $email, $phone, $address);
        
        if ($result === true) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;
            $_SESSION['address'] = $address;
            $_SESSION['success_message'] = "Profile updated successfully!";
        } elseif ($result === 'username_taken') {
            $_SESSION['error_message'] = "Username is already taken. Please choose a different one.";
        } elseif ($result === 'email_taken') {
            $_SESSION['error_message'] = "Email is already in use. Please use a different email.";
        } else {
            $_SESSION['error_message'] = "Failed to update profile. Please try again.";
        }
        
        $redirect_to = ($_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';

// Then update all your headers:
header("Location:  . $redirect_to");
exit();
    }

    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $userId = intval($_POST['user_id']);
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error_message'] = "New passwords do not match.";
             $redirect_to = ($_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';

// Then update all your headers:
header("Location:  . $redirect_to");
exit();
        }
        
        if (strlen($newPassword) < 6) {
            $_SESSION['error_message'] = "Password must be at least 6 characters long.";
             $redirect_to = ($_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';

// Then update all your headers:
header("Location:  . $redirect_to");
exit();
        }
        
        $result = $user->changePassword($userId, $currentPassword, $newPassword);
        
        if ($result === true) {
            $_SESSION['success_message'] = "Password changed successfully!";
        } elseif ($result === 'incorrect_current_password') {
            $_SESSION['error_message'] = "Current password is incorrect.";
        } elseif ($result === 'user_not_found') {
            $_SESSION['error_message'] = "User not found.";
        } else {
            $_SESSION['error_message'] = "Failed to change password. Please try again.";
        }
        
         $redirect_to = ($_SESSION['role'] === 'admin') ? 'admin-dashboard.php' : 'staff-dashboard.php';

            // Then update all your headers:
            header("Location:  . $redirect_to");
            exit();
    }


    
}

?>