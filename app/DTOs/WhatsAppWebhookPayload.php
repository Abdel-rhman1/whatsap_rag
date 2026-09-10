<?php

namespace App\DTOs;

class WhatsAppWebhookPayload
{
    public function __construct(
        public string $accountIdentifier, // instance_name or phone_number_id
        public string $from,              // sender phone number
        public ?string $body,             // text content
        public string $pushName = 'Guest', // sender contact name
        public string $messageType = 'text', // text, audio, image, document
        public ?string $mediaUrl = null,
        public ?string $mimeType = null,
        public ?string $fileName = null,
        public ?string $messageId = null,
        public bool $isFromMe = false,
        public array $rawPayload = []
    ) {}

    public function toArray(): array
    {
        return [
            'instance_id' => $this->accountIdentifier,
            'from'        => $this->from,
            'body'        => $this->body,
            'pushName'    => $this->pushName,
            'type'        => $this->messageType,
            'media_url'   => $this->mediaUrl,
            'mime_type'   => $this->mimeType,
            'file_name'   => $this->fileName,
            'message_id'  => $this->messageId,
            'is_from_me'  => $this->isFromMe,
            'from_me'     => $this->isFromMe,
            'raw'         => $this->rawPayload,
        ];
    }
}
