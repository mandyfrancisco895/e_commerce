<?php
class OTP {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ==================== LOGIN OTP METHODS ==================== //

    // For login OTP (user exists)
    public function saveOTP($userId, $otp, $expiration) {
        $this->invalidateExistingOTPs($userId);
        
        $sql = "INSERT INTO user_otps (user_id, otp_code, expiration) 
                VALUES (:user_id, :otp_code, :expiration)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':otp_code' => $otp,
            ':expiration' => $expiration
        ]);
    }

    // Verify OTP for login (user exists)
    public function verifyOTP($userId, $otp) {
        $sql = "SELECT * FROM user_otps 
                WHERE user_id = :user_id 
                AND otp_code = :otp 
                AND used = 0
                AND expiration > NOW()
                ORDER BY id DESC LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':otp' => $otp
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==================== REGISTRATION OTP METHODS ==================== //

    // For registration OTP (user doesn't exist yet)
    public function saveOTPForEmail($email, $otp, $expiration) {
        // First, invalidate any existing OTPs for this email
        $this->invalidateExistingRegistrationOTPs($email);
        
        $sql = "INSERT INTO registration_otps (email, otp_code, expiration, used, created_at) 
                VALUES (:email, :otp_code, :expiration, 0, NOW())";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $result = $stmt->execute([
                ':email' => $email,
                ':otp_code' => $otp,
                ':expiration' => $expiration
            ]);
            
            if ($result) {
                error_log("OTP saved successfully for email: $email, OTP: $otp");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error saving OTP: " . $e->getMessage());
            return false;
        }
    }

    // Verify OTP for registration (user doesn't exist yet)
    public function verifyOTPForEmail($email, $otp) {
        $sql = "SELECT * FROM registration_otps 
                WHERE email = :email 
                AND otp_code = :otp 
                AND used = 0
                AND expiration > NOW()
                ORDER BY id DESC LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':email' => $email,
                ':otp' => $otp
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log("Verifying OTP for email: $email, entered OTP: $otp");
            error_log("OTP verification result: " . ($result ? "Found" : "Not found"));
            
            if ($result) {
                error_log("Found OTP - ID: {$result['id']}, Code: {$result['otp_code']}, Expiration: {$result['expiration']}, Used: {$result['used']}");
            } else {
                // Check if OTP exists but doesn't match conditions
                $debugSql = "SELECT * FROM registration_otps WHERE email = :email ORDER BY id DESC LIMIT 1";
                $debugStmt = $this->pdo->prepare($debugSql);
                $debugStmt->execute([':email' => $email]);
                $debugResult = $debugStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($debugResult) {
                    error_log("Latest OTP for this email: Code={$debugResult['otp_code']}, Expiration={$debugResult['expiration']}, Used={$debugResult['used']}, Current time=" . date('Y-m-d H:i:s'));
                } else {
                    error_log("No OTP found in database for email: $email");
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error verifying OTP: " . $e->getMessage());
            return false;
        }
    }

    // ==================== PASSWORD RESET OTP METHODS ==================== //

    /**
     * Save password reset OTP
     * Uses the new password_resets table
     */
    public function savePasswordResetOTP($email, $otp, $expiration) {
        try {
            // First, invalidate any existing unused password reset OTPs for this email
            $this->invalidateExistingPasswordResetOTPs($email);
            
            // Get IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Insert new password reset OTP
            $sql = "INSERT INTO password_resets 
                    (email, otp_code, expires_at, used, created_at, ip_address) 
                    VALUES (:email, :otp_code, :expires_at, 0, NOW(), :ip_address)";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':email' => $email,
                ':otp_code' => $otp,
                ':expires_at' => $expiration,
                ':ip_address' => $ipAddress
            ]);
            
            if ($result) {
                error_log("Password reset OTP saved for email: $email, OTP: $otp");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error saving password reset OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password reset OTP
     */
    public function verifyPasswordResetOTP($email, $otp) {
        $sql = "SELECT * FROM password_resets 
                WHERE email = :email 
                AND otp_code = :otp 
                AND used = 0 
                AND expires_at > NOW() 
                ORDER BY created_at DESC 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':email' => $email,
                ':otp' => $otp
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log("Verifying password reset OTP for email: $email, entered OTP: $otp");
            error_log("Password reset OTP verification result: " . ($result ? "Found" : "Not found"));
            
            if ($result) {
                error_log("Found password reset OTP - ID: {$result['id']}, Code: {$result['otp_code']}, Expiration: {$result['expires_at']}, Used: {$result['used']}");
            } else {
                // Debug: Check if OTP exists but doesn't match conditions
                $debugSql = "SELECT * FROM password_resets WHERE email = :email ORDER BY created_at DESC LIMIT 1";
                $debugStmt = $this->pdo->prepare($debugSql);
                $debugStmt->execute([':email' => $email]);
                $debugResult = $debugStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($debugResult) {
                    error_log("Latest password reset OTP: Code={$debugResult['otp_code']}, Expiration={$debugResult['expires_at']}, Used={$debugResult['used']}, Current time=" . date('Y-m-d H:i:s'));
                } else {
                    error_log("No password reset OTP found in database for email: $email");
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error verifying password reset OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark password reset OTP as used
     */
    public function markPasswordResetOTPUsed($id) {
        $sql = "UPDATE password_resets 
                SET used = 1, used_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $result = $stmt->execute([':id' => $id]);
            error_log("Marked password reset OTP as used. ID: $id, Success: " . ($result ? "Yes" : "No"));
            return $result;
        } catch (PDOException $e) {
            error_log("Error marking password reset OTP as used: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidate existing unused password reset OTPs for an email
     */
    public function invalidateExistingPasswordResetOTPs($email) {
        $sql = "UPDATE password_resets SET used = 1 
                WHERE email = :email AND used = 0";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $result = $stmt->execute([':email' => $email]);
            error_log("Invalidated existing password reset OTPs for email: $email");
            return $result;
        } catch (PDOException $e) {
            error_log("Error invalidating existing password reset OTPs: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get password reset attempt count for rate limiting
     */
    public function getPasswordResetAttemptCount($email, $minutes = 60) {
        try {
            $sql = "SELECT COUNT(*) as count FROM password_resets 
                    WHERE email = :email 
                    AND created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':minutes' => $minutes
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error getting password reset attempt count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clean up expired password reset OTPs
     * Run this via cron job or admin maintenance page
     */
    public function cleanupExpiredPasswordResets() {
        try {
            $sql = "DELETE FROM password_resets 
                    WHERE expires_at < NOW() 
                    OR (used = 1 AND used_at < DATE_SUB(NOW(), INTERVAL 7 DAY))";
            
            $stmt = $this->pdo->prepare($sql);
            $deleted = $stmt->execute();
            
            $count = $stmt->rowCount();
            error_log("Cleaned up $count expired/old password reset records");
            
            return $deleted;
        } catch (PDOException $e) {
            error_log("Error cleaning up expired password resets: " . $e->getMessage());
            return false;
        }
    }

    // ==================== UTILITY METHODS ==================== //

    public function markUsed($id) {
        $sql = "UPDATE user_otps SET used = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function markRegistrationOTPUsed($id) {
        $sql = "UPDATE registration_otps SET used = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $result = $stmt->execute([':id' => $id]);
            error_log("Marked registration OTP as used. ID: $id, Success: " . ($result ? "Yes" : "No"));
            return $result;
        } catch (PDOException $e) {
            error_log("Error marking OTP as used: " . $e->getMessage());
            return false;
        }
    }

    public function invalidateExistingOTPs($userId) {
        $sql = "UPDATE user_otps SET used = 1 
                WHERE user_id = :user_id AND used = 0";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':user_id' => $userId]);
    }

    public function invalidateExistingRegistrationOTPs($email) {
        $sql = "UPDATE registration_otps SET used = 1 
                WHERE email = :email AND used = 0";
        $stmt = $this->pdo->prepare($sql);
        
        try {
            $result = $stmt->execute([':email' => $email]);
            error_log("Invalidated existing OTPs for email: $email");
            return $result;
        } catch (PDOException $e) {
            error_log("Error invalidating existing OTPs: " . $e->getMessage());
            return false;
        }
    }
}
?>