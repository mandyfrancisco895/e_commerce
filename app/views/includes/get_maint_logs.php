<?php
require_once __DIR__ . '/../../config/dbcon.php';
header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

try {
    // Updated query to select 'message' column
    $stmt = $db->prepare("SELECT operation, admin_user, details, message, status, created_at FROM system_audit_logs ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logs);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>