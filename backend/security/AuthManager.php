<?php
/**
 * AuthManager Class
 * 
 * Provides secure authentication and authorization functionality
 * including session management, password hashing, and role-based access control.
 * 
 * @author Manus AI
 * @version 1.0
 */

class AuthManager {
    private $db;
    private $sessionTimeout;
    private $maxLoginAttempts;
    private $lockoutDuration;
    private $logFile;
    
    public function __construct($database, $sessionTimeout = 3600, $maxLoginAttempts = 5, $lockoutDuration = 900) {
        $this->db = $database;
        $this->sessionTimeout = $sessionTimeout; // 1 hour default
        $this->maxLoginAttempts = $maxLoginAttempts;
        $this->lockoutDuration = $lockoutDuration; // 15 minutes default
        $this->logFile = __DIR__ . '/../logs/auth.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Configure secure session settings
        $this->configureSession();
    }
    
    /**
     * Configure secure session settings
     */
    private function configureSession() {
        // Prevent session fixation
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session configuration
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.gc_maxlifetime', $this->sessionTimeout);
            
            session_start();
        }
    }
    
    /**
     * Hash password securely
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }
    
    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Stored password hash
     * @return bool True if password matches
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if IP is locked out due to failed login attempts
     * 
     * @param string $ip IP address to check
     * @return bool True if locked out
     */
    public function isLockedOut($ip) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt 
            FROM login_attempts 
            WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        
        $stmt->execute([$ip, $this->lockoutDuration]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['attempts'] >= $this->maxLoginAttempts;
    }
    
    /**
     * Record failed login attempt
     * 
     * @param string $username Attempted username
     * @param string $ip IP address
     */
    private function recordFailedAttempt($username, $ip) {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (username, ip_address, attempt_time, success) 
            VALUES (?, ?, NOW(), 0)
        ");
        
        $stmt->execute([$username, $ip]);
        
        $this->logAuthEvent('FAILED_LOGIN', $username, $ip);
    }
    
    /**
     * Record successful login
     * 
     * @param string $username Username
     * @param string $ip IP address
     */
    private function recordSuccessfulLogin($username, $ip) {
        // Clear failed attempts for this IP
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        
        // Record successful login
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (username, ip_address, attempt_time, success) 
            VALUES (?, ?, NOW(), 1)
        ");
        
        $stmt->execute([$username, $ip]);
        
        $this->logAuthEvent('SUCCESSFUL_LOGIN', $username, $ip);
    }
    
    /**
     * Authenticate user
     * 
     * @param string $username Username or email
     * @param string $password Password
     * @return array|false User data on success, false on failure
     */
    public function authenticate($username, $password) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Check if IP is locked out
        if ($this->isLockedOut($ip)) {
            $this->logAuthEvent('LOCKOUT_ATTEMPT', $username, $ip);
            return false;
        }
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->recordFailedAttempt($username, $ip);
            return false;
        }
        
        // Get user from database
        $stmt = $this->db->prepare("
            SELECT id, username, email, password_hash, role, status, created_at, last_login 
            FROM users 
            WHERE (username = ? OR email = ?) AND status = 'active'
        ");
        
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $this->recordFailedAttempt($username, $ip);
            return false;
        }
        
        // Verify password
        if (!$this->verifyPassword($password, $user['password_hash'])) {
            $this->recordFailedAttempt($username, $ip);
            return false;
        }
        
        // Update last login
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        $this->recordSuccessfulLogin($username, $ip);
        
        // Remove password hash from returned data
        unset($user['password_hash']);
        
        return $user;
    }
    
    /**
     * Create secure session for authenticated user
     * 
     * @param array $user User data
     * @return string Session token
     */
    public function createSession($user) {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        
        // Generate secure session token
        $sessionToken = bin2hex(random_bytes(32));
        
        // Store session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Store session in database for additional security
        $stmt = $this->db->prepare("
            INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, created_at, last_activity) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
            session_token = VALUES(session_token),
            last_activity = NOW()
        ");
        
        $stmt->execute([
            $user['id'],
            $sessionToken,
            $_SESSION['ip_address'],
            $_SESSION['user_agent']
        ]);
        
        $this->logAuthEvent('SESSION_CREATED', $user['username'], $_SESSION['ip_address']);
        
        return $sessionToken;
    }
    
    /**
     * Validate current session
     * 
     * @return bool True if session is valid
     */
    public function validateSession() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > $this->sessionTimeout) {
            $this->destroySession();
            return false;
        }
        
        // Verify session token in database
        $stmt = $this->db->prepare("
            SELECT user_id FROM user_sessions 
            WHERE user_id = ? AND session_token = ? AND ip_address = ?
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['session_token'],
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        if (!$stmt->fetch()) {
            $this->destroySession();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        $stmt = $this->db->prepare("
            UPDATE user_sessions 
            SET last_activity = NOW() 
            WHERE user_id = ? AND session_token = ?
        ");
        
        $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
        
        return true;
    }
    
    /**
     * Check if user has required role
     * 
     * @param string $requiredRole Required role
     * @return bool True if user has required role
     */
    public function hasRole($requiredRole) {
        if (!$this->validateSession()) {
            return false;
        }
        
        $userRole = $_SESSION['role'] ?? '';
        
        // Define role hierarchy
        $roleHierarchy = [
            'subscriber' => 1,
            'contributor' => 2,
            'author' => 3,
            'editor' => 4,
            'admin' => 5,
            'super_admin' => 6
        ];
        
        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Get current user data
     * 
     * @return array|false User data or false if not authenticated
     */
    public function getCurrentUser() {
        if (!$this->validateSession()) {
            return false;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Destroy current session
     */
    public function destroySession() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
            // Remove session from database
            $stmt = $this->db->prepare("
                DELETE FROM user_sessions 
                WHERE user_id = ? AND session_token = ?
            ");
            
            $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
            
            $this->logAuthEvent('SESSION_DESTROYED', $_SESSION['username'] ?? 'unknown', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        }
        
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     * 
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Create new user
     * 
     * @param array $userData User data
     * @return int|false User ID on success, false on failure
     */
    public function createUser($userData) {
        // Validate required fields
        if (empty($userData['username']) || empty($userData['email']) || empty($userData['password'])) {
            return false;
        }
        
        // Check if username or email already exists
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users 
            WHERE username = ? OR email = ?
        ");
        
        $stmt->execute([$userData['username'], $userData['email']]);
        
        if ($stmt->fetchColumn() > 0) {
            return false; // User already exists
        }
        
        // Hash password
        $passwordHash = $this->hashPassword($userData['password']);
        
        // Insert user
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash, role, status, created_at) 
            VALUES (?, ?, ?, ?, 'active', NOW())
        ");
        
        $role = $userData['role'] ?? 'subscriber';
        
        if ($stmt->execute([$userData['username'], $userData['email'], $passwordHash, $role])) {
            $userId = $this->db->lastInsertId();
            $this->logAuthEvent('USER_CREATED', $userData['username'], $_SERVER['REMOTE_ADDR'] ?? 'system');
            return $userId;
        }
        
        return false;
    }
    
    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions() {
        $stmt = $this->db->prepare("
            DELETE FROM user_sessions 
            WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        
        $stmt->execute([$this->sessionTimeout]);
        
        // Also clean up old login attempts
        $stmt = $this->db->prepare("
            DELETE FROM login_attempts 
            WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        $stmt->execute();
    }
    
    /**
     * Log authentication events
     * 
     * @param string $event Event type
     * @param string $username Username
     * @param string $ip IP address
     * @param string $details Additional details
     */
    private function logAuthEvent($event, $username, $ip, $details = '') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'username' => $username,
            'ip' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
}
?>

