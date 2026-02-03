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

// ===================================================================
// SECURITY: Only admin users can perform backup operations
// ===================================================================
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => 'Access denied. Only administrators can perform database backups.'
    ]);
    exit;
}

$backupDir = __DIR__ . '/backups/';
if (!is_dir($backupDir)) { mkdir($backupDir, 0755, true); }

$action = $_GET['action'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();

    // ===================================================================
    // ACTION: LIST BACKUPS (For the Dashboard Table)
    // ===================================================================
    if ($action === 'list') {
        // Fetch backup logs from database with admin info
        $stmt = $db->prepare("
            SELECT 
                bl.id,
                bl.filename,
                bl.created_by_username,
                bl.created_by_role,
                bl.file_size,
                bl.backup_path,
                bl.status,
                bl.created_at,
                u.profile_pic
            FROM backup_logs bl
            LEFT JOIN users u ON bl.created_by_user_id = u.id
            WHERE bl.status = 'success'
            ORDER BY bl.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $dbBackups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $backupList = [];
        foreach ($dbBackups as $backup) {
            // Verify physical file still exists
            $filePath = $backup['backup_path'] ?? ($backupDir . $backup['filename']);
            $fileExists = file_exists($filePath);
            
            if ($fileExists) {
                $backupList[] = [
                    'id' => $backup['id'],
                    'filename' => $backup['filename'],
                    'created_by' => $backup['created_by_username'],
                    'role' => $backup['created_by_role'],
                    'size' => formatBytes($backup['file_size'] ?? filesize($filePath)),
                    'date' => date('M d, Y h:i A', strtotime($backup['created_at'])),
                    'timestamp' => $backup['created_at'],
                    'status' => $backup['status'],
                    'profile_pic' => $backup['profile_pic']
                ];
            }
        }

        // Get last admin who created backup
        $lastAdmin = !empty($dbBackups) ? $dbBackups[0]['created_by_username'] : 'System';

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'backups' => $backupList,
            'last_admin' => $lastAdmin
        ]);
        exit;
    }

    // ===================================================================
    // ACTION: GENERATE BACKUP
    // ===================================================================
    if ($action === 'generate') {
        $tables = [];
        $query = $db->query("SHOW TABLES");
        while ($row = $query->fetch(PDO::FETCH_NUM)) { 
            $tables[] = $row[0]; 
        }

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
        
        $filename = 'E-commerce_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $backupDir . $filename;
        
        // Write backup file
        file_put_contents($filePath, $sqlScript);
        $fileSize = filesize($filePath);

        // ===================================================================
        // LOG BACKUP IN DATABASE with ROLE INFO
        // ===================================================================
        try {
            $stmt = $db->prepare("
                INSERT INTO backup_logs 
                (filename, created_by_user_id, created_by_username, created_by_role, 
                 file_size, backup_path, ip_address, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'success', NOW())
            ");
            
            $userId = $_SESSION['user_id'];
            $username = $_SESSION['username'] ?? 'Unknown';
            $userRole = $_SESSION['role'];
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            
            $stmt->execute([
                $filename,
                $userId,
                $username,
                $userRole,
                $fileSize,
                $filePath,
                $ipAddress
            ]);

            // Enforce retention policy (keep only last 5 backups)
            enforceRetentionPolicy($db, $backupDir);

        } catch (PDOException $e) {
            // Log error but don't fail the backup
            error_log("Failed to log backup in database: " . $e->getMessage());
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'filename' => $filename,
            'file_size' => formatBytes($fileSize)
        ]);
        exit;
    }

    // ===================================================================
    // ACTION: DOWNLOAD BACKUP
    // ===================================================================
    if ($action === 'download' && isset($_GET['file'])) {
        $file = basename($_GET['file']);
        
        // Security: Validate filename format
        if (!preg_match('/^E-commerce_[\d\-_]+\.sql$/', $file)) {
            http_response_code(400);
            die('Invalid filename format');
        }
        
        $filePath = $backupDir . $file;
        
        if (file_exists($filePath)) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Pragma: public');
            header('Cache-Control: no-cache');
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Backup file not found'
            ]);
            exit;
        }
    }

    // ===================================================================
    // ACTION: DELETE BACKUP
    // ===================================================================
    if ($action === 'delete') {
        $filename = $_POST['filename'] ?? '';
        
        // Security: Validate filename
        if (!preg_match('/^E-commerce_[\d\-_]+\.sql$/', $filename)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid filename']);
            exit;
        }

        try {
            $filePath = $backupDir . $filename;

            // Delete physical file
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Mark as deleted in database
            $stmt = $db->prepare("UPDATE backup_logs SET status = 'deleted' WHERE filename = ?");
            $stmt->execute([$filename]);

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Backup deleted successfully'
            ]);
            exit;

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}

// ===================================================================
// HELPER FUNCTIONS
// ===================================================================

/**
 * Enforce retention policy - keep only last 5 backups
 * Deletes both physical files and updates database records
 */
function enforceRetentionPolicy($db, $backupDir) {
    try {
        $maxBackups = 5;
        
        // Get all successful backups ordered by date
        $stmt = $db->query("
            SELECT id, filename, backup_path 
            FROM backup_logs 
            WHERE status = 'success'
            ORDER BY created_at DESC
        ");
        $allBackups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If we have more than 5, delete the oldest ones
        if (count($allBackups) > $maxBackups) {
            $backupsToDelete = array_slice($allBackups, $maxBackups);

            foreach ($backupsToDelete as $backup) {
                // Delete physical file
                $filePath = $backup['backup_path'] ?? ($backupDir . $backup['filename']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Mark as deleted in database
                $deleteStmt = $db->prepare("UPDATE backup_logs SET status = 'deleted' WHERE id = ?");
                $deleteStmt->execute([$backup['id']]);
            }
        }

        // Also clean up orphaned files (files that exist but not in database)
        $existingFiles = glob($backupDir . "E-commerce_*.sql");
        if (count($existingFiles) > $maxBackups) {
            // Sort by modification time (oldest first)
            usort($existingFiles, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Delete oldest files
            $filesToDelete = count($existingFiles) - $maxBackups;
            for ($i = 0; $i < $filesToDelete; $i++) {
                if (file_exists($existingFiles[$i])) {
                    unlink($existingFiles[$i]);
                }
            }
        }

    } catch (Exception $e) {
        // Log error but don't fail the backup operation
        error_log("Retention policy enforcement failed: " . $e->getMessage());
    }
}

/**
 * Format bytes to human-readable size
 * @param int $bytes Size in bytes
 * @param int $precision Decimal places
 * @return string Formatted size
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}   