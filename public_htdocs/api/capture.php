<?php
/**
 * API Capture endpoint for Better World Browser
 * Receives brain rot content captures and stores them
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

// Verify authorization (simple token check)
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authorization required']);
    exit;
}
$token = $matches[1];

// In production, validate token against database
// For now, just check it's not empty
if (strlen($token) < 10) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Extract capture data
$userid = $input['userid'] ?? null;
$html = $input['html'] ?? '';
$text = $input['text'] ?? '';
$url = $input['url'] ?? '';
$category = $input['category'] ?? 'unknown';
$timestamp = $input['timestamp'] ?? date('c');
$source = $input['source'] ?? 'browser';

// Validate required fields
if (!$userid || (!$html && !$text)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $pdo = DatabaseConfig::getConnection();

    // Store capture as a submission
    $stmt = $pdo->prepare("
        INSERT INTO submissions (
            submission_type,
            content_text,
            categories,
            session_hash
        ) VALUES (
            'text',
            ?,
            ?,
            ?
        )
    ");

    // Combine text and metadata for content
    $content = json_encode([
        'text' => $text,
        'url' => $url,
        'source' => $source,
        'captured_at' => $timestamp,
        'html_length' => strlen($html)
    ], JSON_UNESCAPED_UNICODE);

    // Store categories as JSON array
    $categories = json_encode([$category]);

    // Generate session hash from userid and source
    $sessionHash = hash('sha256', $userid . '_' . $source . '_' . date('Y-m-d'));

    $stmt->execute([$content, $categories, $sessionHash]);

    $captureId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'capture_id' => $captureId,
        'message' => 'Content captured successfully'
    ]);

} catch (Exception $e) {
    error_log('Capture error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save capture']);
}
