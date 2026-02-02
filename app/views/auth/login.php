    <?php
        session_start();
        require_once __DIR__ . '/../../../config/dbcon.php';
        require_once __DIR__ . '/../../../config/constants.php';

        // ======== LOAD TURNSTILE CONFIG ========
        $turnstileConfig = require __DIR__ . '/../../../config/turnstile.php';
        $turnstileSiteKey = $turnstileConfig['site_key'];

        $showCaptcha = false;
        $attemptCount = 0;
        $remainingAttempts = 5;

        if (isset($_GET['attempts'])) {
            $attemptCount = (int)$_GET['attempts'];
            $showCaptcha = $attemptCount >= 3;
        }

        if (isset($_GET['remaining'])) {
            $remainingAttempts = (int)$_GET['remaining'];
        }

        if (isset($_GET['error']) && in_array($_GET['error'], ['captcha_required', 'captcha_failed', 'account_locked', 'max_attempts'])) {
            $showCaptcha = true;
        }

        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        // Check which form to show
        $showOTP = isset($_GET['show_otp']) && $_GET['show_otp'] == '1';
        $showResetOTP = isset($_GET['show_reset_otp']) && $_GET['show_reset_otp'] == '1';
        $showNewPassword = isset($_GET['show_new_password']) && $_GET['show_new_password'] == '1';
        $userEmail = $_GET['email'] ?? ($_SESSION['pending_user_email'] ?? '');
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login & Signup</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../../../public/css/login.css">
        
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

        <style>
            .cf-turnstile { margin: 0 auto; display: flex; justify-content: center; }
            .alert { border-radius: 8px; font-size: 14px; animation: slideDown 0.3s ease-out; }
            .alert-warning { background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; }
            .alert-danger { background-color: #f8d7da; border: 1px solid #dc3545; color: #842029; }
            .alert-info { background-color: #e7f3ff; border: 1px solid #0dcaf0; color: #055160; }
            
            .password-input-container { position: relative; }
            .password-toggle {
                position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                background: none; border: none; cursor: pointer; color: #6c757d;
                padding: 5px 10px; z-index: 10;
            }
            .password-toggle:hover { color: #000; }
            
            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .forgot-password-link {
                display: block;
                text-align: right;
                margin-top: -8px;
                margin-bottom: 16px;
                font-size: 14px;
                color: #666;
                text-decoration: none;
                transition: color 0.3s;
            }
            .forgot-password-link:hover {
                color: #000;
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row g-0">
                <!-- Left side - All Forms -->
                <div class="col-lg-6 auth-section">
                    <button class="back-button" onclick="goBack()" title="Back">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="auth-container">
                        <!-- ==================== LOGIN FORM ==================== -->
                        <div id="loginForm" class="auth-form active">
                            <div class="auth-header">
                                <h2>Welcome Back</h2>
                                <p>Please sign in to your account</p>
                            </div>
                            
                            <?php if ($attemptCount > 0 && $attemptCount < 5): ?>
                                <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong>Warning:</strong> 
                                        <?php echo "$remainingAttempts attempt" . ($remainingAttempts != 1 ? 's' : '') . " remaining before account lockout."; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_GET['error']) && $_GET['error'] === 'account_locked'): ?>
                                <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                                    <i class="fas fa-lock me-2"></i>
                                    <div>
                                        <strong>Account Locked!</strong> 
                                        Too many failed attempts. Please wait <?php echo $_GET['minutes'] ?? '15'; ?> minutes.
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <form action="../../../app/controllers/AuthController.php?action=login" method="POST" autocomplete="off" id="loginFormElement">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label"><i class="fas fa-envelope me-1"></i> Email Address</label>
                                    <input type="email" class="form-control" id="loginEmail" name="email" 
                                        placeholder="Enter your email" required autocomplete="new-email"
                                        value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label"><i class="fas fa-lock me-1"></i> Password</label>
                                    <div class="password-input-container">
                                        <input type="password" class="form-control" id="loginPassword" name="password" 
                                            placeholder="Enter your password" required autocomplete="new-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('loginPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Forgot Password Link -->
                                <a href="#" class="forgot-password-link" onclick="showForgotPasswordForm(event)">
                                    <i class="fas fa-key me-1"></i> Forgot Password?
                                </a>
                                
                                <?php if ($showCaptcha): ?>
                                    <div class="mb-3">
                                        <div class="alert alert-info d-flex align-items-center mb-2" role="alert">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            <small>Please complete the security verification below</small>
                                        </div>
                                        <div class="cf-turnstile" 
                                            data-sitekey="<?= htmlspecialchars($turnstileSiteKey) ?>" 
                                            data-theme="light" data-size="normal">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe" style="border: 2px solid #e0e0e0;">
                                    <label class="form-check-label" for="rememberMe" style="color: black;">Remember me</label>
                                </div>
                               <button type="submit" class="btn btn-primary mb-3 w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                                </button>
                            </form>
                            <div class="toggle-text">
                                Don't have an account? <a href="#" class="toggle-link" onclick="toggleForms()">Sign up</a>
                            </div>
                        </div>

                        <!-- ==================== SIGNUP FORM ==================== -->
                        <div id="signupForm" class="auth-form">
                            <div class="auth-header">
                                <h2>Create Account</h2>
                                <p>Please fill in your information</p>
                            </div>
                            <form id="signupFormElement" action="../../../app/controllers/AuthController.php?action=register" method="POST" autocomplete="off">
                                <div class="mb-3">
                                    <label for="signupName" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="signupName" name="username" 
                                        placeholder="Enter your full name" required autocomplete="new-username">
                                </div>
                                <div class="mb-3">
                                    <label for="signupEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="signupEmail" name="email" 
                                        placeholder="Enter your email" required autocomplete="new-email">
                                </div>

                                 <div class="mb-3">
                                    <label for="signupPassword" class="form-label">Password</label>
                                    <div class="password-input-container">
                                        <input type="password" class="form-control" id="signupPassword" name="password" 
                                            placeholder="Create a password" required autocomplete="new-password" minlength="8">
                                        <button type="button" class="password-toggle" onclick="togglePassword('signupPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Password must be at least 8 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <div class="password-input-container">
                                        <input type="password" class="form-control" id="confirmPassword" 
                                            placeholder="Confirm your password" required autocomplete="new-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="agreeTerms" style="border: 2px solid #e0e0e0;" required>
                                    <label class="form-check-label" for="agreeTerms" style="color: black;">
                                        I agree to the Terms and Conditions
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary mb-3">Create Account</button>
                            </form>
                            <div class="toggle-text">
                                Already have an account? <a href="#" class="toggle-link" onclick="toggleForms()">Sign in</a>
                            </div>
                        </div>

                        <!-- ==================== FORGOT PASSWORD FORM ==================== -->
                        <div id="forgotPasswordForm" class="auth-form">
                            <div class="auth-header">
                                <div class="email-icon-container">
                                    <div class="email-icon">
                                        <i class="fas fa-key"></i>
                                    </div>
                                </div>
                                <h2>Forgot Password?</h2>
                                <p>Enter your email and we'll send you a code to reset your password</p>
                            </div>
                            
                            <form action="../../../app/controllers/AuthController.php?action=forgot_password" method="POST">
                                <div class="mb-3">
                                    <label for="forgotEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="forgotEmail" name="email" 
                                        placeholder="Enter your registered email" required>
                                </div>
                                <button type="submit" class="btn btn-primary mb-3">Send Reset Code</button>
                            </form>
                            
                            <div class="toggle-text">
                                Remember your password? <a href="#" class="toggle-link" onclick="showLoginForm()">Sign in</a>
                            </div>
                        </div>

                        <!-- ==================== RESET OTP VERIFICATION FORM ==================== -->
                        <div id="resetOtpForm" class="auth-form">
                            <div class="email-icon-container">
                                <div class="email-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                            </div>

                            <div class="auth-header">
                                <h2>Verify Reset Code</h2>
                                <p>We've sent a verification code to<br><span class="email-display" id="resetEmailDisplay">user@example.com</span></p>
                            </div>

                            <form id="resetOtpVerificationForm" action="../../../app/controllers/AuthController.php?action=verify_reset_otp" method="POST">
                                <input type="hidden" name="otp" id="resetCombinedOTP">
                                <input type="hidden" name="email" id="resetEmailHidden">
                                
                                <div class="otp-inputs" id="resetOtpInputs">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                </div>

                                <button type="submit" class="btn btn-verify" id="resetVerifyBtn" disabled>
                                    <span class="btn-text">Verify Code</span>
                                </button>
                            </form>

                            <div class="resend-section">
                                Didn't receive the code? 
                                <a href="#" class="resend-link disabled" id="resetResendLink" onclick="resendResetOTP(event)">Resend Code</a>
                            </div>
                        </div>

                        <!-- ==================== NEW PASSWORD FORM ==================== -->
                        <div id="newPasswordForm" class="auth-form">
                            <div class="email-icon-container">
                                <div class="email-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                    <i class="fas fa-check-circle" style="color: white;"></i>
                                </div>
                            </div>

                            <div class="auth-header">
                                <h2>Create New Password</h2>
                                <p>Your identity has been verified.<br>Set your new password below.</p>
                            </div>

                            <form action="../../../app/controllers/AuthController.php?action=reset_password" method="POST">
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <div class="password-input-container">
                                        <input type="password" class="form-control" id="newPassword" name="new_password" 
                                            placeholder="Enter new password" required minlength="8">
                                        <button type="button" class="password-toggle" onclick="togglePassword('newPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Password must be at least 8 characters</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                                    <div class="password-input-container">
                                        <input type="password" class="form-control" id="confirmNewPassword" name="confirm_password" 
                                            placeholder="Confirm new password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirmNewPassword', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mb-3">Reset Password</button>
                            </form>
                        </div>

                        <!-- ==================== SIGNUP OTP VERIFICATION FORM ==================== -->
                        <div id="otpForm" class="auth-form">
                            <div class="email-icon-container">
                                <div class="email-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>

                            <div class="auth-header">
                                <h2>Verify Your Email</h2>
                                <p>We've sent a verification code to<br><span class="email-display" id="otpEmailDisplay">user@example.com</span></p>
                            </div>

                            <form id="otpVerificationForm" action="../../../app/controllers/AuthController.php?action=verify_otp" method="POST">
                                <input type="hidden" name="otp" id="combinedOTP">
                                
                                <div class="otp-inputs">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                </div>

                                <button type="submit" class="btn btn-verify" id="verifyBtn" disabled>
                                    <span class="btn-text">Verify Code</span>
                                </button>
                            </form>

                            <div class="resend-section">
                                Didn't receive the code? 
                                <a href="#" class="resend-link disabled" id="resendLink" onclick="resendCode(event)">Resend Code</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 image-section">
                    <div class="scroll-container">
                        <div class="image-placeholder">
                            <img src="<?= ASSETS_URL ?>/images/hero1.jpg" alt="Welcome" class="slide-image">
                            <div class="slide-overlay">
                                <h3>Welcome to Our Platform</h3>
                                <p>Discover amazing features</p>
                            </div>
                        </div>
                        <div class="image-placeholder">
                            <img src="<?= ASSETS_URL ?>/images/hero2.jpg" alt="Modern Design" class="slide-image">
                            <div class="slide-overlay">
                                <h3>Modern Design</h3>
                                <p>Beautiful and intuitive</p>
                            </div>
                        </div>
                        <div class="image-placeholder">
                            <img src="<?= ASSETS_URL ?>/images/hero3.jpg" alt="Secure" class="slide-image">
                            <div class="slide-overlay">
                                <h3>Secure & Fast</h3>
                                <p>Your data is safe with us</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 text-center position-relative">
                        <div class="w-100">
                            <div class="success-animation mb-3">
                                <i class="fas fa-check-circle modal-icon success-icon"></i>
                                <div class="success-ripple"></div>
                            </div>
                            <h5 class="modal-title fw-bold" id="successModalLabel">Success!</h5>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center px-4 pb-4">
                        <p id="successMessage" class="mb-4 text-muted fs-6"></p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success btn-lg rounded-pill shadow-sm hover-lift" data-bs-dismiss="modal">
                                <i class="fas fa-check me-2"></i>Got it!
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 text-center position-relative">
                        <div class="w-100">
                            <div class="error-animation mb-3">
                                <i class="fas fa-exclamation-circle modal-icon error-icon"></i>
                                <div class="error-pulse"></div>
                            </div>
                            <h5 class="modal-title fw-bold" id="errorModalLabel">Oops!</h5>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center px-4 pb-4">
                        <p id="errorMessage" class="mb-4 text-muted fs-6"></p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-danger btn-lg rounded-pill shadow-sm hover-lift" data-bs-dismiss="modal">
                                <i class="fas fa-redo me-2"></i>Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Modal -->
        <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 text-center position-relative">
                        <div class="w-100">
                            <i class="fas fa-info-circle modal-icon info-icon mb-3"></i>
                            <h5 class="modal-title fw-bold" id="infoModalLabel">Information</h5>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center px-4 pb-4">
                        <p id="infoMessage" class="mb-4 text-muted fs-6"></p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-info btn-lg rounded-pill shadow-sm hover-lift" data-bs-dismiss="modal">
                                <i class="fas fa-check me-2"></i>Okay
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // ==================== GLOBAL VARIABLES ====================
        let userEmail = '';

        // ==================== PAGE INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            
            setTimeout(forceClearAllInputs, 100);
            
            // Initialize modals
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('hidden.bs.modal', function() {
                    this.setAttribute('aria-hidden', 'true');
                });
                modal.addEventListener('show.bs.modal', function() {
                    this.removeAttribute('aria-hidden');
                });
            });
            
            showModalBasedOnURL();
            
            // Password match validation for signup
            const signupFormElement = document.getElementById('signupFormElement');
            const passwordInput = document.getElementById('signupPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');

            if (signupFormElement) {
                signupFormElement.addEventListener('submit', function(e) {
                    if (passwordInput.value !== confirmPasswordInput.value) {
                        e.preventDefault();
                        showModal('error', 'Password Mismatch', 'The passwords you entered do not match. Please try again.');
                        confirmPasswordInput.focus();
                        return;
                    }
                });
            }

            // Password match validation for new password form
            const newPasswordForm = document.querySelector('#newPasswordForm form');
            if (newPasswordForm) {
                newPasswordForm.addEventListener('submit', function(e) {
                    const newPass = document.getElementById('newPassword').value;
                    const confirmPass = document.getElementById('confirmNewPassword').value;
                    
                    if (newPass !== confirmPass) {
                        e.preventDefault();
                        showModal('error', 'Password Mismatch', 'The passwords you entered do not match. Please try again.');
                        return;
                    }
                });
            }

            initializeOTPInputs();
            
            // Auto-show forms based on URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('show_otp') === '1') {
                showOTPForm();
            } else if (urlParams.get('show_reset_otp') === '1') {
                showResetOTPForm();
            } else if (urlParams.get('show_new_password') === '1') {
                showNewPasswordForm();
            }
        });

        // ==================== FORM NAVIGATION ====================
        function showLoginForm() {
            hideAllForms();
            document.getElementById('loginForm').classList.add('active');
            forceClearAllInputs();
            cleanURL();
        }

        function showForgotPasswordForm(e) {
            if (e) e.preventDefault();
            hideAllForms();
            document.getElementById('forgotPasswordForm').classList.add('active');
            forceClearAllInputs();
        }

        function showResetOTPForm() {
            const urlParams = new URLSearchParams(window.location.search);
            const email = urlParams.get('email');
            
            hideAllForms();
            document.getElementById('resetOtpForm').classList.add('active');
            
            if (email) {
                document.getElementById('resetEmailDisplay').textContent = email;
                document.getElementById('resetEmailHidden').value = email;
                userEmail = email;
            }
            
            // Clear OTP inputs
            document.querySelectorAll('#resetOtpInputs .otp-input').forEach(input => {
                input.value = '';
                input.classList.remove('filled');
            });
            
            // Initialize reset OTP inputs
            initializeResetOTPInputs();
            
            // Focus first input
            setTimeout(() => {
                document.querySelector('#resetOtpInputs .otp-input').focus();
            }, 100);
            
            startResendTimer('resetResendLink');
        }

        function showNewPasswordForm() {
            hideAllForms();
            document.getElementById('newPasswordForm').classList.add('active');
            forceClearAllInputs();
        }

        function showOTPForm() {
            const urlParams = new URLSearchParams(window.location.search);
            const action = urlParams.get('action');
            const email = urlParams.get('email');
            
            hideAllForms();
            document.getElementById('otpForm').classList.add('active');
            
            if (email) {
                document.getElementById('otpEmailDisplay').textContent = email;
                userEmail = email;
            }
            
            const otpVerificationForm = document.getElementById('otpVerificationForm');
            const resendLink = document.getElementById('resendLink');
            
            if (action === 'verify_signup') {
                otpVerificationForm.action = '../../../app/controllers/AuthController.php?action=verify_signup_otp';
                resendLink.onclick = function(e) {
                    e.preventDefault();
                    if (!this.classList.contains('disabled')) {
                        window.location.href = '../../../app/controllers/AuthController.php?action=resend_otp&type=signup';
                    }
                };
            }
            
            // Clear OTP inputs
            document.querySelectorAll('.otp-input').forEach(input => {
                input.value = '';
                input.classList.remove('filled');
            });
            
            document.getElementById('verifyBtn').disabled = true;
            
            setTimeout(() => {
                document.querySelector('.otp-input').focus();
            }, 100);
            
            startResendTimer('resendLink');
        }

        function hideAllForms() {
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
        }

        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            
            forceClearAllInputs();
            
            if (loginForm.classList.contains('active')) {
                loginForm.classList.remove('active');
                signupForm.classList.add('active');
            } else {
                signupForm.classList.remove('active');
                loginForm.classList.add('active');
            }
            
            setTimeout(forceClearAllInputs, 50);
        }

        // ==================== OTP INPUT HANDLING ====================
        function initializeOTPInputs() {
            const otpInputs = document.querySelectorAll('#otpForm .otp-input');
            const verifyBtn = document.getElementById('verifyBtn');
            const combinedOTPInput = document.getElementById('combinedOTP');

            setupOTPInputs(otpInputs, verifyBtn, combinedOTPInput);
        }

        function initializeResetOTPInputs() {
            const otpInputs = document.querySelectorAll('#resetOtpInputs .otp-input');
            const verifyBtn = document.getElementById('resetVerifyBtn');
            const combinedOTPInput = document.getElementById('resetCombinedOTP');

            setupOTPInputs(otpInputs, verifyBtn, combinedOTPInput);
        }

        function setupOTPInputs(otpInputs, verifyBtn, combinedOTPInput) {
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    const value = e.target.value;
                    
                    if (!/^\d$/.test(value)) {
                        e.target.value = '';
                        return;
                    }

                    if (value) {
                        input.classList.add('filled');
                        if (index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        }
                    } else {
                        input.classList.remove('filled');
                    }

                    updateCombinedOTP();
                    checkAllFilled();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        otpInputs[index - 1].focus();
                        otpInputs[index - 1].value = '';
                        otpInputs[index - 1].classList.remove('filled');
                        updateCombinedOTP();
                        checkAllFilled();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').trim();
                    
                    if (/^\d{6}$/.test(pastedData)) {
                        pastedData.split('').forEach((char, i) => {
                            if (otpInputs[i]) {
                                otpInputs[i].value = char;
                                otpInputs[i].classList.add('filled');
                            }
                        });
                        otpInputs[5].focus();
                        updateCombinedOTP();
                        checkAllFilled();
                    }
                });
            });

            function updateCombinedOTP() {
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                combinedOTPInput.value = otp;
            }

            function checkAllFilled() {
                const allFilled = Array.from(otpInputs).every(input => input.value.length === 1);
                verifyBtn.disabled = !allFilled;
            }
        }

        // ==================== RESEND TIMER ====================
        function startResendTimer(linkId) {
            const resendLink = document.getElementById(linkId);
            let countdown = 60;
            
            resendLink.classList.add('disabled');
            resendLink.style.pointerEvents = 'none';
            
            const interval = setInterval(() => {
                countdown--;
                if (countdown > 0) {
                    resendLink.textContent = `Resend Code (${countdown}s)`;
                } else {
                    clearInterval(interval);
                    resendLink.textContent = 'Resend Code';
                    resendLink.classList.remove('disabled');
                    resendLink.style.pointerEvents = 'auto';
                }
            }, 1000);
        }

        function resendResetOTP(e) {
            e.preventDefault();
            const link = e.target;
            
            if (link.classList.contains('disabled')) {
                return;
            }
            
            const email = document.getElementById('resetEmailHidden').value;
            if (email) {
                window.location.href = `../../../app/controllers/AuthController.php?action=resend_reset_otp&email=${encodeURIComponent(email)}`;
            }
        }

        function resendCode(e) {
            e.preventDefault();
            const link = e.target;
            
            if (link.classList.contains('disabled')) {
                return;
            }
            
            window.location.href = '../../../app/controllers/AuthController.php?action=resend_otp&type=signup';
        }

        // ==================== MODAL FUNCTIONS ====================
        function showModalBasedOnURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const successParam = urlParams.get('success');
            const errorParam = urlParams.get('error');

            if (successParam) {
                let title = 'Success!';
                let message = '';
                
                switch (successParam) {
                    case 'login_success':
                        message = 'You have successfully logged in!';
                        showModal('success', title, message);
                        setTimeout(() => {
                            window.location.href = '/e-commerce/app/views/pages/shop.php';
                        }, 2000);
                        break;
                    case 'email_verified':
                        message = 'Email verified successfully! You can now sign in with your new account.';
                        showModal('success', title, message);
                        setTimeout(() => {
                            showLoginForm();
                        }, 2500);
                        break;
                    case 'reset_email_sent':
                        message = 'If an account exists with that email, we\'ve sent a password reset code.';
                        showModal('success', title, message);
                        break;
                    case 'password_reset':
                        message = 'Your password has been reset successfully! You can now sign in with your new password.';
                        showModal('success', title, message);
                        setTimeout(() => {
                            showLoginForm();
                        }, 2500);
                        break;
                    case 'otp_resent':
                        message = 'A new verification code has been sent to your email!';
                        showModal('success', title, message);
                        break;
                    case 'registered':
                        message = 'Account created successfully! You can now sign in.';
                        showModal('success', title, message);
                        break;
                    case 'logged_out':
                        title = 'Logged Out';
                        message = 'You have been successfully logged out.';
                        showModal('success', title, message);
                        break;
                }
            }
            
            if (errorParam) {
                let title = 'Error!';
                let message = '';
                
                switch (errorParam) {
                    case 'username_taken':
                        message = 'This username is already taken. Please choose a different one.';
                        break;
                    case 'email_taken':
                        message = 'This email address is already registered. Please use a different email or try logging in.';
                        break;
                    case 'account_locked':
                        const minutes = urlParams.get('minutes') || '15';
                        title = 'Account Temporarily Locked';
                        message = `🔒 Too many failed login attempts. Your account is locked for ${minutes} minutes.`;
                        break;
                    case 'max_attempts':
                        const lockMinutes = urlParams.get('minutes') || '15';
                        title = 'Account Locked';
                        message = `🚫 Maximum login attempts exceeded. Your account has been locked for ${lockMinutes} minutes.`;
                        break;
                    case 'captcha_required':
                        message = '🛡️ Security verification required. Please complete the CAPTCHA to continue.';
                        break;
                    case 'captcha_failed':
                        message = '❌ Security verification failed. Please try the CAPTCHA again.';
                        break;
                    case 'invalid_credentials':
                        const attempts = urlParams.get('attempts');
                        const remaining = urlParams.get('remaining');
                        if (attempts && remaining) {
                            message = `Invalid email or password. You have ${remaining} attempt${remaining > 1 ? 's' : ''} remaining.`;
                        } else {
                            message = 'Invalid email or password. Please check your credentials and try again.';
                        }
                        break;
                    case 'invalid_otp':
                        message = 'Invalid or expired code. Please try again or request a new code.';
                        showModal('error', title, message);
                        setTimeout(() => {
                            document.querySelectorAll('.otp-input').forEach(input => {
                                input.value = '';
                                input.classList.remove('filled');
                            });
                            document.querySelector('.otp-input').focus();
                        }, 1000);
                        return;
                    case 'session_expired':
                        message = 'Your session has expired. Please start the process again.';
                        showModal('error', title, message);
                        setTimeout(() => {
                            window.location.href = window.location.pathname;
                        }, 2000);
                        return;
                    case 'password_mismatch':
                        message = 'The passwords you entered do not match. Please try again.';
                        break;
                    case 'weak_password':
                        message = 'Password must be at least 8 characters long.';
                        break;
                    case 'update_failed':
                        message = 'Failed to update password. Please try again.';
                        break;
                    case 'reset_failed':
                        message = 'Failed to process reset request. Please try again.';
                        break;
                    case 'email_failed':
                        message = 'Failed to send email. Please try again later.';
                        break;
                    default:
                        message = 'An error occurred. Please try again.';
                }

                showModal('error', title, message);
            }
            
            if (successParam || errorParam) {
                cleanURL();
            }
        }

        function showModal(type, title, message) {
            let modalId, modalTitleId, modalMessageId;
            
            if (type === 'success') {
                modalId = 'successModal';
                modalTitleId = 'successModalLabel';
                modalMessageId = 'successMessage';
            } else if (type === 'error') {
                modalId = 'errorModal';
                modalTitleId = 'errorModalLabel';
                modalMessageId = 'errorMessage';
            } else {
                modalId = 'infoModal';
                modalTitleId = 'infoModalLabel';
                modalMessageId = 'infoMessage';
            }
            
            const modalElement = document.getElementById(modalId);
            const titleElement = document.getElementById(modalTitleId);
            const messageElement = document.getElementById(modalMessageId);
            
            if (!modalElement) {
                console.error('Modal element not found:', modalId);
                return;
            }
            
            if (titleElement) titleElement.textContent = title;
            if (messageElement) messageElement.textContent = message;
            
            modalElement.removeAttribute('aria-hidden');
            
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true
            });
            modal.show();
        }

        // ==================== UTILITY FUNCTIONS ====================
        function forceClearAllInputs() {
            const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            
            inputs.forEach(input => {
                input.value = '';
                input.setAttribute('value', '');
                if (input.hasAttribute('autocomplete')) {
                    input.setAttribute('autocomplete', 'new-password');
                }
            });
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            resetPasswordVisibility();
            
            inputs.forEach(input => {
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        function togglePassword(inputId, button) {
            const passwordInput = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function resetPasswordVisibility() {
            const passwordInputs = ['loginPassword', 'signupPassword', 'confirmPassword', 'newPassword', 'confirmNewPassword'];
            passwordInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input && input.nextElementSibling) {
                    const button = input.nextElementSibling;
                    const icon = button.querySelector('i');
                    
                    if (input && icon) {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        }

        function goBack() {
            const activeForm = document.querySelector('.auth-form.active');
            
            if (activeForm && activeForm.id !== 'loginForm') {
                if (confirm('Are you sure you want to go back? Any progress will be lost.')) {
                    window.location.href = window.location.pathname;
                }
            } else {
                forceClearAllInputs();
                window.location.href = '/e-commerce/public/index.php';
            }
        }

        function cleanURL() {
            const url = new URL(window.location.href);
            url.searchParams.delete('success');
            url.searchParams.delete('error');
            url.searchParams.delete('attempts');
            url.searchParams.delete('remaining');
            url.searchParams.delete('minutes');
            window.history.replaceState({}, document.title, url.pathname);
        }

        // Auto-close modals after 5 seconds
        document.addEventListener('shown.bs.modal', function (event) {
            setTimeout(function() {
                const modal = bootstrap.Modal.getInstance(event.target);
                if (modal) {
                    modal.hide();
                }
            }, 5000);
        });     
        </script>   


    
    
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="../../../public/js/login-handlers.js"></script>
    </body>
    </html>