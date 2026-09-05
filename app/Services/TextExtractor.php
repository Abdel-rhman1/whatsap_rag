<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Exception;
use Illuminate\Support\Facades\Http;

class TextExtractor
{
    public function extract(string $path, string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => $this->extractPdf($path),
            'docx' => $this->extractDocx($path),
            'txt', 'md', 'html' => file_get_contents($path),
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' => $this->extractImage($path),
            default => throw new Exception("Unsupported file type: {$extension}"),
        };
    }

    protected function extractPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    protected function extractDocx(string $path): string
    {
        $phpWord = WordIOFactory::load($path);
        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }
        return $text;
    }

    /**
     * Extract text from image using OCR (Tesseract)
     */
    protected function extractImage(string $path): string
    {
        $ocrService = config('rag.ocr.service', 'tesseract');
        
        if ($ocrService === 'tesseract') {
            return $this->extractWithTesseract($path);
        } elseif ($ocrService === 'api') {
            return $this->extractWithOcrApi($path);
        }
        
        throw new Exception("No OCR service configured");
    }

    /**
     * Extract text using Tesseract OCR
     */
    protected function extractWithTesseract(string $path): string
    {
        $tesseractPath = config('rag.ocr.tesseract_path', 'tesseract');
        $outputPath = sys_get_temp_dir() . '/' . uniqid('ocr_');
        
        // Support both Arabic and English
        $languages = 'ara+eng';
        
        $command = sprintf(
            '%s %s %s -l %s 2>&1',
            escapeshellcmd($tesseractPath),
            escapeshellarg($path),
            escapeshellarg($outputPath),
            escapeshellarg($languages)
        );
        
        exec($command, $output, $returnCode);
        
        $textFile = $outputPath . '.txt';
        
        if ($returnCode !== 0 || !file_exists($textFile)) {
            throw new Exception("Tesseract OCR failed: " . implode("\n", $output));
        }
        
        $text = file_get_contents($textFile);
        @unlink($textFile);
        
        return $text;
    }

    /**
     * Extract text using external OCR API
     */
    protected function extractWithOcrApi(string $path): string
    {
        $ocrApiUrl = config('rag.ocr.api_url');
        $ocrApiKey = config('rag.ocr.api_key');
        
        if (!$ocrApiUrl) {
            throw new Exception("OCR API URL not configured");
        }
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $ocrApiKey
        ])->attach(
            'image',
            file_get_contents($path),
            basename($path)
        )->post($ocrApiUrl);

        if ($response->failed()) {
            throw new Exception("OCR API failed: " . $response->body());
        }

        $result = $response->json();
        return $result['text'] ?? '';
    }
}

