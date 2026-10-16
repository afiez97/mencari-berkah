<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Pelan 21 hari untuk pemula dewasa (~15–20 minit sehari).
 *
 * Hari 1–8   : kenal huruf baru ikut keluarga bentuk (3–5 huruf sehari).
 * Hari 9–14  : kukuhkan — bunyi, bentuk sambung, huruf yang serupa.
 * Hari 15–21 : ulang kaji ringan supaya hafalan kekal, ditutup dengan ujian akhir.
 */
class StudyPlan
{
    public const LENGTH = 21;

    private const INTRO = [
        1 => ['title' => 'Alif & keluarga Ba', 'letters' => ['ا', 'ب', 'ت', 'ث'],
              'tip' => 'Ba, Ta dan Tsa berbadan sama — bezanya hanya titik: 1 di bawah, 2 di atas, 3 di atas. Alif hanya satu batang tegak.'],
        2 => ['title' => 'Keluarga Jim', 'letters' => ['ج', 'ح', 'خ'],
              'tip' => 'Jim titik di dalam perut, Ha (berat) tiada titik, Kho titik di atas kepala. Ha berat dari TENGAH halkum (seperti menghembus cermin); Kho garau dari HUJUNG halkum.'],
        3 => ['title' => 'Dal, Dzal, Ro, Zai', 'letters' => ['د', 'ذ', 'ر', 'ز'],
              'tip' => 'Dal/Dzal bersudut dan duduk atas garisan; Ro/Zai melengkung dan turun di bawah garisan. Keempat-empatnya tidak bersambung ke huruf selepasnya.'],
        4 => ['title' => 'Keluarga gigi & Sod', 'letters' => ['س', 'ش', 'ص', 'ض'],
              'tip' => 'Sin ada 3 gigi; Syin 3 gigi + 3 titik. Sod/Dhod ada gelung bujur dan bunyinya TEBAL — lidah naik ke lelangit.'],
        5 => ['title' => 'Tha, Zho, Ain, Ghain', 'letters' => ['ط', 'ظ', 'ع', 'غ'],
              'tip' => 'Tha/Zho: gelung + batang tegak, bunyi tebal. Ain/Ghain: kepala terbuka seperti huruf "c". Perhatikan bentuk tengah Ain (ـعـ) yang sangat berbeza.'],
        6 => ['title' => 'Fa, Qaf, Kaf, Lam', 'letters' => ['ف', 'ق', 'ك', 'ل'],
              'tip' => 'Fa 1 titik dan ekor mendatar; Qaf 2 titik dan ekor mangkuk dalam. Kaf ada "tanda kecil" di dalam; Lam tinggi seperti kail.'],
        7 => ['title' => 'Mim, Nun, Waw, Ha, Ya', 'letters' => ['م', 'ن', 'ه', 'و', 'ي'],
              'tip' => 'Nun: mangkuk dalam + 1 titik atas. Ya: 2 titik di bawah. Ha ringan (ه) dari PANGKAL halkum; bentuknya berubah paling banyak — lihat keempat-empat bentuknya di halaman Belajar.'],
        8 => ['title' => 'Hamzah & Alif', 'letters' => ['ء'],
              'tip' => 'Alif (ا) tiada bunyi sendiri. Hamzah (ء) ialah hentian di tekak; ia duduk di atas alif أ, bawah alif إ, atas wau ؤ atau atas ya ئ. Hari ini juga ulang kaji semua huruf.'],
    ];

    private const PRACTICE = [
        9  => ['title' => 'Latih telinga', 'mode' => 'audio', 'count' => 20,
               'tip' => 'Fokus pasangan bunyi yang hampir sama: س/ص, ت/ط, د/ض, ذ/ز/ظ, ه/ح, ك/ق, ء/ع.'],
        10 => ['title' => 'Bentuk bersambung', 'mode' => 'form', 'count' => 20,
               'tip' => 'Enam huruf tidak bersambung ke kiri: ا د ذ ر ز و. Huruf selepasnya mesti mula dengan bentuk awal.'],
        11 => ['title' => 'Huruf yang serupa', 'mode' => 'mixed', 'count' => 30,
               'tip' => 'Selepas kuiz, tekan "Latih huruf yang salah sahaja" sehingga semuanya betul.'],
        12 => ['title' => 'Dengar semua huruf', 'mode' => 'audio', 'count' => 'all',
               'tip' => 'Sebut setiap huruf dengan kuat selepas mendengarnya — mulut dan telinga sama-sama menghafal.'],
        13 => ['title' => 'Semua bentuk sambung', 'mode' => 'form', 'count' => 'all',
               'tip' => 'Jika tersilap, buka huruf itu di halaman Belajar dan lihat keempat-empat bentuknya.'],
        14 => ['title' => 'Ujian 2 minggu', 'mode' => 'mixed', 'count' => 'all', 'target' => 90,
               'tip' => 'Sasaran 90%. Jika belum capai, ulang ujian ini esok sebelum teruskan.'],
    ];

