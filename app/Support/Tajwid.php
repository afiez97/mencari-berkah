<?php

namespace App\Support;

/**
 * Cara sebutan: makhraj (tempat keluar huruf) dan sifat huruf.
 *
 * Kawasan makhraj dan definisi sifat mengikut akuislam.com/quran/huruf-hijaiyyah.
 * Senarai huruf bagi setiap sifat mengikut ilmu tajwid standard; di mana halaman
 * tersebut menulis ا dalam senarai sifat, ia merujuk kepada hamzah (ء).
 */
class Tajwid
{
    public const SOURCE = 'https://akuislam.com/quran/huruf-hijaiyyah/';

    public const REGIONS = [
        'jauf'     => ['name' => 'al-Jauf', 'desc' => 'rongga mulut dan kerongkong'],
        'halq'     => ['name' => 'al-Halq', 'desc' => 'kerongkong'],
        'lisan'    => ['name' => 'al-Lisan', 'desc' => 'lidah'],
        'syafatan' => ['name' => 'asy-Syafatan', 'desc' => 'bibir'],
    ];

    /** [kawasan makhraj, cara sebut] */
    private const MAKHRAJ = [
        'ا' => [['jauf'], 'Udara keluar dari rongga mulut tanpa sekatan — alif memanjangkan bunyi "a" sebanyak 2 harakat.'],
        'ب' => [['syafatan'], 'Rapatkan dua bibir, kemudian lepaskan.'],
        'ت' => [['lisan'], 'Hujung lidah menyentuh pangkal gigi hadapan atas. Nipis, ada hembusan nafas.'],
        'ث' => [['lisan'], 'Hujung lidah menyentuh hujung gigi hadapan atas (lidah terkeluar sedikit). Nipis, ada hembusan.'],
        'ج' => [['lisan'], 'Tengah lidah menekan lelangit keras.'],
        'ح' => [['halq'], 'Dari TENGAH halkum. Bersih tanpa garau — seperti menghembus cermin supaya berkabus.'],
        'خ' => [['halq'], 'Dari HUJUNG halkum (paling dekat dengan mulut). Garau dan tebal.'],
        'د' => [['lisan'], 'Hujung lidah menyentuh pangkal gigi hadapan atas (sama tempat dengan Ta), tanpa hembusan.'],
        'ذ' => [['lisan'], 'Hujung lidah menyentuh hujung gigi hadapan atas (lidah terkeluar sedikit), tanpa hembusan.'],
        'ر' => [['lisan'], 'Hujung lidah ke gusi atas, bergetar sekali sahaja. Tebal bila berbaris atas atau depan.'],
        'ز' => [['lisan'], 'Hujung lidah dekat gigi hadapan bawah — bunyi berdesir seperti lebah.'],
        'س' => [['lisan'], 'Hujung lidah dekat gigi hadapan bawah — bunyi berdesir nipis, ada hembusan.'],
        'ش' => [['lisan'], 'Tengah lidah ke lelangit keras — angin tersembur ke seluruh mulut.'],
        'ص' => [['lisan'], 'Hujung lidah dekat gigi hadapan bawah, belakang lidah naik ke lelangit — berdesir dan TEBAL.'],
        'ض' => [['lisan'], 'Tepi lidah (kiri atau kanan) menekan geraham atas — TEBAL dan memanjang.'],
        'ط' => [['lisan'], 'Hujung lidah ke pangkal gigi hadapan atas, lidah naik memenuhi mulut — T yang TEBAL.'],
        'ظ' => [['lisan'], 'Hujung lidah ke hujung gigi hadapan atas, lidah naik ke lelangit — Dz yang TEBAL.'],
        'ع' => [['halq'], 'Dari TENGAH halkum (sama tempat dengan Ha berat) — suara tersekat sedikit lalu dilepaskan.'],
        'غ' => [['halq'], 'Dari HUJUNG halkum (sama tempat dengan Kho) — seperti berkumur, tebal.'],
        'ف' => [['syafatan'], 'Perut bibir bawah menyentuh hujung gigi hadapan atas. Ada hembusan.'],
        'ق' => [['lisan'], 'PANGKAL lidah (paling dalam) menekan lelangit lembut — K yang TEBAL dan melantun.'],
        'ك' => [['lisan'], 'Pangkal lidah, sedikit ke depan dari Qaf, menekan lelangit — nipis, ada hembusan.'],
        'ل' => [['lisan'], 'Tepi hujung lidah menyentuh gusi gigi atas.'],
        'م' => [['syafatan'], 'Rapatkan dua bibir, bunyi dengung sedikit dari hidung.'],
        'ن' => [['lisan'], 'Hujung lidah menyentuh gusi gigi atas, bunyi dengung dari hidung.'],
        'و' => [['jauf', 'syafatan'], 'Muncungkan dua bibir tanpa merapatkannya.'],
        'ه' => [['halq'], 'Dari PANGKAL halkum (paling dalam) — nafas lembut "hhh", nipis.'],
        'ء' => [['halq'], 'Dari PANGKAL halkum — suara tersekat sekejap lalu dilepaskan, seperti "sa-at".'],
        'ي' => [['jauf', 'lisan'], 'Tengah lidah ke lelangit keras.'],
    ];

