<?php
// Test script to simulate CoinRemitter webhook calls
// Usage: http://localhost:8000/test-crypto-webhook.php

// Simulate a webhook payload from CoinRemitter
$webhookData = [
    'invoice_id' => 'test_invoice_123',
    'status' => 'paid',
    'amount' => '0.001',
    'currency' => 'BTC',
    'usd_amount' => '50.00',
    'transaction_id' => 'test_tx_456',
    'confirmations' => 3
];

// Send POST request to our webhook endpoint
$url = 'http://localhost:8000/app.php/crypto-payment/webhook';
$postData = json_encode($webhookData);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h2>Webhook Test Results</h2>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h3>Test Data Sent:</h3>";
echo "<pre>" . htmlspecialchars(json_encode($webhookData, JSON_PRETTY_PRINT)) . "</pre>";
?> 