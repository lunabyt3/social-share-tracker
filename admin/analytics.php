<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// Get the URL query parameters
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-6 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$platform = $_GET['platform'] ?? 'all';

$allowedPlatforms = [
    'all',
    'facebook',
    'x',
    'whatsapp',
    'telegram',
    'email'
];


// Do validation: date, platform
$datePattern = '/^\d{4}-\d{2}-\d{2}$/';

if (
    !preg_match($datePattern, $startDate) ||
    !preg_match($datePattern, $endDate) ||
    $startDate > $endDate ||
    !in_array($platform, $allowedPlatforms, true)
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid filters']);
    exit;
}

// Filtering
try {
    // By date
    $where = 'created_at >= :start_date
              AND created_at < DATE_ADD(:end_date, INTERVAL 1 DAY)';

    $params = [
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ];

    // By platform
    if ($platform !== 'all') {
        $where .= ' AND platform = :platform';
        $params[':platform'] = $platform;
    }

    // BarChart (By Platform): Check and get the number of clicks for each platform
    $stmt = $pdo->prepare(
        "SELECT platform, COUNT(*) AS total
         FROM social_shares
         WHERE $where
         GROUP BY platform
         ORDER BY total DESC"
    );
    $stmt->execute($params);
    $platformStats = $stmt->fetchAll();
    error_log('Analytics params: ' . json_encode($params));
    error_log('Platform stats: ' . json_encode($platformStats));


    // LineChart (Daily): Check and get the number of clicks for each date
    $stmt = $pdo->prepare(
        "SELECT DATE(created_at) AS share_date, COUNT(*) AS total
         FROM social_shares
         WHERE $where
         GROUP BY DATE(created_at)
         ORDER BY share_date ASC"
    );
    $stmt->execute($params);
    $dailyStats = $stmt->fetchAll();

    // Return response
    echo json_encode([
        'platform_stats' => $platformStats,
        'daily_stats' => $dailyStats
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load analytics']);
}