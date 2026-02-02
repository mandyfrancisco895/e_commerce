<?php
// src/controllers/BackupController.php

class BackupController {
    private $db;
    private $backupDir;

    public function __construct($db) {
        $this->db = $db;
        $this->backupDir = __DIR__ . '/../../storage/backups/';
    }

    // RBAC: Only Admin and Technical roles allowed
    private function checkAccess() {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'technical'])) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
            exit;
        }
    }

    public function generateBackup() {
        $this->checkAccess();

        $filename = 'backup_' . date('Ymd_His') . '.sql';
        $filePath = $this->backupDir . $filename;

        // Command for XAMPP (Localhost). 
        // Note: 'root' and '' are used based on your dbcon.php
        $command = "mysqldump --user=root --host=127.0.0.1 \"e-commerce_app\" > " . escapeshellarg($filePath);
        
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->logActivity("Backup Generated", $filename);
            return ['status' => 'success', 'filename' => $filename];
        } else {
            return ['status' => 'error', 'message' => 'System error: Check if mysqldump is installed.'];
        }
    }

    public function downloadBackup($filename) {
        $this->checkAccess();
        $filePath = $this->backupDir . basename($filename);

        if (file_exists($filePath)) {
            $this->logActivity("Backup Downloaded", $filename);
            header('Content-Description: File Transfer');
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            readfile($filePath);
            exit;
        }
    }

    private function logActivity($action, $details) {
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, role, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['role'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR']
        ]);
    }
}