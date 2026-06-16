<?php

require_once 'vendor/autoload.php';

use App\Services\FirebaseService;

try {
    echo "Testing Firebase Service...\n";
    
    $firebase = new FirebaseService();
    
    // Test with a dummy device token (won't actually send)
    $testToken = "dummy-token-for-testing";
    $title = "Test Notification";
    $body = "This is a test to check Firebase setup";
    
    $result = $firebase->sendPushNotification($testToken, $title, $body, []);
    
    if ($result) {
        echo "✅ Firebase Service is working!\n";
    } else {
        echo "❌ Firebase Service failed!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "Check storage/logs/laravel.log for detailed logs\n";