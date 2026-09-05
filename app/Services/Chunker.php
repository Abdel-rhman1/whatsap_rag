<?php

namespace App\Services;

class Chunker
{
    public function chunk(string $text, int $size = 1000, int $overlap = 200): array
    {
        $chunks = [];
        $textLength = mb_strlen($text);
        $start = 0;

        while ($start < $textLength) {
            $end = $start + $size;
            
            // Try to find a space near the end to avoid cutting words
            if ($end < $textLength) {
                $lastSpace = mb_strrpos(mb_substr($text, $start, $size), ' ');
                if ($lastSpace !== false && $lastSpace > ($size * 0.8)) {
                    $end = $start + $lastSpace;
                }
            }

            $chunk = mb_substr($text, $start, $end - $start);
            $chunks[] = trim($chunk);
            
            $start = $end - $overlap;
            
            if ($start >= $textLength - 10) break; // Avoid tiny tiny chunks at end
        }

        return array_filter($chunks);
    }
}
