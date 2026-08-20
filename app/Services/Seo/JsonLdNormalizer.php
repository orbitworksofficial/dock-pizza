<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * Repairs the two things that mechanically break JSON-LD pasted from schema
 * generators, so editors are not asked to fix them by hand.
 */
class JsonLdNormalizer
{
    /**
     * Clean a pasted block, returning the decoded structure or an error.
     *
     * @return array{ok: true, data: array, json: string}|array{ok: false, error: string}
     */
    public function normalize(?string $raw): array
    {
        $input = trim((string) $raw);

        if ($input === '') {
            return ['ok' => true, 'data' => [], 'json' => ''];
        }

        $input = $this->stripScriptWrapper($input);
        $input = $this->escapeNewlinesInsideStrings($input);

        $decoded = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'ok' => false,
                'error' => 'That is not valid JSON-LD: ' . json_last_error_msg()
                    . '. Paste the JSON from your schema generator, with or without the <script> wrapper.',
            ];
        }

        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'JSON-LD must be an object or an array of objects.'];
        }

        return [
            'ok' => true,
            'data' => $decoded,
            // Re-encoded, so malformed input can never reach the page as raw text.
            'json' => (string) json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Remove a surrounding <script type="application/ld+json"> … </script>.
     */
    private function stripScriptWrapper(string $input): string
    {
        if (stripos($input, '<script') === false) {
            return $input;
        }

        if (preg_match('#<script\b[^>]*>(.*?)</script\s*>#is', $input, $matches) === 1) {
            return trim($matches[1]);
        }

        // An opening tag with no close — drop the tag and keep the body.
        return trim(preg_replace('#</?script\b[^>]*>#i', '', $input) ?? $input);
    }

    /**
     * Convert real line breaks that appear *inside string values* into \n.
     *
     * JSON forbids literal control characters in strings, so a multi-paragraph
     * FAQ answer fails to parse. The newlines that indent and format the JSON
     * itself are structural and must be left alone — hence tracking whether
     * the scanner is currently inside a string, and honouring backslash
     * escaping so an escaped quote does not appear to close one.
     */
    private function escapeNewlinesInsideStrings(string $input): string
    {
        $out = '';
        $inString = false;
        $escaped = false;
        $length = strlen($input);

        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];

            if ($escaped) {
                // Previous char was a backslash: this one is literal.
                $out .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $out .= $char;
                // Only meaningful inside a string; outside, JSON has no escapes.
                $escaped = $inString;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                $out .= $char;
                continue;
            }

            if ($inString) {
                if ($char === "\n") {
                    $out .= '\\n';
                    continue;
                }
                if ($char === "\r") {
                    // Collapse CRLF into a single escape rather than emitting two.
                    if ($i + 1 < $length && $input[$i + 1] === "\n") {
                        $i++;
                    }
                    $out .= '\\n';
                    continue;
                }
                if ($char === "\t") {
                    $out .= '\\t';
                    continue;
                }
            }

            $out .= $char;
        }

        return $out;
    }
}
