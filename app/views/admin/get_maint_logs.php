<?php
require_once __DIR__ . '/../../../config/dbcon.php';
$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->prepare("SELECT * FROM system_audit_logs ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($logs): 
        foreach ($logs as $log): ?>
        <tr>
            <td class="ps-4">
                <div class="d-flex align-items-center">
                    <div class="bg-light text-primary me-3 px-2 py-1 rounded">
                        <i class="fas fa-tools small"></i>
                    </div>
                    <span class="fw-bold small"><?= htmlspecialchars($log['operation']) ?></span>
                </div>
            </td>
            <td class="small"><?= htmlspecialchars($log['admin_user']) ?></td>
            <td>
                <span class="text-muted small text-truncate d-inline-block" style="max-width: 250px;">
                    <?= htmlspecialchars($log['details']) ?>
                </span>
            </td>
            <td class="small text-muted">
                <?= date('M d, g:i A', strtotime($log['created_at'])) ?>
            </td>
            <td class="text-end pe-4">
                <span class="badge bg-success-subtle text-success border border-success px-3">
                    <?= htmlspecialchars($log['status']) ?>
                </span>
            </td>
        </tr>
    <?php endforeach; 
    else: ?>
        <tr><td colspan="5" class="text-center py-4 text-muted small">No recent activity found.</td></tr>
    <?php endif; 
} catch (PDOException $e) { echo "Error"; }
?>