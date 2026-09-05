<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\WhatsAppAccount;
use App\DTOs\WhatsAppWebhookPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BaileysGatewayProvider implements WhatsAppProviderInterface
{
    public function sendMessage(WhatsAppAccount $account, string $to, string $message, int $timeout = 35): bool
    {
        $gatewayUrl = $account->getCredential('gateway_url') ?? config('rag.whatsapp.gateway_url', 'http://localhost:3000');

        Log::info("BaileysGatewayProvider Sending Message", [
            'account' => $account->account_identifier,
            'to'      => $to,
            'gateway' => $gatewayUrl,
        ]);

        $response = Http::timeout($timeout)->post("{$gatewayUrl}/send", [
            'instance_id' => $account->account_identifier,
            'to'          => $to,
            'message'     => $message,
        ]);

        if ($response->failed()) {
            throw new Exception("Baileys gateway send failed: " . ($response->json('error') ?? $response->body()));
        }

        return true;
    }

    public function sendTemplate(WhatsAppAccount $account, string $to, string $templateName, array $parameters = [], string $language = 'en'): bool
    {
        // For Baileys, template sending formats text with parameters
        $message = "Template: {$templateName}\n" . json_encode($parameters, JSON_UNESCAPED_UNICODE);
        return $this->sendMessage($account, $to, $message);
    }

    public function sendMedia(WhatsAppAccount $account, string $to, string $mediaUrl, string $mediaType, ?string $caption = null): bool
    {
        $gatewayUrl = $account->getCredential('gateway_url') ?? config('rag.whatsapp.gateway_url', 'http://localhost:3000');

        $response = Http::timeout(60)->post("{$gatewayUrl}/send-media", [
            'instance_id' => $account->account_identifier,
            'to'          => $to,
            'media_url'   => $mediaUrl,
            'media_type'  => $mediaType,
            'caption'     => $caption,
        ]);

        return $response->successful();
    }

    public function handleWebhook(Request $request): ?WhatsAppWebhookPayload
    {
        $instanceId = $request->input('instance_id');
        $from       = $request->input('from');

        if (!$instanceId || !$from) {
            return null;
        }

        return new WhatsAppWebhookPayload(
            accountIdentifier: $instanceId,
            from:              $from,
            body:              $request->input('body'),
            pushName:          $request->input('pushName', 'Guest'),
            messageType:       $request->input('type', 'text'),
            mediaUrl:          $request->input('media_url'),
            mimeType:          $request->input('mime_type'),
            fileName:          $request->input('file_name'),
            messageId:         $request->input('message_id'),
            rawPayload:        $request->all()
        );
    }

    public function getAccountInfo(WhatsAppAccount $account): array
    {
        return [
            'provider'           => 'baileys',
            'account_identifier' => $account->account_identifier,
            'phone_number'       => $account->phone_number,
            'status'             => $account->status,
        ];
    }

    public function validateCredentials(WhatsAppAccount $account): bool
    {
        return !empty($account->account_identifier);
    }

    public function startInstance(WhatsAppAccount $account): bool
    {
        $gatewayUrl = $account->getCredential('gateway_url') ?? config('rag.whatsapp.gateway_url', 'http://localhost:3000');

        $response = Http::post("{$gatewayUrl}/start", [
            'instance_id' => $account->account_identifier
        ]);

        return $response->successful();
    }
}
