<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-scheduled-whatsapp-notifications')->everyMinute();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test:firebase', function () {
    $this->info('=== Firebase Laravel Test ===');
    
    // Test the service directly
    try {
        $firebaseService = new \App\Services\FirebaseService();
        $this->info('✅ Firebase service instantiated successfully');
        
        // Test reflection to see the messaging property
        $reflection = new ReflectionClass($firebaseService);
        $messagingProperty = $reflection->getProperty('messaging');
        $messagingProperty->setAccessible(true);
        $messaging = $messagingProperty->getValue($firebaseService);
        
        if ($messaging) {
            $this->info('✅ Firebase messaging is initialized: ' . get_class($messaging));
        } else {
            $this->error('❌ Firebase messaging is null');
        }
        
        // Test a notification
        $testResult = $firebaseService->sendPushNotification(
            'test-token',
            'Test Title',
            'Test Body',
            []
        );
        
        if ($testResult) {
            $this->info('✅ Test notification sent successfully');
        } else {
            $this->error('❌ Test notification failed');
        }
        
    } catch (Exception $e) {
        $this->error('❌ Error: ' . $e->getMessage());
        $this->error('Stack trace:');
        $this->error($e->getTraceAsString());
    }
    
    // Check environment variables
    $this->info('Environment variables:');
    $envVars = ['GOOGLE_APPLICATION_CREDENTIALS', 'FIREBASE_CREDENTIALS'];
    foreach ($envVars as $var) {
        $value = env($var);
        $this->line("- $var: " . ($value ? 'SET' : 'NOT SET'));
    }
    
    // Check file path resolution
    $credentialsPath = base_path('google-services.json');
    $this->info("Credentials path: $credentialsPath");
    $this->info("File exists: " . (file_exists($credentialsPath) ? 'YES' : 'NO'));
    
})->purpose('Test Firebase service within Laravel context');
