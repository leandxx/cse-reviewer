<?php
/**
 * Pure PHP PDF text extractor — handles ToUnicode CMap (Google Docs / Skia PDFs).
 */
class PdfExtractor
{
    public static function extract(string $filePath): string
    {
        $data = file_get_contents($filePath);
        if ($data === false) throw new RuntimeException('Cannot read PDF file.');

        // Decompress all FlateDecode streams
        $data = self::inflateStreams($data);

        // Build per-font ToUnicode maps: fontRef -> [hexCode -> utf8char]
        $unicodeMaps = self::parseUnicodeMaps($data);

        $text = '';

        // Match content streams and track current font
        preg_match_all('/BT(.*?)ET/s', $data, $blocks);
        foreach ($blocks[1] as $block) {
            $currentFont = null;

            // Walk token by token
            $tokens = preg_split('/\s+/', trim($block), -1, PREG_SPLIT_NO_EMPTY);
            $stack  = [];

            foreach ($tokens as $tok) {
                if ($tok === 'Tf') {
                    // font name is two tokens back: /FontName size Tf
                    $currentFont = count($stack) >= 2 ? ltrim($stack[count($stack)-2], '/') : null;
                    $stack = [];
                } elseif ($tok === 'Tj') {
                    $str = array_pop($stack) ?? '';
                    $text .= self::decodeString($str, $currentFont, $unicodeMaps) . ' ';
                    $stack = [];
                } elseif ($tok === 'TJ') {
                    // array already collected as one token won't work — handled below
                    $stack = [];
                } else {
                    $stack[] = $tok;
                }
            }

            // Handle Tj strings: (text) Tj
            preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)\s*Tj/s', $block, $tj);
            foreach ($tj[1] as $t) {
                $text .= self::decodeString($t, $currentFont, $unicodeMaps) . ' ';
            }

            // Handle TJ arrays: [(text)-kern(text)] TJ
            preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjArr);
            foreach ($tjArr[1] as $t) {
                preg_match_all('/\(((?:[^()\\\\]|\\\\.)*)\)/', $t, $parts);
                foreach ($parts[1] as $p) {
                    $text .= self::decodeString($p, $currentFont, $unicodeMaps);
                }
                $text .= ' ';
            }
        }

        // Fallback for PDFs with no BT/ET
        if (trim($text) === '') {
            preg_match_all('/\(((?:[^()\\\\]|\\.){4,})\)/', $data, $raw);
            foreach ($raw[1] as $r) {
                $decoded = self::decodePdfLiteral($r);
                if (self::isPrintable($decoded)) $text .= $decoded . "\n";
            }
        }

        return self::cleanText($text);
    }

    // ── Inflate FlateDecode streams ──────────────────────────────────────────

    private static function inflateStreams(string $data): string
    {
        return preg_replace_callback(
            '/<<([^>]*)>>\s*stream\r?\n(.*?)\r?\nendstream/s',
            function ($m) {
                $dict   = $m[1];
                $stream = $m[2];
                if (stripos($dict, 'FlateDecode') === false) return $m[0];
                $inflated = @gzuncompress($stream);
                if ($inflated === false) $inflated = @gzinflate($stream);
                if ($inflated === false) return $m[0];
                return '<<' . $dict . ">>\nstream\n" . $inflated . "\nendstream";
            },
            $data
        );
    }

    // ── Parse ToUnicode CMaps ────────────────────────────────────────────────

    private static function parseUnicodeMaps(string $data): array
    {
        $maps = [];

        // Find all ToUnicode references: /ToUnicode X Y R
        preg_match_all('/\/ToUnicode\s+(\d+)\s+(\d+)\s+R/', $data, $refs, PREG_SET_ORDER);
        foreach ($refs as $ref) {
            $objId = $ref[1];
            $map   = self::parseCMapForObj($objId, $data);
            if ($map) $maps[$objId] = $map;
        }

        // Associate fonts with their ToUnicode object
        // /Font << /F1 << ... /ToUnicode X Y R ... >> >>
        $fontMaps = [];
        preg_match_all('/\/(\w+)\s*<<([^>]{0,2000})>>/s', $data, $fontObjs, PREG_SET_ORDER);
        foreach ($fontObjs as $fo) {
            if (preg_match('/\/ToUnicode\s+(\d+)\s+\d+\s+R/', $fo[2], $tm)) {
                $fontMaps[$fo[1]] = $maps[$tm[1]] ?? [];
            }
        }

        return $fontMaps;
    }

    private static function parseCMapForObj(string $objId, string $data): ?array
    {
        // Find the object body
        if (!preg_match('/' . $objId . '\s+\d+\s+obj\b(.*?)endobj/s', $data, $m)) return null;
        $body = $m[1];

        // Extract stream content
        if (!preg_match('/stream\r?\n(.*?)\r?\nendstream/s', $body, $sm)) return null;
        $cmap = $sm[1];

        return self::parseCMapContent($cmap);
    }

    private static function parseCMapContent(string $cmap): array
    {
        $map = [];

        // beginbfchar ... endbfchar
        preg_match_all('/beginbfchar(.*?)endbfchar/s', $cmap, $chars);
        foreach ($chars[1] as $block) {
            preg_match_all('/<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>/', $block, $pairs, PREG_SET_ORDER);
            foreach ($pairs as $p) {
                $map[strtolower($p[1])] = self::hexToUtf8($p[2]);
            }
        }

        // beginbfrange ... endbfrange
        preg_match_all('/beginbfrange(.*?)endbfrange/s', $cmap, $ranges);
        foreach ($ranges[1] as $block) {
            preg_match_all('/<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>/', $block, $triples, PREG_SET_ORDER);
            foreach ($triples as $t) {
                $start    = hexdec($t[1]);
                $end      = hexdec($t[2]);
                $uniStart = hexdec($t[3]);
                for ($i = $start; $i <= $end; $i++) {
                    $key       = strtolower(str_pad(dechex($i), strlen($t[1]), '0', STR_PAD_LEFT));
                    $map[$key] = self::codePointToUtf8($uniStart + ($i - $start));
                }
            }
        }

        return $map;
    }

    private static function hexToUtf8(string $hex): string
    {
        $out = '';
        for ($i = 0; $i < strlen($hex); $i += 4) {
            $out .= self::codePointToUtf8(hexdec(substr($hex, $i, 4)));
        }
        return $out;
    }

    private static function codePointToUtf8(int $cp): string
    {
        if ($cp < 0x80)   return chr($cp);
        if ($cp < 0x800)  return chr(0xC0 | ($cp >> 6))   . chr(0x80 | ($cp & 0x3F));
        if ($cp < 0x10000)return chr(0xE0 | ($cp >> 12))  . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
        return chr(0xF0 | ($cp >> 18)) . chr(0x80 | (($cp >> 12) & 0x3F))
             . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
    }

    // ── String decoding ──────────────────────────────────────────────────────

    private static function decodeString(string $s, ?string $font, array $maps): string
    {
        // Unescape PDF literal escapes first
        $s = self::decodePdfLiteral($s);

        $map = ($font && isset($maps[$font])) ? $maps[$font] : null;

        if (!$map) return self::isPrintable($s) ? $s : '';

        // Try 2-byte lookup (Adobe Identity uses 2-byte glyph codes)
        $out = '';
        $len = strlen($s);
        $i   = 0;
        while ($i < $len) {
            if ($i + 1 < $len) {
                $key2 = strtolower(bin2hex(substr($s, $i, 2)));
                if (isset($map[$key2])) { $out .= $map[$key2]; $i += 2; continue; }
            }
            $key1 = strtolower(bin2hex(substr($s, $i, 1)));
            if (isset($map[$key1])) { $out .= $map[$key1]; $i++; continue; }
            // Fallback: if printable ASCII, keep it
            $byte = ord($s[$i]);
            if ($byte >= 0x20 && $byte <= 0x7E) $out .= $s[$i];
            $i++;
        }
        return $out;
    }

    private static function decodePdfLiteral(string $s): string
    {
        $s = str_replace(['\\n','\\r','\\t','\\\\','\\(','\\)'],
                         ["\n", "\r", "\t", '\\',  '(',   ')'], $s);
        return preg_replace_callback('/\\\\([0-7]{1,3})/', fn($m) => chr(octdec($m[1])), $s);
    }

    private static function isPrintable(string $s): bool
    {
        $printable = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $s);
        return strlen($s) === 0 || strlen($printable) > strlen($s) * 0.5;
    }

    private static function cleanText(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E\x{0080}-\x{FFFF}\n\r\t]/u', ' ', $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
