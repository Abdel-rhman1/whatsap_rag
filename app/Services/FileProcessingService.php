<?php

namespace App\Services;

use App\Models\KnowledgeSource;
use App\Models\KnowledgeChunk;
use App\Models\KnowledgeBase;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileProcessingService
{
    public function __construct(
        protected TextExtractor $extractor,
        protected Chunker $chunker,
        protected EmbeddingService $embedding,
        protected QdrantService $qdrant,
        protected GroqService $llm
    ) {}

    /**
     * Process uploaded file: extract text, chunk, embed, and store
     */
    public function processFile(string $filePath, string $fileName, int $tenantId, string $sourceType = 'file', ?int $knowledgeBaseId = null): array
    {
        $startTime = microtime(true);
        TenantContext::set(tenantId: $tenantId);

        try {
            // Resolve KnowledgeBase
            if (!$knowledgeBaseId) {
                $kb = KnowledgeBase::withoutGlobalScopes()
                    ->firstOrCreate([
                        'tenant_id' => $tenantId,
                        'name'      => 'General Knowledge Base',
                    ], [
                        'description' => 'Default system knowledge base',
                        'is_active'   => true,
                    ]);
                $knowledgeBaseId = $kb->id;
            }

            // 1. Validate file size
            $maxSizeMb = config('rag.guardrails.max_file_size_mb', 25);
            $fileSizeBytes = Storage::size($filePath);
            $fileSizeMb = $fileSizeBytes / 1024 / 1024;
            
            if ($fileSizeMb > $maxSizeMb) {
                throw new Exception("File size ({$fileSizeMb}MB) exceeds maximum allowed ({$maxSizeMb}MB)");
            }

            // 2. Extract text from file
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $fullPath = Storage::path($filePath);
            
            Log::info("Extracting text from file", [
                'file' => $fileName,
                'extension' => $extension,
                'tenant_id' => $tenantId,
                'knowledge_base_id' => $knowledgeBaseId,
            ]);
            
            $text = $this->extractor->extract($fullPath, $extension);
            
            if (empty(trim($text))) {
                throw new Exception("No readable text content found in this file type. Please upload a supported document (PDF, DOCX, TXT) or clear image.");
            }

            // 3. Clean and normalize text
            $text = $this->cleanText($text);
            
            // 4. Detect language
            $language = $this->detectLanguage($text);

            // 5. Generate Summary if file is large
            $summary = null;
            if (strlen($text) > 3000) {
                $summary = $this->llm->summarize(mb_substr($text, 0, 8000));
            }
            
            // 6. Create knowledge source record
            $source = KnowledgeSource::create([
                'tenant_id'         => $tenantId,
                'knowledge_base_id' => $knowledgeBaseId,
                'name'              => $fileName,
                'type'              => $extension,
                'path'              => $filePath,
                'status'            => 'processing',
                'metadata'          => [
                    'source_type'     => $sourceType,
                    'file_name'       => $fileName,
                    'upload_time'     => now()->toIso8601String(),
                    'language'        => $language,
                    'file_size_bytes' => $fileSizeBytes,
                    'summary'         => $summary
                ]
            ]);

            // 7. Chunk the text
            $chunkSize = 1000;
            $chunkOverlap = 200;
            $chunks = $this->chunker->chunk($text, $chunkSize, $chunkOverlap);
            
            if ($summary) {
                array_unshift($chunks, "[DOCUMENT SUMMARY]: " . $summary);
            }

            $maxChunks = config('rag.guardrails.max_chunks_per_source', 2000);
            if (count($chunks) > $maxChunks) {
                Log::warning("Large file detected, truncating chunks to {$maxChunks}");
                $chunks = array_slice($chunks, 0, $maxChunks);
            }

            // 8. Process each chunk: embed and store
            $tenant = \App\Models\Tenant::findOrFail($tenantId);
            $collectionName = $tenant->qdrant_collection;
            $processedChunks = 0;

            foreach ($chunks as $index => $chunkText) {
                try {
                    $vector = $this->embedding->embed($chunkText);
                    
                    $chunk = KnowledgeChunk::create([
                        'tenant_id'           => $tenantId,
                        'knowledge_base_id'   => $knowledgeBaseId,
                        'knowledge_source_id' => $source->id,
                        'content'             => $chunkText,
                        'chunk_index'         => $index,
                        'metadata'            => [
                            'language'          => $language,
                            'source_type'       => $sourceType,
                            'file_name'         => $fileName,
                            'knowledge_base_id' => $knowledgeBaseId,
                        ]
                    ]);
                    
                    // Store in Qdrant with tenant_id and knowledge_base_id
                    $this->qdrant->upsert($tenantId, [
                        'id'      => $chunk->id,
                        'vector'  => $vector,
                        'payload' => [
                            'content'           => $chunkText,
                            'source_id'         => $source->id,
                            'source_name'       => $fileName,
                            'source_type'       => $sourceType,
                            'language'          => $language,
                            'chunk_index'       => $index,
                            'tenant_id'         => $tenantId,
                            'knowledge_base_id' => $knowledgeBaseId,
                        ]
                    ], $collectionName);
                    
                    $processedChunks++;
                    
                } catch (Exception $e) {
                    Log::error("Failed to process chunk {$index}", [
                        'source_id' => $source->id,
                        'error'     => $e->getMessage()
                    ]);
                }
            }

            // 9. Update source status
            $source->update([
                'status' => 'indexed',
                'metadata' => array_merge($source->metadata ?? [], [
                    'chunks_count'       => $processedChunks,
                    'processing_time_ms' => (microtime(true) - $startTime) * 1000
                ])
            ]);

            return [
                'success'           => true,
                'source_id'         => $source->id,
                'knowledge_base_id' => $knowledgeBaseId,
                'chunks_count'      => $processedChunks,
                'language'          => $language,
                'file_name'         => $fileName,
                'summary'           => $summary
            ];

        } catch (Exception $e) {
            Log::error("File processing failed", [
                'file'      => $fileName,
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage()
            ]);
            
            if (isset($source)) {
                $source->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }

    protected function cleanText(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    protected function detectLanguage(string $text): string
    {
        $sample = mb_substr($text, 0, 500);
        $arabicCount = preg_match_all('/[\x{0600}-\x{06FF}]/u', $sample);
        $latinCount = preg_match_all('/[a-zA-Z]/', $sample);
        
        if ($arabicCount > $latinCount) {
            return 'ar';
        } elseif ($latinCount > 0) {
            return 'en';
        }
        
        return 'auto';
    }
}
