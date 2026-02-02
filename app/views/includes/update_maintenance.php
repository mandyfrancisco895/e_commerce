<?php
require_once __DIR__ . '/../../config/dbcon.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    
    $adminName = $_SESSION['admin_name'] ?? 'System Admin'; 
    $maintMode = isset($_POST['maintenance_mode']) ? '1' : '0';
    $message = trim($_POST['maint_message'] ?? '');
    $time = $_POST['recovery_time'] ?? '';

    // LOGIC: Prevent update if fields are blank
    if (empty($message) || empty($time)) {
        echo json_encode(['success' => false, 'error' => 'Fields cannot be empty']);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Update Settings
        $stmt = $db->prepare("UPDATE system_settings SET setting_value = :val WHERE setting_key = :key");
        $stmt->execute([':val' => $maintMode, ':key' => 'maintenance_mode']);
        $stmt->execute([':val' => $message, ':key' => 'maint_message']);
        $stmt->execute([':val' => $time, ':key' => 'recovery_time']);

        // 2. Insert into System Logs (Using your system_logs table)
        $logDetails = "Maintenance " . ($maintMode == '1' ? 'Enabled' : 'Disabled') . ". Message: $message";
        $logStmt = $db->prepare("INSERT INTO system_logs (action_type, action_details, performed_by) VALUES ('SYSTEM_CONTROL', :details, :admin)");
        $logStmt->execute([':details' => $logDetails, ':admin' => $adminName]);

        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}