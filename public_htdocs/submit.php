<?php
// Brain rot submission handler
// All submissions are anonymous

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once '../config/database.php';

try {
    // Validate categories selection
    if (!isset($_POST['categories']) || empty($_POST['categories'])) {
        throw new Exception('Please select at least one brain rot category');
    }

    $categories = $_POST['categories'];
    $validCategories = ['misinformation', 'toxic', 'clickbait', 'superficial', 'meme_overload', 'doomscrolling'];
    
    foreach ($categories as $category) {
        if (!in_array($category, $validCategories)) {
            throw new Exception('Invalid category selected');
        }
    }

    $pdo = DatabaseConfig::getConnection();

    // Gather content
    $textContent = null;
    $imageFilename = null;
    $imageMimeType = null;
    $hasText = false;
    $hasImage = false;

    // Text content
    if (!empty($_POST['text_content']) && trim($_POST['text_content']) !== '') {
        $textContent = trim($_POST['text_content']);
        if (strlen($textContent) > 10000) {
            throw new Exception('Text content is too long (max 10,000 characters)');
        }
        $hasText = true;
    }

    // File upload (image only - schema doesn't support video)
    if (isset($_FILES['image_content']) && $_FILES['image_content']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['image_content'];
        $fileMimeType = mime_content_type($uploadedFile['tmp_name']);

        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($fileMimeType, $allowedImageTypes)) {
            throw new Exception('Invalid file format. Please upload images (JPEG, PNG, GIF, WebP).');
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($uploadedFile['size'] > $maxSize) {
            throw new Exception('File is too large (max 10MB)');
        }

        // Generate unique filename
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $uniqueId = uniqid('', true);
        $imageFilename = 'img_' . $uniqueId . '.' . $extension;
        $imageMimeType = $fileMimeType;
        $hasImage = true;

        // Create upload directory
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetFile = $uploadDir . $imageFilename;
        if (!move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
            throw new Exception('Failed to save uploaded file');
        }
    }

    // Must have some content
    if (!$hasText && !$hasImage) {
        throw new Exception('Please provide either text content or an image');
    }

    // Determine submission type
    if ($hasText && $hasImage) {
        $submissionType = 'text'; // Store as text, image is supplementary
    } elseif ($hasImage) {
        $submissionType = 'image';
    } else {
        $submissionType = 'text';
    }

    // Insert submission (anonymous - no userid)
    $stmt = $pdo->prepare("
        INSERT INTO submissions (
            submission_type,
            content_text,
            image_filename,
            image_mime_type,
            categories
        ) VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $submissionType,
        $textContent,
        $imageFilename,
        $imageMimeType,
        json_encode($categories)
    ]);

    $submissionId = $pdo->lastInsertId();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Submission received! Thank you for helping identify brain rot content.',
        'submission_id' => $submissionId,
        'type' => $submissionType,
        'categories' => count($categories)
    ]);

} catch (Exception $e) {
    error_log("Submission error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
