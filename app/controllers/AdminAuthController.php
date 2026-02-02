<?php
// Set PHP timezone to Asia/Manila (UTC+8)
date_default_timezone_set('Asia/Manila');

session_start();
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../config/dbcon.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // ========================= ADMIN LOGIN WITH ATTEMPT LIMITING & CAPTCHA ========================= //
    if ($action === 'admin_login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $selectedRole = $_POST['role'] ?? ''; // <--- GET ROLE FROM DROPDOWN
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Security settings
        $MAX_ATTEMPTS = 5;
        $CAPTCHA_THRESHOLD = 3;
        $LOCKOUT_MINUTES = 15;
        $ATTEMPT_WINDOW = 30;
        
        // ============ CHECK IF ACCOUNT IS LOCKED ============
        $lockStatus = $user->isAccountLocked($email);
        if ($lockStatus['locked']) {
            $lockedUntil = new DateTime($lockStatus['locked_until']);
            $now = new DateTime();
            $remainingMinutes = ceil(($lockedUntil->getTimestamp() - $now->getTimestamp()) / 60);
            
            header("Location: ../../app/views/login.php?error=account_locked&minutes=$remainingMinutes&email=" . urlencode($email));
            exit;
        }
        
        // ============ GET CURRENT ATTEMPT COUNT ============
        $attemptCount = $user->getFailedAttemptCount($email, $ATTEMPT_WINDOW);
        
        // ============ VERIFY TURNSTILE CAPTCHA IF NEEDED ============
        if ($attemptCount >= $CAPTCHA_THRESHOLD) {
            $turnstileToken = $_POST['cf-turnstile-response'] ?? '';
            if (empty($turnstileToken) || !verifyTurnstileToken($turnstileToken, $ipAddress)) {
                header("Location: ../../app/views/login.php?error=captcha_failed&attempts=$attemptCount&email=" . urlencode($email));
                exit;
            }
        }
        
        // ============ ATTEMPT LOGIN ============
        $loginUser = $user->login($email, $password);
        
        // Handle blocked/inactive status (Existing checks)
        if ($loginUser === 'blocked') { header("Location: ../../app/views/login.php?error=blocked"); exit; }
        if ($loginUser === 'inactive') { header("Location: ../../app/views/login.php?error=inactive"); exit; }
        if ($loginUser === 'deactivated') { header("Location: ../../app/views/login.php?error=deactivated"); exit; }
        
        // ============ CHECK LOGIN RESULT ============
        if (!$loginUser || !is_array($loginUser)) {
            $user->recordFailedAttempt($email, $ipAddress, $userAgent);
            $attemptCount++;
            
            if ($attemptCount >= $MAX_ATTEMPTS) {
                $user->lockAccount($email, $LOCKOUT_MINUTES);
                header("Location: ../../app/views/login.php?error=max_attempts&minutes=$LOCKOUT_MINUTES&email=" . urlencode($email));
                exit;
            }
            
            $remainingAttempts = $MAX_ATTEMPTS - $attemptCount;
            header("Location: ../../app/views/login.php?error=invalid_credentials&remaining=$remainingAttempts&email=" . urlencode($email));
            exit;
        }

        // ============ 🛡️ CORE LOGIC: ROLE VALIDATION ============
        $actualRole = strtolower($loginUser['role']); // Role from Database
        
        if ($selectedRole !== $actualRole) {
            // Even if password is correct, mismatch fails the login
           error_log("Security Alert: Role Mismatch for $email. Selected: $selectedRole, Actual: $actualRole");
            header("Location: ../../app/views/login.php?error=invalid_credentials&email=" . urlencode($email));
            exit;
        }
        
        // ============ LOGIN SUCCESS ============
        $user->clearFailedAttempts($email);
        
        // Set session variables
        $_SESSION['user_id'] = $loginUser['id'];
        $_SESSION['username'] = $loginUser['username'];
        $_SESSION['email'] = $loginUser['email'];
        $_SESSION['role'] = $actualRole; // Strictly use DB role
        
        // ============ 🎯 ROLE-BASED REDIRECTION ============
        if ($actualRole === 'admin') {
            header("Location: /e-commerce/app/views/admin/admin-dashboard.php?success=login_success");
        } elseif ($actualRole === 'staff') {
            header("Location: /e-commerce/app/views/staff/staff-dashboard.php?success=login_success");
        } else {
            header("Location: ../../app/views/login.php?error=unauthorized");
        }
        exit;
    }

    // ========================= ADMIN LOGOUT ========================= //
    if ($action === 'admin_logout') {
        // Verify it's an admin before logging out
        $allowed_roles = ['admin', 'staff'];
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
            header("Location: ../../app/views/login.php?error=unauthorized");
            exit;
        }
        
        error_log("Admin logout: " . ($_SESSION['email'] ?? 'unknown'));
        
        // Clear all session data
        session_unset();
        session_destroy();
        
        // Start a new session for the logout message
        session_start();
        session_regenerate_id(true);
        
        header("Location: ../../app/views/login.php?success=logged_out");
        exit;
    }
}

// ========================= TURNSTILE VERIFICATION FUNCTION ========================= //
function verifyTurnstileToken($token, $remoteIp = '') {
    $turnstileConfig = require __DIR__ . '/../../config/turnstile.php';
    $secretKey = $turnstileConfig['secret_key'];
    
    error_log("=== ADMIN TURNSTILE VERIFICATION ===");
    error_log("Token: " . substr($token, 0, 50) . "...");
    error_log("Remote IP: $remoteIp");
    
    $data = [
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $remoteIp
    ];
    
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $turnstileConfig['timeout'] ?? 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log("Admin Turnstile cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Admin Turnstile API returned HTTP $httpCode");
        return false;
    }
    
    $result = json_decode($response, true);
    error_log("Admin Turnstile Response: " . print_r($result, true));
    
    if (isset($result['success'])) {
        error_log("Admin Turnstile verification: " . ($result['success'] ? "SUCCESS" : "FAILED"));
        if (!$result['success'] && isset($result['error-codes'])) {
            error_log("Admin Turnstile errors: " . implode(', ', $result['error-codes']));
        }
    }
    
    return isset($result['success']) && $result['success'] === true;
}

?>