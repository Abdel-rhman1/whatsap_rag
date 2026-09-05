<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\WhatsAppAccount;
use App\DTOs\WhatsAppWebhookPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MetaCloudApiProvider implements WhatsAppProviderInterface
{
    protected string $apiVersion = 'v18.0';

    public function sendMessage(WhatsAppAccount $account, string $to, string $message, int $timeout = 35): bool
    {
        $phoneNumberId = $account->account_identifier;
        $accessToken   = $account->getCredential('access_token');

        if (!$accessToken) {
            throw new Exception("Meta Cloud API access token missing for account {$account->id}");
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$phoneNumberId}/messages";

        $response = Http::withToken($accessToken)
            ->timeout($timeout)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $to,
                'type'              => 'text',
                'text'              => ['body' => $message],
            ]);

        if ($response->failed()) {
            Log::error("Meta Cloud API Send Error", ['response' => $response->body()]);
            throw new Exception("Meta WhatsApp API error: " . ($response->json('error.message') ?? $response->body()));
        }

        return true;
    }

    public function sendTemplate(WhatsAppAccount $account, string $to, string $templateName, array $parameters = [], string $language = 'en'): bool
    {
        $phoneNumberId = $account->account_identifier;
        $accessToken   = $account->getCredential('access_token');

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$phoneNumberId}/messages";

        $components = [];
        if (!empty($parameters)) {
            $params = [];
            foreach ($parameters as $val) {
                $params[] = ['type' => 'text', 'text' => (string)$val];
            }
            $components[] = [
                'type' => 'body',
                'parameters' => $params
            ];
        }

        $response = Http::withToken($accessToken)->post($url, [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => $language],
                'components' => $components,
            ]
        ]);

        return $response->successful();
    }

    public function sendMedia(WhatsAppAccount $account, string $to, string $mediaUrl, string $mediaType, ?string $caption = null): bool
    {
        $phoneNumberId = $account->account_identifier;
        $accessToken   = $account->getCredential('access_token');

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => $mediaType,
            $mediaType          => array_filter(['link' => $mediaUrl, 'caption' => $caption]),
        ];

        $response = Http::withToken($accessToken)->post($url, $payload);
        return $response->successful();
    }

    public function handleWebhook(Request $request): ?WhatsAppWebhookPayload
    {
        $entry = $request->input('entry.0.changes.0.value');
        if (!$entry || empty($entry['messages'])) {
            return null;
        }

        $msgData       = $entry['messages'][0];
        $phoneNumberId = $entry['metadata']['phone_number_id'] ?? null;
        $from          = $msgData['from'] ?? null;
        $pushName      = $entry['contacts'][0]['profile']['name'] ?? 'Guest';
        $type          = $msgData['type'] ?? 'text';
        $body          = $msgData['text']['body'] ?? null;

        return new WhatsAppWebhookPayload(
            accountIdentifier: $phoneNumberId,
            from:              $from,
            body:              $body,
            pushName:          $pushName,
            messageType:       $type,
            rawPayload:        $request->all()
        );
    }

    public function getAccountInfo(WhatsAppAccount $account): array
    {
        return [
            'provider'           => 'meta',
            'account_identifier' => $account->account_identifier,
            'status'             => $account->status,
        ];
    }

    public function validateCredentials(WhatsAppAccount $account): bool
    {
        return !empty($account->getCredential('access_token')) && !empty($account->account_identifier);
    }

    public function startInstance(WhatsAppAccount $account): bool
    {
        // Meta API does not require QR starting
        return true;
    }
}
