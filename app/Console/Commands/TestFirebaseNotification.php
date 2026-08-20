<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class TestFirebaseNotification extends Command
{
    protected $signature = 'firebase:test {deviceToken}';
    protected $description = 'Send a test push notification to verify Firebase setup';

    public function handle()
    {
        $deviceToken = $this->argument('deviceToken');
        
        $this->info('Sending test notification to device: ' . $deviceToken);

        $firebaseService = new FirebaseService();
        
        $result = $firebaseService->sendPushNotification(
            $deviceToken,
            'Test Notification',
            'This is a test notification from Laravel',
            ['test' => 'true', 'timestamp' => now()->toDateTimeString()]
        );

        if ($result) {
            $this->info('✓ Test notification sent successfully!');
        } else {
            $this->error('✗ Failed to send notification. Check logs.');
        }
    }
}
