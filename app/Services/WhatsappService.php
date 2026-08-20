<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $phoneNumberId;
    protected string $accessToken;
    protected string $apiVersion;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->apiVersion = config('services.whatsapp.api_version', 'v20.0');
    }

    /**
     * Send a WhatsApp template message to a single mobile number.
     *
     * @param  array<int, array{type: string, text: string}>  $bodyParams  positional {{1}}, {{2}}... values
     */
    public function sendTemplateMessage(string $mobile, string $templateName, array $bodyParams = [], string $languageCode = 'en_US', ?string $headerImageUrl = null): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $components = [];

        if ($headerImageUrl) {
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    ['type' => 'image', 'image' => ['link' => $headerImageUrl]],
                ],
            ];
        }

        if (!empty($bodyParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($value) => ['type' => 'text', 'text' => $value],
                    $bodyParams
                ),
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $mobile,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        $response = Http::withToken($this->accessToken)->post($url, $payload);

        if ($response->failed()) {
            Log::error('WhatsApp send failed', [
                'mobile' => $mobile,
                'response' => $response->json(),
            ]);
        }

        return [
            'success' => $response->successful(),
            'response' => $response->json(),
        ];
    }

    /**
     * Send a free-form WhatsApp text message.
     * Only works within the 24-hour customer service window (i.e. after
     * the recipient has messaged your business number recently) — use
     * sendTemplateMessage() to initiate contact outside that window.
     */
    public function sendTextMessage(string $mobile, string $body): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $mobile,
            'type' => 'text',
            'text' => ['body' => $body],
        ];

        $response = Http::withToken($this->accessToken)->post($url, $payload);

        if ($response->failed()) {
            Log::error('WhatsApp send failed', [
                'mobile' => $mobile,
                'response' => $response->json(),
            ]);
        }

        return [
            'success' => $response->successful(),
            'response' => $response->json(),
        ];
    }
}
