<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        
    }

    private function isUsernameTaken($username, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE username = :username";
        $params = [':username' => $username];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    private function isEmailTaken($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $params = [':email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // ---------------- AUTH ----------------
   
public function register($username, $email, $password, $profilePic = null, $phone = '', $address = '', $role = 'user') {
    try {
        if ($this->isUsernameTaken($username)) return false;
        if ($this->isEmailTaken($email)) return false;

        $sql = "INSERT INTO users (username, email, password, profile_pic, phone, address, role, status) 
                VALUES (:username, :email, :password, :profile_pic, :phone, :address, :role, 'Active')";
        
        $stmt = $this->pdo->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':profile_pic' => $profilePic,
            ':phone' => $phone,
            ':address' => $address,
            ':role' => $role
        ]);
    } catch (PDOException $e) {
        error_log("Register error: " . $e->getMessage());
        return false;
    }
}
    

    public function login($email, $password) {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ✅ Check password
        if (password_verify($password, $user['password'])) {

            // ✅ Corrected: Use 'status' instead of 'userstatus'
            if ($user['status'] === 'Blocked') {
                return 'blocked';
            }
            if ($user['status'] === 'Inactive') {
                return 'inactive';
            }
            if ($user['status'] === 'Deactivated') {
                return 'deactivated';
            }

            return $user; // ✅ Return full user data if status is Active
        }
    }

    return false;
}


    // ==================== LOGIN ATTEMPT TRACKING ====================

    /*** Record a failed login attempt*/
    public function recordFailedAttempt($identifier, $ipAddress, $userAgent = '') {
        try {
            $sql = "INSERT INTO login_attempts (identifier, ip_address, user_agent, attempt_time) 
                    VALUES (:identifier, :ip_address, :user_agent, NOW())";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':identifier' => $identifier,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("Record failed attempt error: " . $e->getMessage());
            return false;
        }
    }

    /** * Get failed login attempt count within timeframe (default: 30 minutes) */
    public function getFailedAttemptCount($identifier, $minutes = 30) {
        try {
            $sql = "SELECT COUNT(*) as attempt_count 
                    FROM login_attempts 
                    WHERE identifier = :identifier 
                    AND attempt_time > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':minutes' => $minutes
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['attempt_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Get failed attempt count error: " . $e->getMessage());
            return 0;
        }
    }

    /*** Check if account is locked*/
    public function isAccountLocked($identifier) {
        try {
            $sql = "SELECT locked_until 
                    FROM account_lockouts 
                    WHERE identifier = :identifier 
                    AND locked_until > NOW()";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':identifier' => $identifier]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'locked' => true,
                    'locked_until' => $result['locked_until']
                ];
            }
            return ['locked' => false];
        } catch (PDOException $e) {
            error_log("Check account lock error: " . $e->getMessage());
            return ['locked' => false];
        }
    }

    /*** Lock account for specified minutes*/
    public function lockAccount($identifier, $minutes = 15) {
        try {
            $sql = "INSERT INTO account_lockouts (identifier, locked_until, attempt_count) 
                    VALUES (:identifier, DATE_ADD(NOW(), INTERVAL :minutes MINUTE), 1)
                    ON DUPLICATE KEY UPDATE 
                        locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE),
                        attempt_count = attempt_count + 1";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':identifier' => $identifier,
                ':minutes' => $minutes
            ]);
        } catch (PDOException $e) {
            error_log("Lock account error: " . $e->getMessage());
            return false;
        }
    }

    /*** Clear failed login attempts (on successful login)*/
    public function clearFailedAttempts($identifier) {
        try {
            // Clear login attempts
            $sql1 = "DELETE FROM login_attempts WHERE identifier = :identifier";
            $stmt1 = $this->pdo->prepare($sql1);
            $stmt1->execute([':identifier' => $identifier]);
            
            // Clear lockout
            $sql2 = "DELETE FROM account_lockouts WHERE identifier = :identifier";
            $stmt2 = $this->pdo->prepare($sql2);
            $stmt2->execute([':identifier' => $identifier]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Clear failed attempts error: " . $e->getMessage());
            return false;
        }
    }

    /*** Clean up old login attempts (run periodically, e.g., via cron)*/
    public function cleanupOldAttempts($days = 7) {
        try {
            $sql = "DELETE FROM login_attempts 
                    WHERE attempt_time < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':days' => $days]);
        } catch (PDOException $e) {
            error_log("Cleanup old attempts error: " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword($email, $newPassword) {
    try {
        // Hash the new password using PASSWORD_DEFAULT (consistent with other methods)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // First verify the user exists
        $checkQuery = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $this->pdo->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            error_log("updatePassword: User not found for email: $email");
            return false;
        }
        
        // Update the password
        $query = "UPDATE users 
                  SET password = :password 
                  WHERE email = :email";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        
        $result = $stmt->execute();
        
        if ($result && $stmt->rowCount() > 0) {
            error_log("✅ Password updated successfully for: $email");
            return true;
        } else {
            error_log("❌ Failed to update password for: $email (no rows affected)");
            return false;
        }
        
    } catch (PDOException $e) {
        error_log("updatePassword Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}



    // ---------------- USER FUNCTIONS ----------------
    public function changePassword($id, $currentPassword, $newPassword) {
        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return "user_not_found";
            }

            if (!password_verify($currentPassword, $user['password'])) {
                return "incorrect_current_password";
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            return $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return false;
        }
    }

    // Fixed User class updateProfile method
public function updateProfile($id, $username, $email, $phone = null, $address = null, $profilePic = null) {
    try {
        // ✅ Prevent duplicates
        if ($this->isUsernameTaken($username, $id)) {
            return 'username_taken';
        }

        if ($this->isEmailTaken($email, $id)) {
            return 'email_taken';
        }

        // ✅ Get current user data to preserve existing profile pic if needed
        $currentUser = $this->getUserById($id);
        
        // ✅ Handle profile picture logic
        $finalProfilePic = $currentUser['profile_pic']; // Keep existing by default
        
        if ($profilePic) {
            // New profile picture uploaded
            $finalProfilePic = $profilePic;
        } elseif (isset($_POST['remove_profile_pic']) && $_POST['remove_profile_pic'] == '1') {
            // User wants to remove profile picture
            $finalProfilePic = null;
            
            // Delete old file if it exists
            if (!empty($currentUser['profile_pic'])) {
                $oldFilePath = 'public/uploads/' . $currentUser['profile_pic'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
        }

        // ✅ Always include profile_pic in update to handle removal
        $sql = "UPDATE users 
                SET username = :username, 
                    email = :email, 
                    phone = :phone, 
                    address = :address,
                    profile_pic = :profile_pic
                WHERE id = :id";
                
        $params = [
            ':username'    => $username,
            ':email'       => $email,
            ':phone'       => !empty($phone) ? $phone : null,
            ':address'     => !empty($address) ? $address : null,
            ':profile_pic' => $finalProfilePic,
            ':id'          => $id
        ];

        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($params);

        if ($success) {
            // ✅ Update session with latest values
            $_SESSION['username'] = $username;
            $_SESSION['email']    = $email;
            $_SESSION['phone']    = !empty($phone) ? $phone : '';
            $_SESSION['address']  = !empty($address) ? $address : '';
            $_SESSION['profile_pic'] = $finalProfilePic; // Use consistent key
            $_SESSION['avatar'] = $finalProfilePic; // Keep both for compatibility
        }

        return $success;

    } catch (PDOException $e) {
        error_log("Update profile error: " . $e->getMessage());
        return false;
    }
}

// Fixed getUserById method to ensure proper data return
public function getUserById($id) {
    try {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Ensure defaults
            $user['role'] = $user['role'] ?? 'user';
            $user['phone'] = $user['phone'] ?? '';
            $user['address'] = $user['address'] ?? '';
            $user['profile_pic'] = $user['profile_pic'] ?? ''; // Ensure this is set
        }

        return $user;
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return false;
    }
}
    

    // ---------------- ADMIN FUNCTIONS ----------------
    
    
    

    public function getUserByUsername($username) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user by username error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user by email error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserProfile($userId, $username, $email, $firstName = '', $lastName = '', $phone = '') {
        try {
            $query = "UPDATE users SET 
                        username = :username,
                        email = :email,
                        first_name = :first_name,
                        last_name = :last_name,
                        phone = :phone,
                        updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':id', $userId);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::updateUserProfile() - " . $e->getMessage());
            return false;
        }
    }

    public function updateUserPassword($userId, $hashedPassword) {
        try {
            $query = "UPDATE users 
                      SET password = :password, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("User::updateUserPassword() - " . $e->getMessage());
            return false;
        }
    }

    // Add this method to your User class
    // Enhanced updateCustomer method with detailed debugging
public function updateCustomer($id, $username, $email, $phone, $address, $status) {
    try {
        error_log("=== UPDATE CUSTOMER IN DATABASE ===");
        error_log("ID: " . $id);
        error_log("Username: " . $username);
        error_log("Email: " . $email);
        error_log("Phone: " . $phone);
        error_log("Address: " . $address);
        error_log("Status: " . $status);
        
        // First, let's check if the customer exists and get current data
        $checkStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $checkStmt->execute([$id]);
        $currentCustomer = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentCustomer) {
            error_log("ERROR: Customer with ID $id not found");
            return false;
        }
        
        error_log("Current customer data: " . print_r($currentCustomer, true));
        
        // Check for duplicates (excluding current user)
        if ($this->isUsernameTaken($username, $id)) {
            error_log("ERROR: Username already taken");
            return 'username_taken';
        }

        if ($this->isEmailTaken($email, $id)) {
            error_log("ERROR: Email already taken");
            return 'email_taken';
        }
        
        // Let's first try to see what columns exist in your users table
        $columnsStmt = $this->pdo->prepare("DESCRIBE users");
        $columnsStmt->execute();
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Available columns in users table:");
        foreach ($columns as $column) {
            error_log("- " . $column['Field'] . " (" . $column['Type'] . ")");
        }
        
        // Validate status against your enum values
        $validStatuses = ['Active', 'Inactive'];
        if (!in_array($status, $validStatuses)) {
            error_log("WARNING: Invalid status '$status', defaulting to 'Active'");
            $status = 'Active';
        }
        
        // Update query without updated_at since your table doesn't have it
        $sql = "UPDATE users 
                SET username = ?, email = ?, phone = ?, address = ?, status = ?
                WHERE id = ?";
        
        error_log("SQL Query: " . $sql);
        
        $stmt = $this->pdo->prepare($sql);
        
        if (!$stmt) {
            error_log("ERROR: Failed to prepare statement");
            error_log("PDO Error Info: " . print_r($this->pdo->errorInfo(), true));
            return false;
        }
        
        $params = [$username, $email, $phone, $address, $status, $id];
        error_log("Parameters: " . print_r($params, true));
        
        // Execute with error checking
        $result = $stmt->execute($params);
        
        if (!$result) {
            error_log("ERROR: SQL execution failed");
            error_log("Statement Error Info: " . print_r($stmt->errorInfo(), true));
            error_log("PDO Error Info: " . print_r($this->pdo->errorInfo(), true));
            return false;
        }
        
        $rowsAffected = $stmt->rowCount();
        error_log("Rows affected: " . $rowsAffected);
        
        if ($rowsAffected === 0) {
            error_log("WARNING: No rows were updated - customer might not exist or data unchanged");
            // Let's check if the data is actually the same
            $newCheckStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $newCheckStmt->execute([$id]);
            $afterUpdate = $newCheckStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Data after update attempt: " . print_r($afterUpdate, true));
            
            // If data is the same, it's still a "success"
            return true;
        }
        
        error_log("SUCCESS: Customer updated successfully");
        return true;
        
    } catch (PDOException $e) {
        error_log("PDO EXCEPTION in updateCustomer:");
        error_log("Message: " . $e->getMessage());
        error_log("Code: " . $e->getCode());
        error_log("File: " . $e->getFile());
        error_log("Line: " . $e->getLine());
        error_log("Trace: " . $e->getTraceAsString());
        return false;
    } catch (Exception $e) {
        error_log("GENERAL EXCEPTION in updateCustomer:");
        error_log("Message: " . $e->getMessage());
        error_log("Code: " . $e->getCode());
        error_log("File: " . $e->getFile());
        error_log("Line: " . $e->getLine());
        return false;
    }
}

    
   
    
   
    
public function updateStatus($id, $status) {
    try {
        // Normalize status values to match your database exactly
        $status = strtolower(trim($status));
        
        // Map status values to exactly what your database expects
        $statusMap = [
            'active' => 'Active',
            'inactive' => 'Inactive', 
            'deactivated' => 'Deactivated',  // Added Deactivated status
            'blocked' => 'Blocked',
            'block' => 'Blocked'  // Handle legacy 'block' input
        ]; 
        
        // Validate and convert status
        if (!array_key_exists($status, $statusMap)) {
            error_log("Invalid status value: " . $status);
            error_log("Valid statuses are: " . implode(', ', array_keys($statusMap)));
            return false;
        }
        
        $dbStatus = $statusMap[$status];
        
        error_log("🔄 Updating user ID {$id} status from '{$status}' to '{$dbStatus}'");
        
        $stmt = $this->pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $result = $stmt->execute([$dbStatus, $id]);
        
        if ($result) {
            error_log("✅ Status update successful for user ID {$id}");
        } else {
            error_log("❌ Status update failed for user ID {$id}");
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Update Status Error: " . $e->getMessage());
        return false;
    }
}

    
    public function deleteCustomer($id) {
        try {
            // Check if customer has orders (optional - you might want to prevent deletion)
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
            $checkStmt->execute([$id]);
            $orderCount = $checkStmt->fetchColumn();
            
            if ($orderCount > 0) {
                // Instead of deleting, you might want to just deactivate
                // return $this->updateStatus($id, 'inactive');
                // Or allow deletion anyway - uncomment the next lines:
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete Customer Error: " . $e->getMessage());
            return false;
        }
    }


    public function getAllUsers() {
        try {
            $sql = "SELECT u.*, 
                           COUNT(o.id) as order_count,
                           COALESCE(SUM(o.total_amount), 0.00) as total_spent
                    FROM users u
                    LEFT JOIN orders o ON u.id = o.user_id
                    GROUP BY u.id
                    ORDER BY u.created_at DESC";
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($users as &$user) {
                if (!isset($user['role']) || empty($user['role'])) {
                    $user['role'] = 'user';
                }
                $user['phone'] = $user['phone'] ?? '';
                $user['address'] = $user['address'] ?? '';
                $user['order_count'] = $user['order_count'] ?? 0;
                $user['total_spent'] = $user['total_spent'] ?? 0.00;
            }
            return $users;
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserStatus($id, $status) {
        try {
            $sql = "UPDATE users SET status = :status WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':status' => $status, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Update user status error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserRole($id, $role) {
        try {
            $sql = "UPDATE users SET role = :role WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':role' => $role, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Update user role error: " . $e->getMessage());
            return false;
        }
    }
}
?>
