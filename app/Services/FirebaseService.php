<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Exception;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        try {
            // Get credentials path from config, with fallback to default location
            $credentialsPath = config('services.firebase.credentials', base_path('google-services.json'));
            
            if (!file_exists($credentialsPath)) {
                throw new Exception('Firebase credentials file not found at: ' . $credentialsPath);
            }

            // Validate the credentials file content
            $credentialsContent = file_get_contents($credentialsPath);
            $credentialsData = json_decode($credentialsContent, true);
            
            if (!$credentialsData) {
                throw new Exception('Firebase credentials file is not valid JSON: ' . json_last_error_msg());
            }
            
            // Check required fields
            $requiredFields = ['type', 'project_id', 'client_email', 'private_key'];
            foreach ($requiredFields as $field) {
                if (empty($credentialsData[$field])) {
                    throw new Exception("Firebase credentials missing required field: {$field}");
                }
            }

            // Initialize Firebase Factory
            $factory = new Factory();
            
            // Check if there are environment overrides first
            $googleCredentialsEnv = env('GOOGLE_APPLICATION_CREDENTIALS');
            if ($googleCredentialsEnv && file_exists($googleCredentialsEnv)) {
                $factory = $factory->withServiceAccount($googleCredentialsEnv);
                \Log::info('Firebase initialized with GOOGLE_APPLICATION_CREDENTIALS: ' . $googleCredentialsEnv);
            } else {
                $factory = $factory->withServiceAccount($credentialsPath);
                \Log::info('Firebase initialized with service account file: ' . $credentialsPath);
            }
            
            $this->messaging = $factory->createMessaging();
            \Log::info('Firebase messaging service created successfully');
            
        } catch (Exception $e) {
            \Log::error('Firebase initialization error: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    public function sendPushNotification($deviceToken, $title, $body, $data = [])
    {
        try {
            if (!$this->messaging) {
                throw new Exception('Firebase not initialized');
            }

            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            
            return true;
        } catch (Exception $e) {
            \Log::error('Push notification error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendMulticastNotification($deviceTokens, $title, $body, $data = [])
    {
        try {
            if (!$this->messaging) {
                throw new Exception('Firebase not initialized');
            }

            if (empty($deviceTokens)) {
                throw new Exception('No device tokens provided');
            }

            $notification = Notification::create($title, $body);
            
            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->sendMulticast($message, $deviceTokens);
            
            return true;
        } catch (Exception $e) {
            \Log::error('Multicast notification error: ' . $e->getMessage());
            return false;
        }
    }
}
