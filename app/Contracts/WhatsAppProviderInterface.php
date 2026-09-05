<?php

namespace App\Contracts;

use App\Models\WhatsAppAccount;
use App\DTOs\WhatsAppWebhookPayload;
use Illuminate\Http\Request;

interface WhatsAppProviderInterface
{
    /**
     * Send a text message to a specific recipient.
     */
    public function sendMessage(WhatsAppAccount $account, string $to, string $message, int $timeout = 35): bool;

    /**
     * Send a pre-approved template message (Meta Cloud API / Twilio).
     */
    public function sendTemplate(WhatsAppAccount $account, string $to, string $templateName, array $parameters = [], string $language = 'en'): bool;

    /**
     * Send media (image, audio, document, video) to a recipient.
     */
    public function sendMedia(WhatsAppAccount $account, string $to, string $mediaUrl, string $mediaType, ?string $caption = null): bool;

    /**
     * Parse and standardize an incoming provider webhook payload into a unified DTO.
     */
    public function handleWebhook(Request $request): ?WhatsAppWebhookPayload;

    /**
     * Fetch connection/account info from the provider.
     */
    public function getAccountInfo(WhatsAppAccount $account): array;

    /**
     * Validate that the account credentials and tokens are valid.
     */
    public function validateCredentials(WhatsAppAccount $account): bool;

    /**
     * Start/initialize the account session (e.g. request QR code for Baileys gateway).
     */
    public function startInstance(WhatsAppAccount $account): bool;
}
