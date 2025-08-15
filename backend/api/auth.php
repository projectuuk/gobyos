<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../security/AuthManager.php';
require_once '../security/InputValidator.php';

// Rate limiting for auth endpoints
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!InputValidator::checkRateLimit($clientIP . '_auth', 10, 300)) { // 10 attempts per 5 minutes
    http_response_code(429);
    echo json_encode(['message' => 'Too many authentication attempts. Please try again later.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$auth = new AuthManager($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON data']);
            exit;
        }
        
        switch ($action) {
            case 'login':
                // Validate input
                $username = isset($data['username']) ? InputValidator::sanitizeString($data['username']) : '';
                $password = isset($data['password']) ? $data['password'] : '';
                
                if (empty($username) || empty($password)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Username and password are required']);
                    break;
                }
                
                // Validate username format (email or username)
                if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $username = InputValidator::validateEmail($username);
                } else {
                    // Validate as username
                    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
                        http_response_code(400);
                        echo json_encode(['message' => 'Invalid username format']);
                        break;
                    }
                }
                
                if ($username === false) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Invalid username or email format']);
                    break;
                }
                
                // Attempt authentication
                $user = $auth->authenticate($username, $password);
                
                if ($user) {
                    // Create session
                    $sessionToken = $auth->createSession($user);
                    
                    // Generate CSRF token
                    $csrfToken = $auth->generateCSRFToken();
                    
                    http_response_code(200);
                    echo json_encode([
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $user['id'],
                            'username' => $user['username'],
                            'email' => $user['email'],
                            'role' => $user['role']
                        ],
                        'session_token' => $sessionToken,
                        'csrf_token' => $csrfToken
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['message' => 'Invalid credentials or account locked']);
                }
                break;
                
            case 'register':
                // Validate input
                $username = isset($data['username']) ? InputValidator::sanitizeString($data['username']) : '';
                $email = isset($data['email']) ? InputValidator::validateEmail($data['email']) : false;
                $password = isset($data['password']) ? $data['password'] : '';
                $confirmPassword = isset($data['confirm_password']) ? $data['confirm_password'] : '';
                
                // Validation checks
                if (empty($username) || empty($email) || empty($password)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Username, email, and password are required']);
                    break;
                }
                
                if ($email === false) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Invalid email format']);
                    break;
                }
                
                // Validate username format
                if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Username must be 3-30 characters and contain only letters, numbers, and underscores']);
                    break;
                }
                
                // Validate password strength
                if (strlen($password) < 8) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Password must be at least 8 characters long']);
                    break;
                }
                
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character']);
                    break;
                }
                
                if ($password !== $confirmPassword) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Passwords do not match']);
                    break;
                }
                
                // Create user
                $userData = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'subscriber' // Default role
                ];
                
                $userId = $auth->createUser($userData);
                
                if ($userId) {
                    http_response_code(201);
                    echo json_encode([
                        'message' => 'User registered successfully',
                        'user_id' => $userId
                    ]);
                } else {
                    http_response_code(409);
                    echo json_encode(['message' => 'Username or email already exists']);
                }
                break;
                
            case 'logout':
                // Validate session first
                if ($auth->validateSession()) {
                    $auth->destroySession();
                    http_response_code(200);
                    echo json_encode(['message' => 'Logged out successfully']);
                } else {
                    http_response_code(401);
                    echo json_encode(['message' => 'No active session found']);
                }
                break;
                
            case 'validate':
                // Validate current session
                if ($auth->validateSession()) {
                    $user = $auth->getCurrentUser();
                    $csrfToken = $auth->generateCSRFToken();
                    
                    http_response_code(200);
                    echo json_encode([
                        'valid' => true,
                        'user' => $user,
                        'csrf_token' => $csrfToken
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(['valid' => false, 'message' => 'Session invalid or expired']);
                }
                break;
                
            case 'change_password':
                // Validate session
                if (!$auth->validateSession()) {
                    http_response_code(401);
                    echo json_encode(['message' => 'Authentication required']);
                    break;
                }
                
                // Validate CSRF token
                $csrfToken = isset($data['csrf_token']) ? $data['csrf_token'] : '';
                if (!$auth->validateCSRFToken($csrfToken)) {
                    http_response_code(403);
                    echo json_encode(['message' => 'Invalid CSRF token']);
                    break;
                }
                
                $currentPassword = isset($data['current_password']) ? $data['current_password'] : '';
                $newPassword = isset($data['new_password']) ? $data['new_password'] : '';
                $confirmPassword = isset($data['confirm_password']) ? $data['confirm_password'] : '';
                
                if (empty($currentPassword) || empty($newPassword)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Current password and new password are required']);
                    break;
                }
                
                // Validate new password strength
                if (strlen($newPassword) < 8) {
                    http_response_code(400);
                    echo json_encode(['message' => 'New password must be at least 8 characters long']);
                    break;
                }
                
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $newPassword)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'New password must contain at least one uppercase letter, one lowercase letter, one number, and one special character']);
                    break;
                }
                
                if ($newPassword !== $confirmPassword) {
                    http_response_code(400);
                    echo json_encode(['message' => 'New passwords do not match']);
                    break;
                }
                
                // Get current user
                $user = $auth->getCurrentUser();
                
                // Verify current password
                $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $currentHash = $stmt->fetchColumn();
                
                if (!$auth->verifyPassword($currentPassword, $currentHash)) {
                    http_response_code(400);
                    echo json_encode(['message' => 'Current password is incorrect']);
                    break;
                }
                
                // Update password
                $newHash = $auth->hashPassword($newPassword);
                $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$newHash, $user['id']])) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Password changed successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Failed to update password']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['message' => 'Invalid action']);
                break;
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
        break;
}

// Clean up expired sessions periodically (1% chance)
if (rand(1, 100) === 1) {
    $auth->cleanupExpiredSessions();
}
?>

