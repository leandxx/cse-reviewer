<?php
/**
 * Pure PHP PDF text extractor — no Composer, no dependencies.
 * Handles most text-based PDFs (not scanned/image PDFs).
 */
class PdfExtractor
{
    public static function extract(string $filePath): string
    {
        $data = file_get_contents($filePath);
        if ($data === false) throw new RuntimeException('Cannot read PDF file.');

        $text = '';

        // Extract all text from BT...ET blocks
        preg_match_all('/BT(.*?)ET/s', $data, $blocks);
        foreach ($blocks[1] as $block) {
            // Tj and TJ operators
            preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $block, $tj);
            foreach ($tj[1] as $t) $text .= self::decodePdfString($t) . ' ';

            preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArr);
            foreach ($tjArr[1] as $t) {
                preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/', $t, $parts);
                foreach ($parts[1] as $p) $text .= self::decodePdfString($p);
                $text .= ' ';
            }
        }

        // Fallback: grab raw string literals if BT/ET found nothing
        if (trim($text) === '') {
            preg_match_all('/\(((?:[^()\\\\]|\\\\.){4,})\)/', $data, $raw);
            foreach ($raw[1] as $r) {
                $decoded = self::decodePdfString($r);
                if (self::isPrintable($decoded)) $text .= $decoded . "\n";
            }
        }

        return self::cleanText($text);
    }

    private static function decodePdfString(string $s): string
    {
        // Unescape PDF escape sequences
        $s = str_replace(['\\n','\\r','\\t','\\\\','\\(','\\)'],
                         ["\n", "\r", "\t", '\\',  '(',   ')'], $s);
        // Octal escapes
        $s = preg_replace_callback('/\\\\([0-7]{1,3})/', function($m) {
            return chr(octdec($m[1]));
        }, $s);
        return $s;
    }

    private static function isPrintable(string $s): bool
    {
        $printable = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $s);
        return strlen($printable) > strlen($s) * 0.6;
    }

    private static function cleanText(string $text): string
    {
        // Remove non-printable except newlines
        $text = preg_replace('/[^\x20-\x7E\n\r\t]/', ' ', $text);
        // Collapse multiple spaces/newlines
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
