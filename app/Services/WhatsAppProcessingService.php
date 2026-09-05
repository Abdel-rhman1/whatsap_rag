<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\WhatsAppAccount;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class WhatsAppProcessingService
{
    public function __construct(
        protected RagChatService $rag,
        protected WhatsAppService $whatsapp,
        protected FileProcessingService $fileProcessor,
        protected AudioProcessingService $audioProcessor
    ) {}

    public function processPayload(array $payload, int $tenantId): void
    {
        try {
            $instanceId = $payload['instance_id'] ?? $payload['accountIdentifier'] ?? null;
            $from = $payload['from'];
            $body = $payload['body'] ?? null;
            $pushName = $payload['pushName'] ?? 'Guest';
            $messageType = $payload['type'] ?? 'text';
            $mediaUrl = $payload['media_url'] ?? null;
            $mimeType = $payload['mime_type'] ?? null;
            $fileName = $payload['file_name'] ?? null;

            // Resolve WhatsAppAccount entity
            $account = $this->whatsapp->resolveAccount($instanceId);

            // Link conversation with tenant_id and whatsapp_account_id
            $conversation = Conversation::withoutGlobalScopes()->firstOrCreate([
                'tenant_id'   => $tenantId,
                'external_id' => $from,
                'platform'    => 'whatsapp',
            ], [
                'whatsapp_account_id' => $account?->id,
                'phone_number'        => $from,
                'contact_name'        => $pushName,
                'last_message_at'     => now(),
            ]);

            if ($account && $conversation->whatsapp_account_id !== $account->id) {
                $conversation->update(['whatsapp_account_id' => $account->id]);
            }

            $processedText = null;
            $sourceType = 'text';
            $userMetadata = [
                'whatsapp_account_id' => $account?->id,
            ];
            $audioSourcePath = null;

            if ($messageType === 'audio' && $mediaUrl) {
                $audioResult = $this->handleAudioMessage($mediaUrl, $tenantId, $pushName, false);
                if (($audioResult['confidence'] ?? 1.0) < 0.85) {
                    $this->whatsapp->sendMessage($account, $from, "لم أتمكن من سماع رسالتك جيدًا، ممكن تعيدها؟ 🎧\nI couldn't hear the message well, could you repeat it? 🎧");
                    return;
                }
                $processedText = $audioResult['text'];
                $sourceType = 'audio';
                $audioSourcePath = $audioResult['path'];
            } elseif (in_array($messageType, ['document', 'image']) && $mediaUrl) {
                $processedText = $this->handleFileMessage($mediaUrl, $fileName, $mimeType, $tenantId);
                $sourceType = 'file';
            } elseif ($body) {
                $processedText = $body;
                $sourceType = 'text';
            } else {
                $this->whatsapp->sendMessage($account, $from, "نوع الرسالة غير مدعوم 🙏");
                return;
            }

            // Call RAG Service
            $response = $this->rag->answer($processedText, $tenantId, 'whatsapp', $from, $instanceId, $userMetadata);
            $answer = $response['reply'] ?? $response['answer'];

            if ($response['is_noisy'] ?? false) {
                $answer = "عذراً، الصوت غير واضح كفاية. هل يمكنك إعادة تسجيل الرسالة؟ 🎤";
            }

            // If escalated to human, don't auto-reply — wait for agent to respond from dashboard
            if ($response['hold_response'] ?? false) {
                Log::info("Message held for human agent", ['from' => $from, 'tenant_id' => $tenantId]);
                return;
            }

            // Send response back using resolved WhatsApp account and provider
            $this->whatsapp->sendMessage($account, $from, $answer);
            
            // Post-response audio indexing
            if ($audioSourcePath) {
                try {
                    $this->audioProcessor->processAudio($audioSourcePath, $tenantId, $pushName, null, 'public');
                } catch (Exception $e) {
                    Log::warning("Post-response audio indexing failed: " . $e->getMessage());
                }
            }

        } catch (Exception $e) {
            Log::error("WhatsApp Processing Failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function handleAudioMessage(string $mediaUrl, int $tenantId, string $sender, bool $shouldIndex = true): array
    {
        $audioContent = file_get_contents($mediaUrl);
        $fileName = uniqid('whatsapp_audio_') . '.ogg';
        $audioPath = 'audio/' . $fileName;
        Storage::disk('public')->put($audioPath, $audioContent);
        $publicUrl = asset('storage/' . $audioPath);

        if ($shouldIndex) {
            $result = $this->audioProcessor->processAudio($audioPath, $tenantId, $sender, null, 'public');
            return [
                'text' => $result['transcription'],
                'audio_url' => $publicUrl,
                'confidence' => $result['confidence'] ?? 0.85,
                'path' => $audioPath
            ];
        }

        $result = $this->audioProcessor->transcribeOnly($audioPath, 'public');

        return [
            'text' => $result['text'],
            'audio_url' => $publicUrl,
            'confidence' => $result['confidence'] ?? 0.85,
            'path' => $audioPath
        ];
    }

    protected function handleFileMessage(string $mediaUrl, ?string $fileName, ?string $mimeType, int $tenantId): string
    {
        $fileContent = file_get_contents($mediaUrl);
        $ext = $this->getExtensionFromMimeType($mimeType) ?? 'bin';
        $filePath = 'files/' . uniqid('whatsapp_file_') . '.' . $ext;
        Storage::put($filePath, $fileContent);

        $result = $this->fileProcessor->processFile(
            $filePath,
            $fileName ?? 'uploaded_file.' . $ext,
            $tenantId,
            'whatsapp_upload'
        );

        if (!empty($result['summary'])) {
            return "[File: {$result['file_name']}]\n" . $result['summary'];
        }

        return "تم استلام الملف وفهرسته بنجاح ✅ يمكنك الآن طرح أسئلة حول محتواه.";
    }

    protected function getExtensionFromMimeType(?string $mimeType): ?string
    {
        if (!$mimeType) return null;
        $mimeMap = [
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'text/plain' => 'txt',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'audio/ogg' => 'ogg',
            'audio/mpeg' => 'mp3',
        ];
        return $mimeMap[$mimeType] ?? null;
    }
}
