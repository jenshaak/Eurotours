<?php
// Simple test webhook endpoint
header('Content-Type: application/json');
http_response_code(200);

// Log the request for debugging
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input')
];

file_put_contents(__DIR__ . '/webhook-test.log', json_encode($logData) . "\n", FILE_APPEND);

echo json_encode(['status' => 'success', 'message' => 'Webhook test endpoint working']);
?> 