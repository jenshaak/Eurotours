<?php
echo "<h1>🔍 Profiler Access Helper</h1>";

// Get the latest debug token from our test
$testUrl = "http://localhost:8000/test-app-minimal.php";
$response = file_get_contents($testUrl);

// Extract debug token from the response
if (preg_match('/x-debug-token-link.*?([a-f0-9]{6})/', $response, $matches)) {
    $debugToken = $matches[1];
    echo "<p>Latest debug token: <strong>$debugToken</strong></p>";
    
    $profilerUrl = "http://localhost:8000/_profiler/$debugToken";
    echo "<p>Profiler URL: <a href='$profilerUrl' target='_blank'>$profilerUrl</a></p>";
    
    // Try to get exception details from profiler
    $exceptionUrl = "http://localhost:8000/_profiler/$debugToken?panel=exception";
    echo "<p>Exception details: <a href='$exceptionUrl' target='_blank'>$exceptionUrl</a></p>";
    
} else {
    echo "<p>❌ Could not extract debug token</p>";
}

echo "<h2>Manual Access</h2>";
echo "<p>You can also manually access:</p>";
echo "<ul>";
echo "<li><a href='http://localhost:8000/_profiler' target='_blank'>http://localhost:8000/_profiler</a> - Profiler home</li>";
echo "<li><a href='http://localhost:8000/test-app-minimal.php' target='_blank'>http://localhost:8000/test-app-minimal.php</a> - Run test again</li>";
echo "</ul>";
?> 