<?php

namespace App\Support;

/**
 * Sumber audio sebutan, ikut keutamaan:
 *   1. Rakaman guru (DB: arabic_letters.audio_url — Mod Guru)
 *   2. Suara AI (public/audio/ai/index.json — php artisan audio:ai)
 *   3. Rakaman terbuka Lingua Libre (public/audio/huruf/index.json)
 *   4. TTS pelayar — dalam speakArabic() jika tiada di atas.
 */
class OpenAudio
{
    private static ?array $entries = null;

    private static ?array $ai = null;

    /** Rakaman Lingua Libre, ikut huruf. */
    public static function entries(): array
    {
        if (self::$entries === null) {
            $data = self::readJson('audio/huruf/index.json');
            self::$entries = collect($data['letters'] ?? [])->keyBy('letter')->all();
        }

        return self::$entries;
    }

    /** Audio AI: ['items' => [teks => entri], 'generator' => [...]]. */
    public static function ai(): array
    {
        if (self::$ai === null) {
            $data = self::readJson('audio/ai/index.json') ?? [];
            // Abaikan entri yang failnya tiada (cth. belum di-deploy).
            $data['items'] = array_filter($data['items'] ?? [], fn ($e) => is_file(public_path(ltrim($e['file'], '/'))));
            self::$ai = $data;
        }

        return self::$ai;
    }

    public static function for(string $letter): ?array
    {
        return self::entries()[$letter] ?? null;
    }

    /** Entri untuk pelayar bagi satu huruf (tanpa rakaman guru): AI dahulu, kemudian Lingua Libre. */
    public static function clientEntry(string $letter): ?array
    {
        if ($ai = self::aiEntry($letter)) {
            return $ai;
        }

        $e = self::for($letter);

        return $e ? [
            'url' => $e['file'],
            'source' => 'open',
            'credit' => $e['speaker'].' · '.$e['license'],
            'page' => $e['source_page'],
        ] : null;
    }

    /**
     * Peta audio untuk pelayar: setiap huruf + setiap suku kata AI.
     *
     * @param  iterable  $letters  ArabicLetter
     */
    public static function clientMap(iterable $letters): array
    {
        $map = [];

        // Suku kata (بَ بِ بُ …) — hanya dari AI.
        foreach (self::ai()['items'] as $text => $item) {
            if (($item['set'] ?? null) === 'baris') {
                $map[$text] = self::aiEntry($text);
            }
        }

        foreach ($letters as $l) {
            $entry = $l->audio_url
                ? ['url' => $l->audio_url, 'source' => 'guru', 'credit' => 'Rakaman guru', 'page' => null]
                : self::clientEntry($l->arabic);
            if ($entry) {
                $map[$l->arabic] = $entry;
            }
        }

        return $map;
    }

    /** Semua fail audio statik — untuk service worker (simpanan offline). */
    public static function staticFiles(): array
    {
        return array_values(array_unique(array_merge(
            array_column(self::ai()['items'], 'file'),
            array_column(self::entries(), 'file'),
        )));
    }

    private static function aiEntry(string $text): ?array
    {
        $item = self::ai()['items'][$text] ?? null;
        if (!$item) {
            return null;
        }
        $gen = self::ai()['generator'] ?? [];

        return [
            'url' => $item['file'],
            'source' => 'ai',
            'credit' => 'Suara AI · '.($gen['provider'] ?? 'OpenAI').' '.($gen['model'] ?? ''),
            'page' => null,
        ];
    }

    private static function readJson(string $relative): ?array
    {
        $path = public_path($relative);

        return is_file($path) ? json_decode(file_get_contents($path), true) : null;
    }

    /** Untuk ujian — kosongkan cache statik selepas fail index ditukar. */
    public static function flush(): void
    {
        self::$entries = null;
        self::$ai = null;
    }
}
