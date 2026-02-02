<?php
// THE LINE ABOVE MUST BE THE VERY FIRST LINE OF THE FILE.
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();

// Verify this path is correct based on your folder structure
require_once __DIR__ . '/../../../../config/dbcon.php';

ob_clean(); 

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$backupDir = __DIR__ . '/backups/';
if (!is_dir($backupDir)) { mkdir($backupDir, 0755, true); }

$action = $_GET['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // --- FEATURE 1: AUTO-CLEANUP (Keep only last 5 files) ---
    // This runs every time the script is accessed to keep your folder clean
    $maxFiles = 5;
    $existingFiles = glob($backupDir . "*.sql");
    if (count($existingFiles) > $maxFiles) {
        // Sort files by date (oldest first)
        usort($existingFiles, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        // Delete the oldest files until only 5 remain
        for ($i = 0; $i < count($existingFiles) - $maxFiles; $i++) {
            unlink($existingFiles[$i]);
        }
    }

    // --- FEATURE 2: LIST BACKUPS (For the Dashboard Table) ---
    if ($action === 'list') {
        $files = array_diff(scandir($backupDir), array('.', '..'));
        $backupList = [];
        foreach ($files as $file) {
            $path = $backupDir . $file;
            if (is_file($path)) {
                $backupList[] = [
                    'name' => $file,
                    'date' => date("M d, Y h:i A", filemtime($path)),
                    'size' => round(filesize($path) / 1024, 2) . ' KB'
                ];
            }
        }
        // Sort newest first
        usort($backupList, function($a, $b) { 
            return strtotime($b['date']) - strtotime($a['date']); 
        });
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'backups' => $backupList]);
        exit;
    }

    // --- EXISTING FEATURE: GENERATE BACKUP ---
    if ($action === 'generate') {
        $tables = [];
        $query = $db->query("SHOW TABLES");
        while ($row = $query->fetch(PDO::FETCH_NUM)) { $tables[] = $row[0]; }

        $sqlScript = "SET FOREIGN_KEY_CHECKS=0;\n";
        foreach ($tables as $table) {
            $row = $db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
            $sqlScript .= "\n\n" . $row[1] . ";\n\n";
            $query = $db->query("SELECT * FROM $table");
            $columnCount = $query->columnCount();
            while ($row = $query->fetch(PDO::FETCH_NUM)) {
                $sqlScript .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $columnCount; $j++) {
                    if (isset($row[$j])) {
                        $sqlScript .= '"' . addslashes($row[$j]) . '"';
                    } else {
                        $sqlScript .= 'NULL';
                    }
                    if ($j < ($columnCount - 1)) $sqlScript .= ',';
                }
                $sqlScript .= ");\n";
            }
        }
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($backupDir . $filename, $sqlScript);

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'filename' => $filename]);
        exit;
    }

    // --- EXISTING FEATURE: DOWNLOAD FILE ---
    if ($action === 'download' && isset($_GET['file'])) {
        $file = basename($_GET['file']);
        $filePath = $backupDir . $file;
        if (file_exists($filePath)) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Pragma: public');
            readfile($filePath);
            exit;
        }
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

if ($action === 'list') {
    $backups = [];
    $files = array_diff(scandir($backupDir), array('.', '..'));
    rsort($files); // Newest first

    foreach ($files as $file) {
        $backups[] = [
            'name' => $file,
            'size' => round(filesize($backupDir . $file) / 1024, 2) . ' KB',
            'date' => date("F d, Y h:i A", filemtime($backupDir . $file))
        ];
    }

    // NEW: Fetch the name of the admin who did the last backup
    $logQuery = $db->prepare("
        SELECT u.username 
        FROM activity_logs l 
        JOIN users u ON l.user_id = u.user_id 
        WHERE l.action = 'DATABASE_BACKUP_REQUEST' 
        ORDER BY l.timestamp DESC LIMIT 1
    ");
    $logQuery->execute();
    $lastAdmin = $logQuery->fetchColumn() ?: 'System';

    echo json_encode([
        'status' => 'success', 
        'backups' => $backups, 
        'last_admin' => $lastAdmin // Send this to JavaScript
    ]);
    exit;
}