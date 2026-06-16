<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Jobs\SendScheduledNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessScheduledNotifications extends Command
{
    protected $signature = 'notifications:process-scheduled';
    protected $description = 'Process and send scheduled notifications that are due';

    public function handle()
    {
        $this->info('Processing scheduled notifications...');

        // Get current date and time
        $now = Carbon::now();
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i');

        // Find notifications that are due (date is today or past, time has passed, and not sent yet)
        $dueNotifications = Notification::where('is_sent', false)
            ->where(function($query) use ($currentDate, $currentTime) {
                $query->where('notification_date', '<', $currentDate)
                      ->orWhere(function($q) use ($currentDate, $currentTime) {
                          $q->where('notification_date', '=', $currentDate)
                            ->where('notification_time', '<=', $currentTime);
                      });
            })
            ->with(['employee', 'assignBase'])
            ->get();

        if ($dueNotifications->isEmpty()) {
            $this->info('No due notifications found.');
            return;
        }

        $this->info("Found {$dueNotifications->count()} due notification(s).");

        foreach ($dueNotifications as $notification) {
            try {
                // Dispatch the job to send notification
                SendScheduledNotification::dispatch($notification);
                
                $this->line("Queued notification ID: {$notification->id} for employee ID: {$notification->emp_id}");
                
                Log::info('Notification queued for processing', [
                    'notification_id' => $notification->id,
                    'emp_id' => $notification->emp_id,
                    'scheduled_date' => $notification->notification_date,
                    'scheduled_time' => $notification->notification_time
                ]);
                
            } catch (\Exception $e) {
                $this->error("Failed to queue notification ID: {$notification->id}. Error: " . $e->getMessage());
                
                Log::error('Failed to queue notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('Finished processing scheduled notifications.');
    }
}
