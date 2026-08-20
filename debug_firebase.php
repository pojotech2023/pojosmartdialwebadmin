<?php

require_once 'vendor/autoload.php';

echo "=== Firebase Debug Script ===\n\n";

// Check if file exists
$credentialsPath = __DIR__ . '/google-services.json';
echo "1. Credentials file path: " . $credentialsPath . "\n";
echo "   File exists: " . (file_exists($credentialsPath) ? "YES" : "NO") . "\n\n";

// Check file contents
if (file_exists($credentialsPath)) {
    $contents = file_get_contents($credentialsPath);
    $data = json_decode($contents, true);
    
    if ($data) {
        echo "2. JSON structure is valid\n";
        echo "   Required fields check:\n";
        echo "   - type: " . (isset($data['type']) && !empty($data['type']) ? "✅ " . $data['type'] : "❌ missing") . "\n";
        echo "   - project_id: " . (isset($data['project_id']) && !empty($data['project_id']) ? "✅ " . $data['project_id'] : "❌ missing") . "\n";
        echo "   - client_email: " . (isset($data['client_email']) && !empty($data['client_email']) ? "✅ " . $data['client_email'] : "❌ missing") . "\n";
        echo "   - private_key: " . (isset($data['private_key']) && !empty($data['private_key']) ? "✅ present (not shown)" : "❌ missing") . "\n";
        echo "\n";
    } else {
        echo "2. ❌ JSON is invalid\n\n";
    }
}

// Check if there are environment variables that might override
echo "3. Environment variables check:\n";
$envVars = [
    'GOOGLE_APPLICATION_CREDENTIALS',
    'FIREBASE_CREDENTIALS',
    'FIREBASE_PROJECT_ID',
    'FIREBASE_CLIENT_EMAIL',
    'FIREBASE_PRIVATE_KEY'
];

foreach ($envVars as $var) {
    $value = getenv($var);
    echo "   - $var: " . ($value ? "SET" : "NOT SET") . "\n";
}

echo "\n4. Testing Firebase Factory directly:\n";
try {
    $factory = new \Kreait\Firebase\Factory();
    
    // First try with service account file
    if (file_exists($credentialsPath)) {
        $factory = $factory->withServiceAccount($credentialsPath);
        echo "   ✅ Factory created with service account file\n";
        
        // Try to create messaging
        $messaging = $factory->createMessaging();
        echo "   ✅ Messaging service created successfully\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== End Debug ===\n";