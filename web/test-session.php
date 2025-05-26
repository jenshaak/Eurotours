<?php
echo "Testing session configuration...<br>";

// Test basic session
if (session_start()) {
    echo "✅ Session started successfully<br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session save path: " . session_save_path() . "<br>";
} else {
    echo "❌ Session failed to start<br>";
}

// Test Symfony session
try {
    require __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';
    
    $kernel = new AppKernel('dev', true);
    $kernel->boot();
    
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    echo "Request created<br>";
    
    if ($request->hasSession()) {
        echo "✅ Request has session support<br>";
    } else {
        echo "❌ Request has NO session support<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
