<?php
/**
 * API Authentication endpoint for Better World Browser
 * Handles login requests and returns JSON with token
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse JSON input
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input || !isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username and password required']);
    exit;
}

$username = trim($input['username']);
$password = $input['password'];

try {
    $pdo = DatabaseConfig::getConnection();

    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND active = 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        exit;
    }

    // Generate a simple token (in production, use JWT)
    $token = bin2hex(random_bytes(32));

    echo json_encode([
        'success' => true,
        'userid' => $user['userid'],
        'username' => $user['username'],
        'role' => $user['role'],
        'token' => $token
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
