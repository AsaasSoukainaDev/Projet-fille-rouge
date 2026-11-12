<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'API backend conforme au template du skill.',
    'routes' => [
        'GET /backend/api/api.php',
        'GET /api/themes.php',
        'GET /api/sessions.php'
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
