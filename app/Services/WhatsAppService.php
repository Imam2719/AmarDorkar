<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Meta Cloud API Service
 * Docs: https://developers.facebook.com/docs/whatsapp/cloud-api
 */
class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->token         = config('services.whatsapp.token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
        $this->apiVersion    = config('services.whatsapp.api_version', 'v21.0');
        $this->baseUrl       = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";
    }

    /**
     * Check if WhatsApp API is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->phoneNumberId);
    }

    /**
     * Format Bangladesh mobile number to international format
     * e.g. 01864719755 → 8801864719755
     */
    public function formatMobile(string $mobile): string
    {
        $phone = preg_replace('/[^0-9]/', '', $mobile);

        if (str_starts_with($phone, '0')) {
            $phone = '880' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '880')) {
            $phone = '880' . $phone;
        }

        return $phone;
    }

    /**
     * Send a free-form text message (only works within 24hr customer-initiated window)
     */
    public function sendText(string $mobile, string $message): array
    {
        $phone = $this->formatMobile($mobile);

        if (!$this->isConfigured()) {
            Log::info('WhatsApp [NOT CONFIGURED] → +' . $phone, ['message' => $message]);
            return ['success' => false, 'error' => 'WhatsApp API not configured'];
        }

        try {
            $response = Http::withToken($this->token)
                ->post($this->baseUrl, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type'    => 'individual',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => [
                        'preview_url' => false,
                        'body'        => $message,
                    ],
                ]);

            $body = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp sent → +' . $phone, ['message_id' => $body['messages'][0]['id'] ?? null]);
                return ['success' => true, 'data' => $body];
            }

            Log::error('WhatsApp API error → +' . $phone, ['status' => $response->status(), 'body' => $body]);
            return ['success' => false, 'error' => $body['error']['message'] ?? 'Unknown error', 'status' => $response->status()];

        } catch (\Exception $e) {
            Log::error('WhatsApp exception → +' . $phone, ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a pre-approved template message
     * Use this for business-initiated messages (outside 24hr window)
     *
     * Example: sendTemplate('8801864719755', 'hello_world', 'en_US')
     */
    public function sendTemplate(string $mobile, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $phone = $this->formatMobile($mobile);

        if (!$this->isConfigured()) {
            Log::info('WhatsApp Template [NOT CONFIGURED] → +' . $phone, ['template' => $templateName]);
            return ['success' => false, 'error' => 'WhatsApp API not configured'];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $phone,
            'type'              => 'template',
            'template'          => [
                'name'     => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        try {
            $response = Http::withToken($this->token)->post($this->baseUrl, $payload);
            $body     = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp Template sent → +' . $phone, ['template' => $templateName, 'message_id' => $body['messages'][0]['id'] ?? null]);
                return ['success' => true, 'data' => $body];
            }

            Log::error('WhatsApp Template error → +' . $phone, ['template' => $templateName, 'body' => $body]);
            return ['success' => false, 'error' => $body['error']['message'] ?? 'Unknown error'];

        } catch (\Exception $e) {
            Log::error('WhatsApp Template exception → +' . $phone, ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a template with text body parameters
     * Useful for templates that have {{1}}, {{2}} variables
     */
    public function sendTemplateWithParams(string $mobile, string $templateName, string $languageCode, array $bodyParams): array
    {
        $components = [
            [
                'type'       => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => (string) $p], $bodyParams),
            ],
        ];

        return $this->sendTemplate($mobile, $templateName, $languageCode, $components);
    }
}
