<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private static $instance = null;
    private $pdo;
    private $user = null;
    
    private function __construct() {
        $this->pdo = DatabaseConfig::getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->loadUserFromSession();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadUserFromSession() {
        if (isset($_SESSION['userid'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE userid = ? AND active = 1");
            $stmt->execute([$_SESSION['userid']]);
            $this->user = $stmt->fetch();
        }
    }
    
    public function register($username, $email, $password) {
        // Validate
        if (strlen($username) < 3) {
            return ['success' => false, 'error' => 'Username must be at least 3 characters'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters'];
        }
        
        // Check if username/email exists
        $stmt = $this->pdo->prepare("SELECT userid FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Username or email already exists'];
        }
        
        // Create user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$username, $email, $hash]);
        
        $userid = $this->pdo->lastInsertId();
        $this->loginById($userid);
        
        return ['success' => true, 'userid' => $userid];
    }
    
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid username or password'];
        }
        
        $this->loginById($user['userid']);
        return ['success' => true, 'user' => $this->user];
    }
    
    private function loginById($userid) {
        $_SESSION['userid'] = $userid;
        $this->loadUserFromSession();
    }
    
    public function logout() {
        unset($_SESSION['userid']);
        $this->user = null;
        session_destroy();
    }
    
    public function isLoggedIn() {
        return $this->user !== null;
    }
    
    public function isAdmin() {
        return $this->user && $this->user['role'] === 'admin';
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function getUserId() {
        return $this->user ? $this->user['userid'] : null;
    }
    
    public function requireLogin($redirect = '/auth/login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirect");
            exit;
        }
    }
    
    public function requireAdmin($redirect = '/auth/login.php') {
        if (!$this->isAdmin()) {
            header("Location: $redirect?error=admin_required");
            exit;
        }
    }
    
    // Create admin user if none exists
    public function ensureAdminExists($password = 'BrainRotAdmin2024!') {
        $stmt = $this->pdo->prepare("SELECT userid FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES ('admin', 'admin@brainrot.local', ?, 'admin')");
            $stmt->execute([$hash]);
        }
    }
}
