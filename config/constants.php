<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple XAMPP-friendly constants
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// For XAMPP/local development, use simple paths
if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
    define('BASE_URL', $protocol . '://' . $host . '/e-commerce');
    define('ASSETS_URL', BASE_URL . '/public/assets');
} else {
    // For production, use dynamic detection
    $project_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', realpath(__DIR__ . '/..')));
    define('BASE_URL', $protocol . '://' . $host . $project_path);
    define('ASSETS_URL', BASE_URL . '/public/assets');
}
?>