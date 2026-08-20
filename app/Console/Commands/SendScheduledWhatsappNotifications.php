<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WhatsappNotification;
use App\Services\WhatsappService;

class SendScheduledWhatsappNotifications extends Command
{
    protected $signature = 'app:send-scheduled-whatsapp-notifications';

    protected $description = 'Send WhatsApp notifications whose scheduled time is due';

    public function handle(WhatsappService $whatsapp)
    {
        $notifications = WhatsappNotification::with('recipients.contact')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($notifications as $notification) {
            $notification->update(['status' => 'processing']);

            $extraParams = $notification->template_params ?? [];
            $languageCode = $notification->template_language ?? config('services.whatsapp.template_language', 'en_US');
            $nameIndex = config('services.whatsapp.name_param_index', 0);
            $headerImageUrl = $notification->header_image_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($notification->header_image_path)
                : null;

            foreach ($notification->recipients as $recipient) {
                if (!$recipient->contact) {
                    continue;
                }

                $bodyParams = $extraParams;
                array_splice($bodyParams, $nameIndex, 0, [$recipient->contact->name ?? $recipient->contact->mobile]);

                try {
                    $result = $whatsapp->sendTemplateMessage(
                        $recipient->contact->mobile,
                        $notification->template_name,
                        $bodyParams,
                        $languageCode,
                        $headerImageUrl
                    );

                    $recipient->update([
                        'status' => $result['success'] ? 'sent' : 'failed',
                        'error_message' => $result['success'] ? null : json_encode($result['response']),
                    ]);
                } catch (\Throwable $e) {
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);

                    $this->error("Recipient #{$recipient->id} failed: {$e->getMessage()}");
                }
            }

            $failedCount = $notification->recipients()->where('status', 'failed')->count();

            $notification->update([
                'status' => $failedCount > 0 ? 'failed' : 'completed',
            ]);

            $this->info("Processed notification #{$notification->id}");
        }

        if ($notifications->isEmpty()) {
            $this->info('No due notifications.');
        }
    }
}
