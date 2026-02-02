<?php
// Set PHP timezone to Asia/Manila (UTC+8)
date_default_timezone_set('Asia/Manila');

session_start();
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/otp.php';
require_once __DIR__ . '/../../config/dbcon.php';

require_once __DIR__ . '/../../libraries/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../libraries/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../../libraries/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$otp = new OTP($db);

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // ========================= REGISTER WITH OTP VERIFICATION ========================= //
    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Check if email already exists
        $existingUser = $user->getUserByEmail($email);
        if ($existingUser) {
            header("Location: ../../app/views/auth/login.php?error=email_taken");
            exit;
        }

        // Check if username already exists
        $existingUsername = $user->getUserByUsername($username);
        if ($existingUsername) {
            header("Location: ../../app/views/auth/login.php?error=username_taken");
            exit;
        }

        // Generate OTP for email verification
        $otpCode = rand(100000, 999999);
        $expiration = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Store user data in session for after OTP verification
        $_SESSION['pending_registration'] = [
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];

        // Save OTP to database
        $otpSaved = $otp->saveOTPForEmail($email, $otpCode, $expiration);

        if (!$otpSaved) {
            header("Location: ../../app/views/auth/login.php?error=otp_failed");
            exit;
        }

        // Send OTP via email
        $emailSent = sendOTPEmail($email, $otpCode, 'registration');

        if (!$emailSent) {
            header("Location: ../../app/views/auth/login.php?error=email_failed");
            exit;
        }

        header("Location: ../../app/views/auth/login.php?show_otp=1&email=" . urlencode($email) . "&action=verify_signup");
        exit;
    }

    // ========================= VERIFY SIGNUP OTP ========================= //
    if ($action === 'verify_signup_otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $enteredOTP = trim($_POST['otp'] ?? '');
        $email = $_SESSION['pending_registration']['email'] ?? null;

        error_log("=== VERIFY SIGNUP OTP ===");
        error_log("Entered OTP: $enteredOTP");
        error_log("Email from session: $email");

        if (!$email || empty($enteredOTP)) {
            error_log("Missing email or OTP");
            header("Location: ../../app/views/auth/login.php?error=session_expired");
            exit;
        }

        // Verify OTP for email
        $otpData = $otp->verifyOTPForEmail($email, $enteredOTP);

        if (!$otpData) {
            error_log("OTP verification failed for email: $email, OTP: $enteredOTP");
            header("Location: ../../app/views/auth/login.php?show_otp=1&error=invalid_otp&email=" . urlencode($email) . "&action=verify_signup");
            exit;
        }

        error_log("OTP verification successful!");

        // Mark OTP as used
        $otp->markRegistrationOTPUsed($otpData['id']);

        // Get the stored registration data
        $userData = $_SESSION['pending_registration'];

        // Create the user account (as regular user, not admin)
        $registrationResult = $user->register($userData['username'], $userData['email'], $userData['password'], 'user');

        if ($registrationResult === true) {
            error_log("User registration successful for: " . $userData['email']);
            
            // Clear pending registration data
            unset($_SESSION['pending_registration']);
            
            header("Location: ../../app/views/auth/login.php?success=email_verified&message=Email verified successfully! Please login with your new account.");
            exit;
        }

        error_log("User registration failed for: " . $userData['email']);
        header("Location: ../../app/views/auth/login.php?error=register_failed");
        exit;
    }

    // ========================= USER LOGIN (NON-ADMIN) ========================= //
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
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
            
            error_log("User login attempt on locked account: $email (locked for $remainingMinutes more minutes)");
            header("Location: ../../app/views/auth/login.php?error=account_locked&minutes=$remainingMinutes&email=" . urlencode($email));
            exit;
        }
        
        // ============ GET CURRENT ATTEMPT COUNT ============
        $attemptCount = $user->getFailedAttemptCount($email, $ATTEMPT_WINDOW);
        error_log("User login attempt for $email - Current failed attempts: $attemptCount");
        
        // ============ VERIFY TURNSTILE CAPTCHA IF NEEDED ============
        if ($attemptCount >= $CAPTCHA_THRESHOLD) {
            $turnstileToken = $_POST['cf-turnstile-response'] ?? '';
            
            if (empty($turnstileToken)) {
                error_log("CAPTCHA required but not provided. Attempt count: $attemptCount");
                header("Location: ../../app/views/auth/login.php?error=captcha_required&attempts=$attemptCount&email=" . urlencode($email));
                exit;
            }
            
            // Verify Turnstile token
            $turnstileVerified = verifyTurnstileToken($turnstileToken, $ipAddress);
            
            if (!$turnstileVerified) {
                error_log("CAPTCHA verification failed for email: $email");
                header("Location: ../../app/views/auth/login.php?error=captcha_failed&attempts=$attemptCount&email=" . urlencode($email));
                exit;
            }
            
            error_log("CAPTCHA verification successful for: $email");
        }
        
        // ============ ATTEMPT LOGIN ============
        $loginUser = $user->login($email, $password);
        
        // Handle blocked/inactive/deactivated status
        if ($loginUser === 'blocked') {
            header("Location: ../../app/views/auth/login.php?error=blocked");
            exit;
        }
        if ($loginUser === 'inactive') {
            header("Location: ../../app/views/auth/login.php?error=inactive");
            exit;
        }
        if ($loginUser === 'deactivated') {
            header("Location: ../../app/views/auth/login.php?error=deactivated");
            exit;
        }
        
        // ============ CHECK LOGIN RESULT ============
        if (!$loginUser || !is_array($loginUser)) {
            // Failed login - record attempt
            $user->recordFailedAttempt($email, $ipAddress, $userAgent);
            $attemptCount++;
            
            error_log("Failed user login attempt #$attemptCount for: $email from IP: $ipAddress");
            
            // Lock account if max attempts reached
            if ($attemptCount >= $MAX_ATTEMPTS) {
                $user->lockAccount($email, $LOCKOUT_MINUTES);
                error_log("User account locked for $LOCKOUT_MINUTES minutes: $email");
                header("Location: ../../app/views/auth/login.php?error=max_attempts&minutes=$LOCKOUT_MINUTES&email=" . urlencode($email));
                exit;
            }
            
            $remainingAttempts = $MAX_ATTEMPTS - $attemptCount;
            header("Location: ../../app/views/auth/login.php?error=invalid_credentials&attempts=$attemptCount&remaining=$remainingAttempts&email=" . urlencode($email));
            exit;
        }
        
        // ============ CHECK IF USER IS NOT AN ADMIN ============
        // Redirect admins to use admin login
        if ($loginUser['role'] === 'admin') {
            error_log("Admin user attempted to use regular login: $email");
            header("Location: ../../app/views/auth/login.php?error=use_admin_login&email=" . urlencode($email));
            exit;
        }
        
        // ============ USER LOGIN SUCCESS ============
        $user->clearFailedAttempts($email);
        
        // Set session variables
        $_SESSION['user_id'] = $loginUser['id'];
        $_SESSION['username'] = $loginUser['username'];
        $_SESSION['email'] = $loginUser['email'];
        $_SESSION['status'] = $loginUser['status'];
        $_SESSION['role'] = $loginUser['role'];
        
        error_log("Successful USER login for: $email");
        
        // Redirect to user dashboard/shop
        header("Location: ../../app/views/pages/shop.php?success=login_success");
        exit;
    }

    // ========================= RESEND OTP (SIGNUP ONLY) ========================= //
    if ($action === 'resend_otp') {
        $actionType = $_GET['type'] ?? 'signup';
        
        if ($actionType === 'signup') {
            $email = $_SESSION['pending_registration']['email'] ?? null;
            if (!$email) {
                header("Location: ../../app/views/auth/login.php?error=session_expired");
                exit;
            }

            // Generate new OTP
            $otpCode = rand(100000, 999999);
            $expiration = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // Save OTP
            $otpSaved = $otp->saveOTPForEmail($email, $otpCode, $expiration);

            if (!$otpSaved) {
                header("Location: ../../app/views/auth/login.php?show_otp=1&error=otp_failed&email=" . urlencode($email) . "&action=verify_signup");
                exit;
            }

            // Send OTP
            $emailSent = sendOTPEmail($email, $otpCode, 'registration');

            if (!$emailSent) {
                header("Location: ../../app/views/auth/login.php?show_otp=1&error=email_failed&email=" . urlencode($email) . "&action=verify_signup");
                exit;
            }

            header("Location: ../../app/views/auth/login.php?show_otp=1&success=otp_resent&email=" . urlencode($email) . "&action=verify_signup");
            exit;
        }
    }

    // ========================= USER LOGOUT ========================= //
    if ($action === 'logout') {
        // Check if it's an admin trying to logout
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            // Admin logout logic - clear admin session
            error_log("Admin logout via AuthController: " . ($_SESSION['email'] ?? 'unknown'));
            
            // Clear all session data
            session_unset();
            session_destroy();
            
            // Start a new session for the logout message
            session_start();
            session_regenerate_id(true);
            
            // Redirect to admin login
            header("Location: ../../app/views/admin/login.php?success=logged_out");
            exit;
        }
        
        // Regular user logout logic
        error_log("User logout: " . ($_SESSION['email'] ?? 'unknown'));
        
        // Clear all session data
        session_unset();
        session_destroy();
        
        // Start a new session for the logout message
        session_start();
        session_regenerate_id(true);
        
        header("Location: ../../app/views/auth/login.php?success=logged_out");
        exit;
    }

    // ========================= FORGOT PASSWORD - REQUEST RESET ========================= //
    if ($action === 'forgot_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        
        // Check if user exists
        $existingUser = $user->getUserByEmail($email);
        
        // Always show success message (security: don't reveal if email exists)
        if (!$existingUser) {
            error_log("Password reset requested for non-existent email: $email");
            header("Location: ../../app/views/auth/login.php?success=reset_email_sent");
            exit;
        }
        
        // Check if account is active
        if ($existingUser['status'] !== 'Active') {
            $status = strtolower($existingUser['status']);
            header("Location: ../../app/views/auth/login.php?error=$status");
            exit;
        }
        
        // Check rate limiting (max 5 attempts per hour)
        $attemptCount = $otp->getPasswordResetAttemptCount($email, 60);
        if ($attemptCount >= 5) {
            error_log("Too many password reset attempts for: $email ($attemptCount attempts)");
            header("Location: ../../app/views/auth/login.php?success=reset_email_sent");
            exit;
        }
        
        // Generate 6-digit OTP for password reset
        $resetOTP = rand(100000, 999999);
        $expiration = date("Y-m-d H:i:s", strtotime("+15 minutes"));
        
        // Save reset OTP to database
        $otpSaved = $otp->savePasswordResetOTP($email, $resetOTP, $expiration);
        
        if (!$otpSaved) {
            error_log("Failed to save password reset OTP for: $email");
            header("Location: ../../app/views/auth/login.php?error=reset_failed");
            exit;
        }
        
        // Send password reset email
        $emailSent = sendPasswordResetEmail($email, $resetOTP, $existingUser['username']);
        
        if (!$emailSent) {
            error_log("Failed to send password reset email to: $email");
            header("Location: ../../app/views/auth/login.php?error=email_failed");
            exit;
        }
        
        error_log("Password reset OTP sent successfully to: $email");
        header("Location: ../../app/views/auth/login.php?show_reset_otp=1&email=" . urlencode($email));
        exit;
    }

    // ========================= VERIFY RESET OTP ========================= //
    if ($action === 'verify_reset_otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $enteredOTP = trim($_POST['otp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        error_log("=== VERIFY RESET OTP ===");
        error_log("Email: $email, OTP: $enteredOTP");
        
        if (empty($email) || empty($enteredOTP)) {
            header("Location: ../../app/views/auth/login.php?error=invalid_request");
            exit;
        }
        
        // Verify the reset OTP
        $otpData = $otp->verifyPasswordResetOTP($email, $enteredOTP);
        
        if (!$otpData) {
            error_log("Invalid or expired reset OTP for: $email");
            header("Location: ../../app/views/auth/login.php?show_reset_otp=1&error=invalid_otp&email=" . urlencode($email));
            exit;
        }
        
        error_log("Reset OTP verified successfully for: $email");
        
        // Mark OTP as used
        $otp->markPasswordResetOTPUsed($otpData['id']);
        
        // Store verified email in session
        $_SESSION['reset_verified_email'] = $email;
        $_SESSION['reset_otp_id'] = $otpData['id'];
        $_SESSION['reset_verified_at'] = time();
        
        // Redirect to new password form
        header("Location: ../../app/views/auth/login.php?show_new_password=1&email=" . urlencode($email));
        exit;
    }

    // ========================= SET NEW PASSWORD ========================= //
    if ($action === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_SESSION['reset_verified_email'] ?? null;
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        error_log("=== RESET PASSWORD ===");
        error_log("Email from session: $email");
        
        // Validate session
        if (!$email || !isset($_SESSION['reset_otp_id'])) {
            error_log("Invalid session for password reset");
            header("Location: ../../app/views/auth/login.php?error=session_expired");
            exit;
        }
        
        // Check session timeout (15 minutes)
        $verifiedAt = $_SESSION['reset_verified_at'] ?? 0;
        if ((time() - $verifiedAt) > 900) {
            error_log("Password reset session expired for: $email");
            unset($_SESSION['reset_verified_email']);
            unset($_SESSION['reset_otp_id']);
            unset($_SESSION['reset_verified_at']);
            header("Location: ../../app/views/auth/login.php?error=session_expired");
            exit;
        }
        
        // Validate passwords
        if (empty($newPassword) || empty($confirmPassword)) {
            header("Location: ../../app/views/auth/login.php?show_new_password=1&error=empty_password&email=" . urlencode($email));
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            header("Location: ../../app/views/auth/login.php?show_new_password=1&error=password_mismatch&email=" . urlencode($email));
            exit;
        }
        
        // Password strength validation
        if (strlen($newPassword) < 8) {
            header("Location: ../../app/views/auth/login.php?show_new_password=1&error=weak_password&email=" . urlencode($email));
            exit;
        }
        
        // Update password in database
        $passwordUpdated = $user->updatePassword($email, $newPassword);
        
        if (!$passwordUpdated) {
            error_log("Failed to update password for: $email");
            header("Location: ../../app/views/auth/login.php?show_new_password=1&error=update_failed&email=" . urlencode($email));
            exit;
        }
        
        error_log("Password updated successfully for: $email");
        
        // Clear failed login attempts
        $user->clearFailedAttempts($email);
        
        // Clear session data
        unset($_SESSION['reset_verified_email']);
        unset($_SESSION['reset_otp_id']);
        unset($_SESSION['reset_verified_at']);
        
        // Send confirmation email
        sendPasswordChangedEmail($email);
        
        // Redirect to login with success
        header("Location: ../../app/views/auth/login.php?success=password_reset&email=" . urlencode($email));
        exit;
    }

    // ========================= RESEND RESET OTP ========================= //
    if ($action === 'resend_reset_otp') {
        $email = $_GET['email'] ?? null;
        
        if (!$email) {
            header("Location: ../../app/views/auth/login.php?error=invalid_request");
            exit;
        }
        
        // Check rate limiting
        $attemptCount = $otp->getPasswordResetAttemptCount($email, 60);
        if ($attemptCount >= 5) {
            error_log("Too many resend attempts for: $email");
            header("Location: ../../app/views/auth/login.php?show_reset_otp=1&error=too_many_attempts&email=" . urlencode($email));
            exit;
        }
        
        // Generate new OTP
        $resetOTP = rand(100000, 999999);
        $expiration = date("Y-m-d H:i:s", strtotime("+15 minutes"));
        
        // Save OTP
        $otpSaved = $otp->savePasswordResetOTP($email, $resetOTP, $expiration);
        
        if (!$otpSaved) {
            header("Location: ../../app/views/auth/login.php?show_reset_otp=1&error=otp_failed&email=" . urlencode($email));
            exit;
        }
        
        // Get username for personalized email
        $existingUser = $user->getUserByEmail($email);
        $username = $existingUser['username'] ?? 'User';
        
        // Send OTP
        $emailSent = sendPasswordResetEmail($email, $resetOTP, $username);
        
        if (!$emailSent) {
            header("Location: ../../app/views/auth/login.php?show_reset_otp=1&error=email_failed&email=" . urlencode($email));
            exit;
        }
        
        header("Location: ../../app/views/auth/login.php?show_reset_otp=1&success=otp_resent&email=" . urlencode($email));
        exit;
    }
}

