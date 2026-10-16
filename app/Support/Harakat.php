<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Baris (harakat) dan suku kata: بَ = ba, بِ = bi, بُ = bu.
 */
class Harakat
{
    public const VOWELS = [
        'a' => ['mark' => "\u{064E}", 'name' => 'Fathah', 'where' => 'garis kecil condong di ATAS huruf'],
        'i' => ['mark' => "\u{0650}", 'name' => 'Kasrah', 'where' => 'garis kecil condong di BAWAH huruf'],
        'u' => ['mark' => "\u{064F}", 'name' => 'Dammah', 'where' => 'seperti wau kecil di ATAS huruf'],
    ];

    /**
     * Ejaan rumi konsonan ikut gaya kelas al-Quran di Malaysia (Kho, Ro, Sod, Dhod, Tha …).
     * Ha berat (ḥ) dan Sod (ṣ) diberi titik bawah supaya tidak sama dengan Ha ringan dan Sin;
     * Ain diberi ‘ supaya berbeza daripada Hamzah.
     */
    private const RUMI = [
        'ء' => '', 'ب' => 'b', 'ت' => 't', 'ث' => 'ts', 'ج' => 'j', 'ح' => 'ḥ', 'خ' => 'kh',
        'د' => 'd', 'ذ' => 'dz', 'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sy', 'ص' => 'ṣ',
        'ض' => 'dh', 'ط' => 'th', 'ظ' => 'zh', 'ع' => '‘', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
        'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h', 'و' => 'w', 'ي' => 'y',
    ];

    /** Huruf tebal (isti'la' + Ro): berbaris atas dibaca "o" — خَ kho, صَ ṣo, رَ ro. */
    private const TEBAL = ['خ', 'ص', 'ض', 'غ', 'ط', 'ق', 'ظ', 'ر'];

    /** Hamzah berbaris ditulis di atas/bawah alif. */
    private const HAMZAH_CARRIER = ['a' => 'أَ', 'i' => 'إِ', 'u' => 'أُ'];

    public const LESSONS = [
        'a' => ['title' => 'Fathah', 'vowels' => ['a'], 'example' => 'بَ = ba'],
        'i' => ['title' => 'Kasrah', 'vowels' => ['i'], 'example' => 'بِ = bi'],
        'u' => ['title' => 'Dammah', 'vowels' => ['u'], 'example' => 'بُ = bu'],
        'campur' => ['title' => 'Campur a · i · u', 'vowels' => ['a', 'i', 'u'], 'example' => 'بَ بِ بُ'],
    ];

    public static function syllable(string $letter, string $vowel): array
    {
        $text = $letter === 'ء' ? self::HAMZAH_CARRIER[$vowel] : $letter.self::VOWELS[$vowel]['mark'];

        $sound = $vowel === 'a' && in_array($letter, self::TEBAL, true) ? 'o' : $vowel;

        return ['letter' => $letter, 'vowel' => $vowel, 'text' => $text, 'rumi' => self::RUMI[$letter].$sound];
    }

    /**
     * Semua suku kata bagi satu baris, ikut susunan abjad. Alif tidak menerima baris (ia jadi hamzah).
     */
    public static function row(Collection $letters, string $vowel): array
    {
        return $letters->sortBy('order_num')
            ->filter(fn ($l) => isset(self::RUMI[$l->arabic]))
            ->map(fn ($l) => self::syllable($l->arabic, $vowel) + ['name' => $l->name])
            ->values()
            ->all();
    }

    /**
     * Soalan kuiz baris.
     *   see  : lihat suku kata → pilih bacaan rumi
     *   hear : dengar suku kata → pilih tulisan
     * Pilihan salah = baris lain bagi huruf sama (uji baris) + huruf serupa dengan baris sama (uji huruf).
     */
    public static function questions(Collection $letters, string $lesson, string $mode, int $count): array
    {
        $vowels = self::LESSONS[$lesson]['vowels'];
        $pool = $letters->filter(fn ($l) => isset(self::RUMI[$l->arabic]))->values();

        $questions = [];
        for ($i = 0; $i < $count; $i++) {
            $letter = $pool->random();
            $vowel = $vowels[array_rand($vowels)];
            $answer = self::syllable($letter->arabic, $vowel);
            $qMode = $mode === 'mixed' ? (random_int(0, 1) ? 'see' : 'hear') : $mode;

            $wrong = collect($vowels)->reject(fn ($v) => $v === $vowel)
                ->map(fn ($v) => self::syllable($letter->arabic, $v));

            $families = $qMode === 'hear' ? LetterFamilies::SOUNDALIKES : LetterFamilies::LOOKALIKES;
            $wrong = $wrong->concat(
                LetterFamilies::similar($letter->arabic, $pool, $families, 3 - $wrong->count())
                    ->map(fn ($c) => self::syllable($c, $vowel))
            );

            $key = $qMode === 'see' ? 'rumi' : 'text';
            $questions[] = [
                'mode' => $qMode,
                'text' => $answer['text'],
                'rumi' => $answer['rumi'],
                'vowel_name' => self::VOWELS[$vowel]['name'],
                'answer' => $answer[$key],
                'options' => $wrong->pluck($key)->push($answer[$key])->unique()->shuffle()->values()->all(),
            ];
        }

        return $questions;
    }
}
