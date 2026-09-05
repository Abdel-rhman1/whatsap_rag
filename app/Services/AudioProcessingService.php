<?php

namespace App\Services;

use App\Models\KnowledgeSource;
use App\Models\KnowledgeChunk;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AudioProcessingService
{
    public function __construct(
        protected Chunker $chunker,
        protected EmbeddingService $embedding,
        protected QdrantService $qdrant
    ) {}

    /**
     * Just transcribes audio without indexing it
     */
    public function transcribeOnly(string $audioPath, string $disk = 'local', ?string $language = null): array
    {
        $fullPath = Storage::disk($disk)->path($audioPath);
        if (!file_exists($fullPath)) {
            throw new Exception("Audio file not found: {$audioPath}");
        }

        // Convert to WAV
        $wavPath = $this->convertToWav($fullPath);
        
        // Transcribe
        $transcription = $this->transcribeAudio($wavPath, $language);
        
        // Cleanup temp WAV file
        if ($wavPath !== $fullPath && file_exists($wavPath)) {
            @unlink($wavPath);
        }

        return [
            'text' => $this->cleanTranscription($transcription['text']),
            'language' => $transcription['language'] ?? 'auto',
            'confidence' => $transcription['confidence'] ?? 0.95,
            '_wav_path' => $wavPath, // internal: for cleanup tracking (already cleaned above)
        ];
    }

    /**
     * Process audio message: transcribe, chunk, embed, and store
     */
    public function processAudio(
        string $audioPath, 
        int $tenantId, 
        string $sender = 'unknown',
        ?string $language = null,
        string $disk = 'local'
    ): array
    {
        $startTime = microtime(true);
        
        try {
            // transcribeOnly handles WAV conversion and temp file cleanup internally
            $transcription = $this->transcribeOnly($audioPath, $disk, $language);
            $cleanedText = $transcription['text'];
            $detectedLanguage = $transcription['language'];

            if (empty(trim($cleanedText))) {
                throw new Exception("Audio transcription yielded empty text. Cannot index.");
            }
            
            // 5. Create knowledge source record
            $fileName = basename($audioPath);
            $source = KnowledgeSource::create([
                'tenant_id' => $tenantId,
                'name' => "Audio: {$fileName}",
                'type' => 'audio',
                'path' => $audioPath,
                'status' => 'processing',
                'metadata' => [
                    'source_type' => 'audio',
                    'sender' => $sender,
                    'timestamp' => now()->toIso8601String(),
                    'language' => $detectedLanguage,
                    'file_size_bytes' => Storage::disk($disk)->size($audioPath),
                    'transcription_raw' => $transcription['text'],
                    'transcription_cleaned' => $cleanedText
                ]
            ]);

            // 6. Chunk the transcription
            $chunks = $this->chunker->chunk($cleanedText, 500, 100);

            if (empty($chunks)) {
                $chunks = [trim($cleanedText)]; // Treat full text as single chunk
            }
            
            Log::info("Audio transcribed and chunked", [
                'source_id' => $source->id,
                'chunks_count' => count($chunks),
                'language' => $detectedLanguage
            ]);

            // 7. Process each chunk: embed and store
            $tenant = \App\Models\Tenant::findOrFail($tenantId);
            $collectionName = $tenant->qdrant_collection;
            $processedChunks = 0;

            foreach ($chunks as $index => $chunkText) {
                try {
                    // Generate embedding
                    $vector = $this->embedding->embed($chunkText);
                    
                    // Store in database
                    $chunk = KnowledgeChunk::create([
                        'knowledge_source_id' => $source->id,
                        'content' => $chunkText,
                        'vector_id' => Str::uuid()->toString(),
                        'metadata' => [
                            'chunk_index' => $index,
                            'language' => $detectedLanguage,
                            'source_type' => 'audio',
                            'sender' => $sender
                        ]
                    ]);
                    
                    // Store in Qdrant
                    $this->qdrant->upsert($tenantId, [
                        'id' => $chunk->id,
                        'vector' => $vector,
                        'payload' => [
                            'content' => $chunkText,
                            'source_id' => $source->id,
                            'source_name' => "Audio from {$sender}",
                            'source_type' => 'audio',
                            'language' => $detectedLanguage,
                            'sender' => $sender,
                            'chunk_index' => $index,
                            'tenant_id' => $tenantId
                        ]
                    ], $collectionName);
                    
                    $processedChunks++;
                    
                } catch (Exception $e) {
                    Log::error("Failed to process audio chunk {$index}", [
                        'source_id' => $source->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // 8. Update source status
            $source->update([
                'status' => 'indexed',
                'metadata' => array_merge($source->metadata ?? [], [
                    'chunks_count' => $processedChunks,
                    'processing_time_ms' => (microtime(true) - $startTime) * 1000
                ])
            ]);

            Log::info("Audio processing completed", [
                'source_id' => $source->id,
                'chunks_processed' => $processedChunks,
                'time_ms' => (microtime(true) - $startTime) * 1000
            ]);

            return [
                'success' => true,
                'source_id' => $source->id,
                'transcription' => $cleanedText,
                'chunks_count' => $processedChunks,
                'language' => $detectedLanguage,
                'confidence' => $transcription['confidence'] ?? 0.95
            ];

        } catch (Exception $e) {
            Log::error("Audio processing failed", [
                'path' => $audioPath,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage()
            ]);
            
            // Update source status if it was created
            if (isset($source)) {
                $source->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }

    /**
     * Convert audio file to WAV format using FFmpeg
     */
    protected function convertToWav(string $inputPath): string
    {
        // Check if already WAV
        $extension = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
        if ($extension === 'wav') {
            return $inputPath;
        }

        // Generate output path
        $outputPath = sys_get_temp_dir() . '/' . uniqid('audio_') . '.wav';
        
        // Use FFmpeg to convert with noise reduction filters
        $ffmpegPath = config('rag.audio.ffmpeg_path', 'ffmpeg');
        $command = sprintf(
            '%s -i %s -af "highpass=f=200,lowpass=f=3000,afftdn" -ar 16000 -ac 1 -c:a pcm_s16le %s 2>&1',
            escapeshellcmd($ffmpegPath),
            escapeshellarg($inputPath),
            escapeshellarg($outputPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($outputPath)) {
            Log::error("FFmpeg conversion failed", [
                'command' => $command,
                'output' => implode("\n", $output),
                'return_code' => $returnCode
            ]);
            throw new Exception("Failed to convert audio to WAV format");
        }
        
        return $outputPath;
    }

    /**
     * Transcribe audio using Whisper API (OpenAI or local)
     */
    protected function transcribeAudio(string $wavPath, ?string $language = null): array
    {
        $whisperUrl = config('rag.audio.whisper_api_url');
        $whisperApiKey = config('rag.audio.openai_api_key');
        
        // Try local Whisper service if explicitly configured
        if ($whisperUrl && !str_contains($whisperUrl, 'openai')) {
            return $this->transcribeWithLocalWhisper($wavPath, $language);
        }
        
        // Fallback to OpenAI or Groq Whisper API
        if ($whisperApiKey || config('rag.groq.api_key')) {
            return $this->transcribeWithGroqOrOpenAI($wavPath, $language);
        }
        
        throw new Exception("No Whisper transcription service configured");
    }

    /**
     * Transcribe using local Whisper service
     */
    protected function transcribeWithLocalWhisper(string $wavPath, ?string $language = null): array
    {
        $whisperUrl = config('rag.audio.whisper_api_url', 'http://whisper:9000/asr');
        
        $response = Http::attach(
            'audio_file',
            file_get_contents($wavPath),
            basename($wavPath)
        )->post($whisperUrl, [
            'task' => 'transcribe',
            'language' => $language ?? 'auto',
            'output' => 'json'
        ]);

        if ($response->failed()) {
            throw new Exception("Whisper transcription failed: " . $response->body());
        }

        $result = $response->json();
        
        return [
            'text' => $result['text'] ?? '',
            'language' => $result['language'] ?? $language ?? 'auto'
        ];
    }

    /**
     * Transcribe using Groq or OpenAI Whisper API
     */
    protected function transcribeWithGroqOrOpenAI(string $wavPath, ?string $language = null): array
    {
        $groqKey = config('rag.groq.api_key');
        $openaiKey = config('rag.audio.openai_api_key');
        
        $url = $groqKey ? 'https://api.groq.com/openai/v1/audio/transcriptions' : 'https://api.openai.com/v1/audio/transcriptions';
        $apiKey = $groqKey ?: $openaiKey;
        $model = $groqKey ? 'whisper-large-v3' : 'whisper-1';
        
        $params = [
            'model' => $model,
            'response_format' => 'json'
        ];

        if ($language && $language !== 'auto') {
            $params['language'] = $language;
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey
        ])->attach(
            'file',
            file_get_contents($wavPath),
            basename($wavPath)
        )->post($url, $params);

        if ($response->failed()) {
            throw new Exception("Transcription failed: " . $response->body());
        }

        $result = $response->json();
        
        return [
            'text' => $result['text'] ?? '',
            'language' => $language ?? 'auto',
            'confidence' => 0.95 // Default high confidence for Groq/OpenAI as they don't return simple global scores in 'json' format
        ];
    }

    /**
     * Clean transcription by removing filler words and normalizing
     */
    protected function cleanTranscription(string $text): string
    {
        // Arabic filler words and common dialect markers
        $arabicFillers = [
            'يعني', 'طيب', 'اممم', 'اه', 'ايوه', 'لا', 'ماشي', 'اوكي', 'بقى', 'خلاص', 
            'كده', 'حضرتك', 'تمام', 'فندم', 'ألو', 'يا هلا', 'يا مسهل'
        ];
        
        // English filler words
        $englishFillers = [
            'um', 'uh', 'like', 'you know', 'i mean', 'actually', 'basically', 
            'literally', 'so', 'well', 'kind of', 'sort of'
        ];
        
        // Remove filler words (case-insensitive)
        foreach (array_merge($arabicFillers, $englishFillers) as $filler) {
            $text = preg_replace('/\b' . preg_quote($filler, '/') . '\b/iu', '', $text);
        }
        
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remove repeated punctuation
        $text = preg_replace('/([.!?]){2,}/', '$1', $text);
        
        return trim($text);
    }
}