// ========================= EMAIL FUNCTIONS ========================= //

function sendOTPEmail($email, $otp, $type = 'registration') {
    $config = require __DIR__ . '/../../config/mail.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->SMTPDebug = $config['debug'] ?? 0;

        $mail->setFrom($config['username'], $config['from_name']);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email - Welcome to Empire";
        $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
        </style>
    </head>
    <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: \"Poppins\", -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;'>
        <table role='presentation' style='width: 100%; border-collapse: collapse; background-color: #f4f4f4;'>
            <tr>
                <td align='center' style='padding: 40px 20px;'>
                    <table role='presentation' style='width: 100%; max-width: 600px; border-collapse: collapse; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);'>
                        
                        <!-- Header -->
                        <tr>
                            <td style='background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%); padding: 50px 40px; text-align: center;'>
                                <h1 style='margin: 0; color: #ffffff; font-size: 48px; font-weight: 900; letter-spacing: 4px; text-transform: uppercase; font-family: \"Poppins\", sans-serif;'>
                                    EMPIRE
                                </h1>
                                <p style='margin: 12px 0 0 0; color: #a0a0a0; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; font-weight: 500;'>
                                    Streetwear Culture
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Main Content -->
                        <tr>
                            <td style='padding: 60px 40px; text-align: center;'>
                                
                                <h2 style='margin: 0 0 16px 0; color: #1a1a1a; font-size: 32px; font-weight: 700; font-family: \"Poppins\", sans-serif;'>
                                    Verify Your Email
                                </h2>
                                
                                <p style='margin: 0 0 40px 0; color: #666666; font-size: 16px; line-height: 1.7; font-weight: 400; max-width: 480px; margin-left: auto; margin-right: auto;'>
                                    Welcome to <strong style='color: #1a1a1a; font-weight: 600;'>Empire</strong>! Enter the code below to verify your email and unlock exclusive streetwear drops.
                                </p>
                                
                                <!-- OTP Box -->
                                <table role='presentation' style='width: 100%; max-width: 400px; margin: 0 auto 40px auto; border-collapse: collapse;'>
                                    <tr>
                                        <td style='background: #f8f8f8; border: 3px solid #000000; border-radius: 12px; padding: 32px 20px; text-align: center;'>
                                            <p style='margin: 0 0 12px 0; color: #999999; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;'>
                                                Your Verification Code
                                            </p>
                                            <div style='font-size: 48px; font-weight: 700; letter-spacing: 12px; color: #000000; font-family: \"Poppins\", monospace; margin: 8px 0;'>
                                                $otp
                                            </div>
                                            <p style='margin: 12px 0 0 0; color: #999999; font-size: 13px; font-weight: 500;'>
                                                Expires in 10 minutes
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Security Notice -->
                                <table role='presentation' style='width: 100%; max-width: 480px; margin: 0 auto; border-collapse: collapse;'>
                                    <tr>
                                        <td style='background: #fffbf0; border-left: 4px solid #ffc107; padding: 20px 24px; border-radius: 8px; text-align: left;'>
                                            <p style='margin: 0; color: #856404; font-size: 14px; line-height: 1.6; font-weight: 500;'>
                                                <strong>Security Note:</strong> This code is valid for 10 minutes only. Didn't request this? You can safely ignore this email.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style='margin: 40px 0 0 0; color: #999999; font-size: 14px; line-height: 1.6; font-weight: 400;'>
                                    Questions? Contact us at <a href='mailto:support@empire.com' style='color: #000000; text-decoration: none; font-weight: 600;'>empirebsit2025@gmail.com</a>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style='background: #000000; padding: 40px 40px; text-align: center;'>
                                <p style='margin: 0 0 8px 0; color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;'>
                                    EMPIRE
                                </p>
                                <p style='margin: 0 0 24px 0; color: #808080; font-size: 13px; line-height: 1.6; font-weight: 400;'>
                                    Culture • Exclusivity • Lifestyle
                                </p>
                                
                                <div style='border-top: 1px solid #333333; padding-top: 24px;'>
                                    <p style='margin: 0; color: #666666; font-size: 12px; line-height: 1.6; font-weight: 400;'>
                                        © 2025 Empire Streetwear. All rights reserved.<br>
                                        You're receiving this because you registered at empire.com
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
";
        $mail->AltBody = "Welcome to Empire! Your verification code is: $otp. This code expires in 10 minutes. If you didn't create an account, please ignore this email.";

        return $mail->send();
    } catch (Exception $e) {
        error_log("OTP Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordResetEmail($email, $otp, $username = 'User') {
    $config = require __DIR__ . '/../../config/mail.php';
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->SMTPDebug = $config['debug'] ?? 0;
        
        $mail->setFrom($config['username'], $config['from_name']);
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = "Reset Your Password - Empire";
        $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
        </style>
    </head>
    <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: \"Poppins\", sans-serif;'>
        <table role='presentation' style='width: 100%; border-collapse: collapse; background-color: #f4f4f4;'>
            <tr>
                <td align='center' style='padding: 40px 20px;'>
                    <table role='presentation' style='width: 100%; max-width: 600px; border-collapse: collapse; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);'>
                        
                        <!-- Header -->
                        <tr>
                            <td style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 50px 40px; text-align: center;'>
                                <div style='background: rgba(255,255,255,0.15); width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center;'>
                                    <span style='font-size: 48px;'>🔐</span>
                                </div>
                                <h1 style='margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; font-family: \"Poppins\", sans-serif;'>
                                    Password Reset Request
                                </h1>
                            </td>
                        </tr>
                        
                        <!-- Main Content -->
                        <tr>
                            <td style='padding: 60px 40px; text-align: center;'>
                                
                                <p style='margin: 0 0 24px 0; color: #1a1a1a; font-size: 18px; font-weight: 600;'>
                                    Hi $username,
                                </p>
                                
                                <p style='margin: 0 0 40px 0; color: #666666; font-size: 16px; line-height: 1.7; max-width: 480px; margin-left: auto; margin-right: auto;'>
                                    We received a request to reset your password. Use the code below to set a new password for your Empire account.
                                </p>
                                
                                <!-- OTP Box -->
                                <table role='presentation' style='width: 100%; max-width: 400px; margin: 0 auto 40px auto; border-collapse: collapse;'>
                                    <tr>
                                        <td style='background: #fff5f5; border: 3px solid #dc3545; border-radius: 12px; padding: 32px 20px; text-align: center;'>
                                            <p style='margin: 0 0 12px 0; color: #dc3545; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;'>
                                                Password Reset Code
                                            </p>
                                            <div style='font-size: 48px; font-weight: 700; letter-spacing: 12px; color: #dc3545; font-family: \"Poppins\", monospace; margin: 8px 0;'>
                                                $otp
                                            </div>
                                            <p style='margin: 12px 0 0 0; color: #999999; font-size: 13px; font-weight: 500;'>
                                                Expires in 15 minutes
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <!-- Security Warning -->
                                <table role='presentation' style='width: 100%; max-width: 480px; margin: 0 auto; border-collapse: collapse;'>
                                    <tr>
                                        <td style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px 24px; border-radius: 8px; text-align: left;'>
                                            <p style='margin: 0; color: #856404; font-size: 14px; line-height: 1.6; font-weight: 500;'>
                                                <strong>⚠️ Security Alert:</strong> If you didn't request this password reset, please ignore this email or contact support immediately. Your password will remain unchanged.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style='margin: 40px 0 0 0; color: #999999; font-size: 14px; line-height: 1.6;'>
                                    Need help? Contact us at <a href='mailto:empirebsit2025@gmail.com' style='color: #dc3545; text-decoration: none; font-weight: 600;'>empirebsit2025@gmail.com</a>
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style='background: #000000; padding: 40px 40px; text-align: center;'>
                                <p style='margin: 0 0 8px 0; color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;'>
                                    EMPIRE
                                </p>
                                <p style='margin: 0 0 24px 0; color: #808080; font-size: 13px; line-height: 1.6;'>
                                    Secure • Trusted • Protected
                                </p>
                                
                                <div style='border-top: 1px solid #333333; padding-top: 24px;'>
                                    <p style='margin: 0; color: #666666; font-size: 12px; line-height: 1.6;'>
                                        © 2025 Empire Streetwear. All rights reserved.<br>
                                        This is an automated security email.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
";
        $mail->AltBody = "Hi $username, Your password reset code is: $otp. This code expires in 15 minutes. If you didn't request this, please ignore this email.";
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Password Reset Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendPasswordChangedEmail($email) {
    $config = require __DIR__ . '/../../config/mail.php';
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->SMTPDebug = $config['debug'] ?? 0;
        
        $mail->setFrom($config['username'], $config['from_name']);
        $mail->addAddress($email);
        
        $mail->isHTML(true);
        $mail->Subject = "Password Changed Successfully - Empire";
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
            </style>
        </head>
        <body style='font-family: Poppins, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 40px; text-align: center;'>
                    <span style='font-size: 64px;'>✅</span>
                    <h2 style='color: white; margin: 16px 0 0 0; font-size: 28px;'>Password Changed Successfully</h2>
                </div>
                <div style='padding: 40px; text-align: center;'>
                    <p style='color: #333; font-size: 16px; line-height: 1.6; margin-bottom: 20px;'>
                        Your Empire account password has been changed successfully.
                    </p>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p style='margin: 0; color: #666; font-size: 14px;'>
                            <strong style='color: #333;'>Time:</strong> " . date('F j, Y, g:i a') . "
                        </p>
                    </div>
                    <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; margin: 20px 0; text-align: left;'>
                        <p style='margin: 0; color: #856404; font-size: 14px;'>
                            <strong>⚠️ If you didn't make this change,</strong> please contact our support team immediately at 
                            <a href='mailto:empirebsit2025@gmail.com' style='color: #dc3545;'>empirebsit2025@gmail.com</a>
                        </p>
                    </div>
                </div>
                <div style='background: #000; padding: 24px; text-align: center;'>
                    <p style='color: #666; font-size: 12px; margin: 0;'>
                        © 2025 Empire Streetwear. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Your Empire account password has been changed successfully on " . date('F j, Y, g:i a') . ". If you didn't make this change, please contact support immediately.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Password Changed Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ========================= TURNSTILE VERIFICATION FUNCTION ========================= //
function verifyTurnstileToken($token, $remoteIp = '') {
    $turnstileConfig = require __DIR__ . '/../../config/turnstile.php';
    $secretKey = $turnstileConfig['secret_key'];
    
    error_log("=== TURNSTILE VERIFICATION ===");
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
        error_log("Turnstile cURL Error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Turnstile API returned HTTP $httpCode");
        return false;
    }
    
    $result = json_decode($response, true);
    error_log("Turnstile API Response: " . print_r($result, true));
    
    if (isset($result['success'])) {
        error_log("Turnstile verification: " . ($result['success'] ? "SUCCESS" : "FAILED"));
        if (!$result['success'] && isset($result['error-codes'])) {
            error_log("Turnstile errors: " . implode(', ', $result['error-codes']));
        }
    }
    
    return isset($result['success']) && $result['success'] === true;
}
?>