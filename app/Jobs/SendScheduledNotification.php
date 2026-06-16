<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\DeviceToken;
use App\Models\AssignBase;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendScheduledNotification implements ShouldQueue
{
    use Queueable;

    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function handle(): void
    {
        try {
            // Check if notification is already sent
            if ($this->notification->is_sent) {
                Log::info('Notification already sent', ['notification_id' => $this->notification->id]);
                return;
            }

            // Get the assign base record for this notification
            $assignBase = AssignBase::find($this->notification->assign_base_id);
            
            if (!$assignBase) {
                Log::warning('Assign base not found', ['assign_base_id' => $this->notification->assign_base_id]);
                return;
            }

            // Get all employees assigned to the same customer (same customer_name and mobile)
            $relatedAssignments = AssignBase::where('customer_name', $assignBase->customer_name)
                ->where('mobile', $assignBase->mobile)
                ->get();

            if ($relatedAssignments->isEmpty()) {
                Log::warning('No related assignments found for customer', [
                    'customer_name' => $assignBase->customer_name,
                    'assign_base_id' => $this->notification->assign_base_id
                ]);
                return;
            }

            // Collect all employee IDs for this customer
            $employeeIds = $relatedAssignments->pluck('emp_id')->unique()->toArray();
            
            // Get device tokens for all employees assigned to this customer
            $deviceTokens = DeviceToken::whereIn('emp_id', $employeeIds)
                ->pluck('device_token')
                ->toArray();

            if (empty($deviceTokens)) {
                Log::warning('No device tokens found for customer employees', [
                    'customer_name' => $assignBase->customer_name,
                    'employee_ids' => $employeeIds,
                    'assign_base_id' => $this->notification->assign_base_id
                ]);
                return;
            }

            // Initialize Firebase service
            $firebaseService = new FirebaseService();
            
            // Prepare notification content
            $title = "Reminder";
            $body = "Mobile: " . $assignBase->mobile . "\n";
            $body .= "Date: " . $this->notification->notification_date->format('d/m/Y') . "\n";
            $body .= "Time: " . $this->notification->notification_time . "\n";
            $body .= "Remarks: " . $this->notification->remarks;
            
            $data = [
                'notification_id' => $this->notification->id,
                'assign_base_id' => $this->notification->assign_base_id,
                'customer_name' => $assignBase->customer_name,
                'mobile' => $assignBase->mobile,
                'notification_date' => $this->notification->notification_date->format('d/m/Y'),
                'notification_time' => $this->notification->notification_time,
                'remarks' => $this->notification->remarks,
                'type' => 'scheduled_notification'
            ];

            // Send to all employees working on this customer
            $result = $firebaseService->sendMulticastNotification($deviceTokens, $title, $body, $data);

            if ($result) {
                // Mark notification as sent
                $this->notification->update(['is_sent' => true]);
                
                Log::info('Scheduled notification sent to all customer employees', [
                    'notification_id' => $this->notification->id,
                    'assign_base_id' => $this->notification->assign_base_id,
                    'customer_name' => $assignBase->customer_name,
                    'employee_ids' => $employeeIds,
                    'device_count' => count($deviceTokens),
                    'assignment_count' => $relatedAssignments->count()
                ]);
            } else {
                Log::error('Failed to send scheduled notification to customer employees', [
                    'notification_id' => $this->notification->id,
                    'assign_base_id' => $this->notification->assign_base_id,
                    'customer_name' => $assignBase->customer_name,
                    'employee_ids' => $employeeIds
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error in SendScheduledNotification job', [
                'notification_id' => $this->notification->id,
                'assign_base_id' => $this->notification->assign_base_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
