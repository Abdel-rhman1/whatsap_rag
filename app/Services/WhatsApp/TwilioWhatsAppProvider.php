<?php

namespace App\Services\WhatsApp;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\WhatsAppAccount;
use App\DTOs\WhatsAppWebhookPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TwilioWhatsAppProvider implements WhatsAppProviderInterface
{
    public function sendMessage(WhatsAppAccount $account, string $to, string $message, int $timeout = 35): bool
    {
        $accountSid = $account->getCredential('account_sid');
        $authToken  = $account->getCredential('auth_token');
        $fromNumber = $account->phone_number; // e.g. whatsapp:+14155238886

        if (!$accountSid || !$authToken) {
            throw new Exception("Twilio credentials missing for account {$account->id}");
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $formattedTo = str_starts_with($to, 'whatsapp:') ? $to : "whatsapp:{$to}";
        $formattedFrom = str_starts_with($fromNumber, 'whatsapp:') ? $fromNumber : "whatsapp:{$fromNumber}";

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->timeout($timeout)
            ->post($url, [
                'From' => $formattedFrom,
                'To'   => $formattedTo,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            Log::error("Twilio WhatsApp Error", ['response' => $response->body()]);
            throw new Exception("Twilio send failed: " . ($response->json('message') ?? $response->body()));
        }

        return true;
    }

    public function sendTemplate(WhatsAppAccount $account, string $to, string $templateName, array $parameters = [], string $language = 'en'): bool
    {
        return $this->sendMessage($account, $to, "Template: {$templateName}");
    }

    public function sendMedia(WhatsAppAccount $account, string $to, string $mediaUrl, string $mediaType, ?string $caption = null): bool
    {
        $accountSid = $account->getCredential('account_sid');
        $authToken  = $account->getCredential('auth_token');
        $fromNumber = $account->phone_number;

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post($url, [
                'From'     => str_starts_with($fromNumber, 'whatsapp:') ? $fromNumber : "whatsapp:{$fromNumber}",
                'To'       => str_starts_with($to, 'whatsapp:') ? $to : "whatsapp:{$to}",
                'Body'     => $caption ?? '',
                'MediaUrl' => $mediaUrl,
            ]);

        return $response->successful();
    }

    public function handleWebhook(Request $request): ?WhatsAppWebhookPayload
    {
        $from = str_replace('whatsapp:', '', $request->input('From', ''));
        $body = $request->input('Body');

        if (!$from) {
            return null;
        }

        return new WhatsAppWebhookPayload(
            accountIdentifier: $request->input('To', 'twilio_account'),
            from:              $from,
            body:              $body,
            pushName:          $request->input('ProfileName', 'Guest'),
            messageType:       $request->input('NumMedia') > 0 ? 'media' : 'text',
            mediaUrl:          $request->input('MediaUrl0'),
            rawPayload:        $request->all()
        );
    }

    public function getAccountInfo(WhatsAppAccount $account): array
    {
        return [
            'provider'           => 'twilio',
            'account_identifier' => $account->account_identifier,
            'status'             => $account->status,
        ];
    }

    public function validateCredentials(WhatsAppAccount $account): bool
    {
        return !empty($account->getCredential('account_sid')) && !empty($account->getCredential('auth_token'));
    }

    public function startInstance(WhatsAppAccount $account): bool
    {
        return true;
    }
}
