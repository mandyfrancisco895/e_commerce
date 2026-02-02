<?php
session_start();
// Adjust this path to point correctly to your dbcon.php
require_once __DIR__ . '/../../../../config/dbcon.php';

header('Content-Type: application/json');

// Security Check: Only allow logged-in Admins to change settings
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// ... existing code inside update_settings.php ...

// ... existing code inside update_settings.php ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
   try {
        $db->beginTransaction();
    
        // Loop through the submitted form data
        foreach ($_POST as $key => $value) {
            // Skip logging logic keys if they exist in POST
            if($key === 'action') continue; 

            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) 
                                 VALUES (:key, :value) 
                                 ON DUPLICATE KEY UPDATE setting_value = :value");
            
            // Allow saving empty strings
            $stmt->execute([
                ':key' => $key,
                ':value' => htmlspecialchars(trim($value))
            ]);
        }

        // --- UPDATED LOGGING CODE ---
        $adminUser = $_SESSION['username'] ?? 'Admin';
        
        // Handle empty values for the log so it looks clean
        $recoveryLog = !empty($_POST['recovery_time']) ? $_POST['recovery_time'] : 'Not Specified';
        $statusLog   = (isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] == '1') ? 'ON' : 'OFF';

        // Custom function to insert into system_audit_logs
        logSystemAudit(
            $db,
            'System Status Update',
            $adminUser,
            "Maintenance: $statusLog | Recovery: $recoveryLog"
        );
        // ----------------------------------

        $db->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Function definition (if not already defined elsewhere)
function logSystemAudit($db, $operation, $adminUser, $details) {
    $sql = "INSERT INTO system_audit_logs (operation, admin_user, details, status, created_at) 
            VALUES (:op, :user, :details, 'Completed', NOW())";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':op' => $operation,
        ':user' => $adminUser,
        ':details' => $details
    ]);
}
