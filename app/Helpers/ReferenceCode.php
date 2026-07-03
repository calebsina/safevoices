<?php

namespace App\Helpers;

use App\Models\Report\Report;

/**
 * Generates the public case reference code and the follow-up PIN.
 *
 * The code uses an unambiguous alphabet (config/safevoice.php) because
 * reporters may need to read it aloud or type it on a basic phone.
 */
class ReferenceCode
{
    /**
     * Generate a unique reference code, e.g. "SV-7F3K-9Q2".
     */
    public static function generate(): string
    {
        $config = config('safevoice.reference_code');

        do {
            $groups = array_map(
                fn (int $length) => self::randomBlock($config['alphabet'], $length),
                $config['groups']
            );

            $code = $config['prefix'].'-'.implode('-', $groups);
        } while (Report::withTrashed()->where('reference_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a numeric follow-up PIN (returned to the reporter ONCE,
     * only its hash is persisted).
     */
    public static function pin(): string
    {
        $length = (int) config('safevoice.pin.length', 6);

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    private static function randomBlock(string $alphabet, int $length): string
    {
        $block = '';
        $max = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $block .= $alphabet[random_int(0, $max)];
        }

        return $block;
    }
}
