<?php

declare(strict_types=1);

namespace Lucent\Support\Str;

use Illuminate\Support\Facades\Log;
use Normalizer;
use Throwable;

/**
 * A library of letter manipulation functions. Supports operations on UTF-8 letters.
 *
 * @author Damian Ułan <damian.ulan@protonmail.com>
 * @copyright 2026 damianulan
 */
class Alphabet
{
    public const A = 'A';

    public const B = 'B';

    public const C = 'C';

    public const D = 'D';

    public const E = 'E';

    public const F = 'F';

    public const G = 'G';

    public const H = 'H';

    public const I = 'I';

    public const J = 'J';

    public const K = 'K';

    public const L = 'L';

    public const M = 'M';

    public const N = 'N';

    public const O = 'O';

    public const P = 'P';

    public const Q = 'Q';

    public const R = 'R';

    public const S = 'S';

    public const T = 'T';

    public const U = 'U';

    public const V = 'V';

    public const W = 'W';

    public const X = 'X';

    public const Y = 'Y';

    public const Z = 'Z';

    /**
     * Get position of a letter (including UTF-8 letters like Ą, É, Ç, etc.) in the Latin alphabet.
     *
     * @param  string  $letter  Single letter
     * @return int|null Position (1–26) or null if not a Latin letter
     */
    public static function getAlphabetPosition(string $letter): ?int
    {
        $letter = mb_substr($letter, 0, 1);
        $normalized = self::normalizeToASCII($letter);
        $normalized = mb_strtoupper($normalized);

        if (1 !== mb_strlen($normalized)) {
            return null; // e.g., ß → ss (length > 1), invalid
        }

        $ascii = ord($normalized);

        return ($ascii >= 65 && $ascii <= 90) ? $ascii - 64 : null;
    }

    /**
     * Normalize a UTF-8 letter (e.g., Ą, É, Ç) to its base ASCII character.
     * Covers Polish and most accented Latin characters.
     *
     * @return string Normalized single-letter string
     */
    private static function normalizeToASCII(string $char): string
    {
        static $map = [
            'Ą' => 'A',
            'Ć' => 'C',
            'Ę' => 'E',
            'Ł' => 'L',
            'Ń' => 'N',
            'Ó' => 'O',
            'Ś' => 'S',
            'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'TH',
            'ß' => 'SS',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'þ' => 'th',
            'ÿ' => 'y',
        ];

        // Use intl Normalizer if available
        $ascii = '';
        try {
            if (class_exists(Normalizer::class)) {
                $normalized = Normalizer::normalize($char, Normalizer::FORM_D);
                $ascii = preg_replace('/\p{Mn}/u', '', $normalized);

                $ascii = mb_substr($ascii ?? '', 0, 1);
            }
        } catch (Throwable $e) {
            Log::debug('Lucent\Support\Str\Alphabet::normalizeToASCII() default failed upon: ' . $e->getMessage());
            $ascii = $map[$char] ?? $char;
        }

        return $ascii;
    }
}
