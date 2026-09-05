<?php

return [
    'qdrant' => [
        'url' => env('QDRANT_URL', 'http://qdrant:6333'),
        'collection' => env('QDRANT_COLLECTION', 'rag_chunks'),
        'vector_size' => env('QDRANT_VECTOR_SIZE', 768),
        'tenant_strategy' => env('QDRANT_TENANT_STRATEGY', 'payload'), // 'payload' or 'collection'
    ],
    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://ollama:11434'),
        'embedding_model' => env('EMBEDDING_MODEL', 'nomic-embed-text'),
        'chat_model' => env('CHAT_MODEL', 'qwen2.5:0.5b'),
    ],
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],
    'search' => [
        'top_k' => 10,
        'min_score' => 0.3,
        'confidence_threshold' => 0.5, // Minimum confidence to answer without escalation
    ],
    'guardrails' => [
        'max_message_length' => 500,
        'max_file_size_mb' => 10,
        'max_chunks_per_source' => 1000,
        'rate_limit_per_minute' => 20,
    ],
    'whatsapp' => [
        'secret' => env('WHATSAPP_SECRET'),
        'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'http://localhost:3000'),
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL'),
    ],
    'audio' => [
        'whisper_api_url' => env('WHISPER_API_URL', 'http://whisper:9000/asr'),
        'openai_api_key' => env('OPENAI_API_KEY'),
        'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),
    ],
    'ocr' => [
        'service' => env('OCR_SERVICE', 'tesseract'), // tesseract or api
        'tesseract_path' => env('TESSERACT_PATH', 'tesseract'),
        'api_url' => env('OCR_API_URL'),
        'api_key' => env('OCR_API_KEY'),
    ],
];
