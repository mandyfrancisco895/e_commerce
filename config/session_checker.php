<?php
session_start();
require_once __DIR__ . '/dbcon.php'; // ✅ Adjust path if needed

// ✅ Create database connection
$database = new Database();
$pdo = $database->getConnection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../app/views/auth/login.php");
    exit();
}

// Fetch latest user data from DB
$userId = $_SESSION['user_id'];
$query = "SELECT status FROM users WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ Check user status
    if ($user['status'] === 'Blocked') {
        session_unset();
        session_destroy();
        header("Location: /e-commerce/app/views/auth/login.php?error=blocked");
        exit();
    }

    if ($user['status'] === 'Inactive') {
        session_unset();
        session_destroy();
        header("Location: ../../app/views/auth/login.php?error=inactive");
        exit();
    }

    if ($user['status'] === 'Deactivated') {
        session_unset();
        session_destroy();
        header("Location: /e-commerce/app/views/auth/login.php?error=deactivated");
        exit();
    }
} else {
    // User no longer exists
    session_unset();
    session_destroy();
    header("Location: ../../app/views/auth/login.php?error=invalid_session");
    exit();
}
