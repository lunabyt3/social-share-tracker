<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Read the data sent by JS and convert into PHP associative array
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$platform = $input['platform'] ?? '';
$pageUrl = $input['page_url'] ?? '';


// Validate that the platform is supported.
$allowedPlatforms = [
    'facebook',
    'x',
    'whatsapp',
    'telegram',
    'email'
];

if (!in_array($platform, $allowedPlatforms, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid platform']);
    exit;
}


// Validate the URL format, length, and HTTP/HTTPS scheme.
if (
    !is_string($pageUrl) ||
    strlen($pageUrl) > 2048 ||
    !filter_var($pageUrl, FILTER_VALIDATE_URL) ||
    !in_array(strtolower((string) parse_url($pageUrl, PHP_URL_SCHEME)), ['http', 'https'], true)
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid page URL']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO social_shares (page_url, platform) VALUES (:page_url, :platform)'
    );

    $stmt->execute([
        ':page_url' => $pageUrl,
        ':platform' => $platform
    ]);

    echo json_encode(['success' => true, 'message' => 'Share click recorded']);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to record share click']);
}