    /** Sifat berlawanan: [nama, definisi (akuislam.com), huruf] — setiap huruf hanya dalam satu daripada pasangan. */
    private const SIFAT_PAIRS = [
        [
            ['Jahr', 'Nafas tersekat ketika menyebutnya, dengan tekanan kuat pada makhraj.', 'ب ج د ذ ر ز ض ط ظ ع غ ل م ن و ي ق ء'],
            ['Hams', 'Huruf keluar dengan nafas terlepas, kerana tiada penekanan pada makhraj.', 'ت ث ح خ س ش ص ف ه ك'],
        ],
        [
            ['Syiddah', 'Huruf keluar dengan suara terhimpit (tersekat) ketika menyebutnya.', 'ء ب ت ج د ط ق ك'],
            ['Tawassuth', 'Pertengahan — suara tidak tersekat sepenuhnya dan tidak terlepas sepenuhnya.', 'ل ن ع م ر'],
            ['Rakhawah', 'Suara terlepas (mengalir) ketika menyebutnya.', 'ث ح خ ذ ز س ش ص ض ظ غ ف ه و ي'],
        ],
        [
            ['Isti\'la\'', 'Lidah terangkat ke lelangit hingga memenuhi mulut — bunyi TEBAL.', 'خ ص ض غ ط ق ظ'],
            ['Istifal', 'Lidah turun dari lelangit — bunyi NIPIS.', 'ء ب ت ث ج ح د ذ ر ز س ش ع ف ك ل م ن و ه ي'],
        ],
        [
            ['Ithbaq', 'Lidah melekap dan menutup ke lelangit.', 'ص ض ط ظ'],
            ['Infitah', 'Lidah renggang dari lelangit supaya nafas boleh keluar.', 'ء ب ت ث ج ح خ د ذ ر ز س ش ع غ ف ق ك ل م ن و ه ي'],
        ],
        [
            ['Izlaq', 'Mudah disebut kerana makhrajnya di hujung lidah dan bibir.', 'ف ر م ن ل ب'],
            ['Ismat', 'Lebih sukar diucapkan.', 'ء ت ث ج ح خ د ذ ز س ش ص ض ط ظ ع غ ق ك و ه ي'],
        ],
    ];

    /** Sifat tidak berlawanan (akuislam.com). */
    private const SIFAT_EXTRA = [
        ['Sofir', 'Disebut berserta desiran angin.', 'ز س ص'],
        ['Inhiraf', 'Disebut dengan melengkungkan lidah.', 'ل ر'],
        ['Takrir', 'Hujung lidah bergetar — tetapi jangan berulang-ulang.', 'ر'],
        ['Istitholah', 'Disebut dengan tepi lidah secara memanjang.', 'ض'],
        ['Tafasysyi', 'Angin tersembur di antara lidah dan lelangit.', 'ش'],
        ['Qolqolah', 'Bunyi melantun bila huruf berbaris mati (sukun) atau ketika berhenti (waqaf).', 'ق ط ب ج د'],
    ];

    public static function for(string $letter): ?array
    {
        if (!isset(self::MAKHRAJ[$letter])) {
            return null;
        }

        [$regions, $how] = self::MAKHRAJ[$letter];

        $sifat = [];
        foreach (self::SIFAT_PAIRS as $pair) {
            foreach ($pair as [$name, $desc, $letters]) {
                if (in_array($letter, explode(' ', $letters), true)) {
                    $sifat[] = ['name' => $name, 'desc' => $desc];
                }
            }
        }
        foreach (self::SIFAT_EXTRA as [$name, $desc, $letters]) {
            if (in_array($letter, explode(' ', $letters), true)) {
                $sifat[] = ['name' => $name, 'desc' => $desc, 'special' => true];
            }
        }

        return [
            'regions' => array_map(fn ($r) => self::REGIONS[$r], $regions),
            'how' => $how,
            // Ra tebal bergantung pada baris; yang lain ikut isti'la'.
            'weight' => $letter === 'ر' ? 'tebal/nipis' : (in_array($letter, explode(' ', self::SIFAT_PAIRS[2][0][2]), true) ? 'tebal' : 'nipis'),
            'sifat' => $sifat,
        ];
    }

    /** Untuk ujian: setiap pasangan mesti meliputi semua huruf (kecuali alif) tepat sekali. */
    public static function pairs(): array
    {
        return self::SIFAT_PAIRS;
    }
}
