<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo json_encode([
    'message' => 'API тест работает!',
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => 'success',
    'php_version' => PHP_VERSION
]); 