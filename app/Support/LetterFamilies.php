<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Keluarga huruf yang mudah tertukar — sumber pilihan salah untuk kuiz huruf dan kuiz baris.
 */
class LetterFamilies
{
    /**
     * Huruf yang mudah tertukar dari segi bentuk — dijadikan pilihan salah
     * supaya kuiz menguji beza sebenar, bukan sekadar teka yang paling jauh.
     */
    public const LOOKALIKES = [
        ['ب', 'ت', 'ث', 'ن', 'ي'],
        ['ج', 'ح', 'خ'],
        ['د', 'ذ', 'ر', 'ز'],
        ['س', 'ش'],
        ['ص', 'ض', 'ط', 'ظ'],
        ['ع', 'غ', 'ء'],
        ['ف', 'ق'],
        ['ك', 'ل'],
        ['و', 'ر', 'ه'],
        ['م', 'ه', 'ة'],
    ];

    /** Huruf yang bunyinya hampir sama — untuk mod dengar. */
    public const SOUNDALIKES = [
        ['س', 'ص', 'ث'],
        ['ت', 'ط'],
        ['د', 'ض'],
        ['ذ', 'ز', 'ظ'],
        ['ه', 'ح', 'خ'],
        ['ك', 'ق'],
        ['ء', 'ع', 'ا'],
        ['غ', 'خ'],
        ['ش', 'س', 'ج'],
    ];

    /**
     * Pilih $count huruf lain sebagai pilihan salah: utamakan yang serupa, lengkapkan dengan rawak.
     *
     * @param  Collection  $pool  koleksi yang mempunyai atribut 'arabic'
     * @return Collection<string> aksara arab
     */
    public static function similar(string $arabic, Collection $pool, array $families, int $count = 3): Collection
    {
        $available = $pool->pluck('arabic')->reject(fn ($c) => $c === $arabic)->unique()->values();

        $similar = collect($families)
            ->filter(fn ($family) => in_array($arabic, $family, true))
            ->flatten()
            ->unique()
            ->intersect($available);

        $picked = $similar->shuffle()->take($count);

        if ($picked->count() < $count) {
            $picked = $picked->concat($available->diff($picked)->shuffle()->take($count - $picked->count()));
        }

        return $picked->values();
    }
}
