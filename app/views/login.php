<?php
        session_start();
        require_once __DIR__ . '/../../config/dbcon.php';
        require_once __DIR__ . '/../../config/constants.php';

        $turnstileConfig = require __DIR__ . '/../../config/turnstile.php';
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

        // Redirect if already logged in as admin
        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff') {
                header('Location: /e-commerce/app/views/admin/admin-dashboard.php');
                exit;
            }
        }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../../public/css/admin-login.css">
        
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

        <style>
            /* Admin-specific elements */
            .admin-badge {
                display: inline-block;
                background: black;
                color: white;
                padding: 6px 16px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: 0.5px;
                margin-bottom: 16px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            }

            .admin-info {
                background: rgba(0, 0, 0, 0.05);
                border-left: 4px solid black;
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 13px;
                color: #555;
            }

            .admin-info i {
                color: black;
                margin-right: 8px;
            }

            .form-label i {
                color: black;
            }

            .cf-turnstile { 
                margin: 0 auto; 
                display: flex; 
                justify-content: center; 
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row g-0">
                <!-- Centered Admin Login Form -->
                <div class="col-12 auth-section-centered">
                    <button class="back-button" onclick="goBack()" title="Back">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="auth-card">
                        <div class="auth-container">
                        <!-- ==================== ADMIN LOGIN FORM ==================== -->
                        <div id="adminLoginForm" class="auth-form active">
                            <div class="auth-header">
                                <span class="admin-badge">
                                    <i class="fas fa-shield-alt me-1"></i>SECURE ACCESS
                                </span>
                                <h2>Management Portal</h2>
                                <p>Secure login for authorized personnel</p>
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

                            <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
                                <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                                    <i class="fas fa-ban me-2"></i>
                                    <div>
                                        <strong>Access Denied!</strong> 
                                        You do not have administrator privileges.
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                           <form action="/E-commerce/app/controllers/AdminAuthController.php?action=admin_login" method="POST" autocomplete="off" id="adminLoginFormElement">
                               <div class="mb-3">
                                    <label for="adminEmail" class="form-label">
                                        <i class="fas fa-user-shield me-2"></i>Work Email
                                    </label>

                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="adminEmail" 
                                        name="email" 
                                        placeholder="Enter your admin email" 
                                        required
                                        autocomplete="off"
                                        value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>" > 
                                </div>

                                <div class="mb-3">
                                    <label for="adminPassword" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="password-input-container">
                                        <input type="password" class="form-control" id="adminPassword" name="password" 
                                            placeholder="Enter your admin password" required autocomplete="new-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('adminPassword', this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                               <div class="mb-3">
                    <label for="role" class="form-label fw-bold">Login As:</label>
                    <select class="form-select" name="role" id="role" required>
                        <option value="" selected disabled>-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                                
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
                                <button type="submit" class="btn btn-primary mb-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
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

        <script>
        // ==================== PAGE INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Login Page Loaded');
            
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
        });

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
                        message = 'Welcome back, Administrator!';
                        showModal('success', title, message);
                        setTimeout(() => {
                            window.location.href = '/e-commerce/app/views/admin/admin-dashboard.php';
                        }, 2000);
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
                            message = `Invalid admin credentials. You have ${remaining} attempt${remaining > 1 ? 's' : ''} remaining.`;
                        } else {
                            message = 'Invalid admin email or password or wrong role. Please check your credentials and try again.';
                        }
                        break;
                    case 'unauthorized':
                        title = 'Access Denied';
                        message = 'You do not have administrator privileges to access this area.';
                        break;
                    default:
                        message = 'Your account currently block.';
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
            } else {
                modalId = 'errorModal';
                modalTitleId = 'errorModalLabel';
                modalMessageId = 'errorMessage';
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
            const passwordInput = document.getElementById('adminPassword');
            if (passwordInput && passwordInput.nextElementSibling) {
                const button = passwordInput.nextElementSibling;
                const icon = button.querySelector('i');
                
                if (passwordInput && icon) {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }

        function goBack() {
            if (confirm('Are you sure you want to leave the admin login page?')) {
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
    </body>
    </html>