    private const REVIEW_TIP = 'Ulang kaji ringan: habiskan kad yang perlu diulang, kemudian kuiz pendek. 10–15 minit sudah memadai.';

    private const FINAL = [
        'title' => 'Ujian akhir', 'mode' => 'mixed', 'count' => 'all', 'target' => 95,
        'tip' => 'Sasaran 95%. Jika lulus, anda sudah bersedia untuk peringkat seterusnya: baris (fathah, kasrah, dammah).',
    ];

    /** Susunan huruf diperkenalkan (ikut keluarga bentuk). */
    public static function introOrder(): array
    {
        return array_merge(...array_column(self::INTRO, 'letters'));
    }

    /**
     * @param  Collection  $letters  semua ArabicLetter
     */
    public static function days(Collection $letters): array
    {
        $idOf = $letters->pluck('id', 'arabic');
        $nameOf = $letters->pluck('name', 'arabic');
        $sesi = route('hafalan.sesi');
        $quiz = route('quiz.play');

        $days = [];
        $introduced = [];

        foreach (self::INTRO as $n => $intro) {
            // Abaikan huruf yang belum di-seed supaya halaman tidak rosak.
            $intro['letters'] = array_values(array_filter($intro['letters'], fn ($c) => $idOf->has($c)));
            $newIds = array_map(fn ($c) => $idOf[$c], $intro['letters']);
            $introduced = array_merge($introduced, $newIds);

            $days[] = [
                'day' => $n,
                'phase' => 'Kenal huruf',
                'title' => $intro['title'],
                'letters' => $intro['letters'],
                'names' => array_map(fn ($c) => $nameOf[$c], $intro['letters']),
                'tip' => $intro['tip'],
                'sesi_url' => $sesi.'?'.http_build_query(['group' => 'all', 'baru' => count($newIds), 'baru_ids' => $newIds]),
                // Kuiz campur huruf hari ini dengan semua huruf sebelumnya (interleaving).
                'quiz_label' => 'Kuiz '.($n === 8 ? 15 : 10).' soalan (huruf setakat ini)',
                'quiz_url' => $quiz.'?'.http_build_query(['mode' => 'mixed', 'count' => $n === 8 ? 15 : 10, 'ids' => $introduced, 'hari' => $n]),
                'target' => null,
            ];
        }

        $practice = self::PRACTICE;
        for ($n = 15; $n < self::LENGTH; $n++) {
            $practice[$n] = ['title' => 'Ulang kaji ringan', 'mode' => 'mixed', 'count' => 15, 'tip' => self::REVIEW_TIP];
        }
        $practice[self::LENGTH] = self::FINAL;

        $modeLabel = ['audio' => 'Dengar Bunyi', 'form' => 'Bentuk Sambung', 'mixed' => 'Campuran'];

        foreach ($practice as $n => $p) {
            $days[] = [
                'day' => $n,
                'phase' => $n <= 14 ? 'Kukuhkan' : 'Kekalkan',
                'title' => $p['title'],
                'letters' => [],
                'names' => [],
                'tip' => $p['tip'],
                'sesi_url' => $sesi.'?group=all',
                'quiz_label' => 'Kuiz '.$modeLabel[$p['mode']].' — '.($p['count'] === 'all' ? 'semua huruf' : $p['count'].' soalan'),
                'quiz_url' => $quiz.'?'.http_build_query(['mode' => $p['mode'], 'count' => $p['count'], 'hari' => $n]),
                'target' => $p['target'] ?? null,
            ];
        }

        return $days;
    }
}
