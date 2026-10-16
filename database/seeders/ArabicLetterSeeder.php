<?php

namespace Database\Seeders;

use App\Models\ArabicLetter;
use Illuminate\Database\Seeder;

class ArabicLetterSeeder extends Seeder
{
    public function run(): void
    {
        ArabicLetter::truncate();

        // Susunan mengikut urutan huruf hijaiyah standard (1-28 + Hamzah bonus)
        $letters = [

            // ── 1. Alif ── straight_family ────────────────────────────────────
            // أرنب = arnab → "arnab" bermula dengan bunyi A seperti Alif ✓
            ['order_num'=>1,'arabic'=>'ا','name'=>'Alif','sound'=>'/aː/','transliteration'=>'alif',
             'group_name'=>'straight_family','difficulty'=>1,
             'mnemonic'=>'ALIF itu lurus seperti orang berdiri tegak — huruf pertama, pemimpin abjad Arab!',
             'visual_description'=>'Garisan lurus tegak. Alif sendiri tiada bunyi — ia memanjangkan bunyi "a". Bunyi "a" di awal perkataan (أ) sebenarnya hamzah yang duduk di atas alif.',
             'form_isolated'=>'ا','form_initial'=>'ا','form_medial'=>'ـا','form_final'=>'ـا',
             'example_word'=>'أرنب','example_translation'=>'arnab','audio_url'=>null],

            // ── 2. Ba ── dot_family ───────────────────────────────────────────
            // باص = bas → "bas" bermula dengan B seperti Ba ✓
            ['order_num'=>2,'arabic'=>'ب','name'=>'Ba','sound'=>'/b/','transliteration'=>'baa',
             'group_name'=>'dot_family','difficulty'=>1,
             'mnemonic'=>'Perahu BA-nyak dengan satu titik penambat di bawah — kalau titik di atas jadi TA-bung!',
             'visual_description'=>'Badan mendatar seperti perahu (mangkuk cetek) dengan satu titik di bawah.',
             'form_isolated'=>'ب','form_initial'=>'بـ','form_medial'=>'ـبـ','form_final'=>'ـب',
             'example_word'=>'باص','example_translation'=>'bas','audio_url'=>null],

            // ── 3. Ta ── dot_family ───────────────────────────────────────────
            // تراب = tanah → "tanah" bermula dengan T seperti Ta ✓
            ['order_num'=>3,'arabic'=>'ت','name'=>'Ta','sound'=>'/t/','transliteration'=>'taa',
             'group_name'=>'dot_family','difficulty'=>1,
             'mnemonic'=>'TA-bung air — sama bentuk Ba tapi ada DUA titik di atas, dua syiling dalam tabung.',
             'visual_description'=>'Garis melengkung seperti perahu dengan dua titik di atas.',
             'form_isolated'=>'ت','form_initial'=>'تـ','form_medial'=>'ـتـ','form_final'=>'ـت',
             'example_word'=>'تراب','example_translation'=>'tanah','audio_url'=>null],

            // ── 4. Tha ── dot_family ──────────────────────────────────────────
            // ثلاثة = tiga → "tiga" bermula dengan T (bunyi Tha hampir T) ✓
            ['order_num'=>4,'arabic'=>'ث','name'=>'Tsa','sound'=>'/θ/','transliteration'=>'tsa',
             'group_name'=>'dot_family','difficulty'=>1,
             'mnemonic'=>'TSA-lasah ertinya TIGA — ingat TIGA titik di atas perahu. Hujung lidah keluar sedikit di hujung gigi atas.',
             'visual_description'=>'Garis melengkung seperti perahu dengan tiga titik segitiga di atas.',
             'form_isolated'=>'ث','form_initial'=>'ثـ','form_medial'=>'ـثـ','form_final'=>'ـث',
             'example_word'=>'ثلاثة','example_translation'=>'tiga','audio_url'=>null],

            // ── 5. Jim ── round_family ────────────────────────────────────────
            // جار = jiran → "jiran" bermula dengan J seperti Jim ✓
            ['order_num'=>5,'arabic'=>'ج','name'=>'Jim','sound'=>'/dʒ/','transliteration'=>'jiim',
             'group_name'=>'round_family','difficulty'=>2,
             'mnemonic'=>'JIM seperti cangkuk J yang ada titik di dalam — JIM JIMAT simpan titik dalam poket.',
             'visual_description'=>'Kepala bulat dengan ekor melengkung ke bawah dan satu titik di tengah badan.',
             'form_isolated'=>'ج','form_initial'=>'جـ','form_medial'=>'ـجـ','form_final'=>'ـج',
             'example_word'=>'جار','example_translation'=>'jiran','audio_url'=>null],

            // ── 6. Haa (berat) ── round_family ───────────────────────────────
            // حرف = huruf → "huruf" bermula dengan H seperti Haa ✓
            ['order_num'=>6,'arabic'=>'ح','name'=>'Ha (berat)','sound'=>'/ħ/','transliteration'=>'ha',
             'group_name'=>'round_family','difficulty'=>2,
             'mnemonic'=>'HA berat tiada titik — seperti menghembus cermin supaya berkabus: bunyi dari TENGAH halkum, bersih tanpa garau.',
             'visual_description'=>'Kepala bulat dengan ekor melengkung ke bawah, tiada titik langsung.',
             'form_isolated'=>'ح','form_initial'=>'حـ','form_medial'=>'ـحـ','form_final'=>'ـح',
             'example_word'=>'حرف','example_translation'=>'huruf','audio_url'=>null],

            // ── 7. Kha ── round_family ────────────────────────────────────────
            // خيمة = khemah → "khemah" bermula dengan Kh seperti Kha ✓
            ['order_num'=>7,'arabic'=>'خ','name'=>'Kho','sound'=>'/x/','transliteration'=>'kho',
             'group_name'=>'round_family','difficulty'=>2,
             'mnemonic'=>'KHO macam Ha berat tapi ada titik di atas kepala — bunyi garau dari hujung halkum, seperti dengkur kecil.',
             'visual_description'=>'Sama seperti Ha (berat) tetapi dengan satu titik di atas kepala.',
             'form_isolated'=>'خ','form_initial'=>'خـ','form_medial'=>'ـخـ','form_final'=>'ـخ',
             'example_word'=>'خيمة','example_translation'=>'khemah','audio_url'=>null],

            // ── 8. Dal ── straight_family ─────────────────────────────────────
            // دفتر = daftar → "daftar" bermula dengan D seperti Dal ✓
            ['order_num'=>8,'arabic'=>'د','name'=>'Dal','sound'=>'/d/','transliteration'=>'daal',
             'group_name'=>'straight_family','difficulty'=>1,
             'mnemonic'=>'DAL macam kapak berdiri tegak — DALam hutan menebang pokok dengan kapak D.',
             'visual_description'=>'Kepala condong ke hadapan seperti sekop atau kapak kecil.',
             'form_isolated'=>'د','form_initial'=>'د','form_medial'=>'ـد','form_final'=>'ـد',
             'example_word'=>'دفتر','example_translation'=>'daftar','audio_url'=>null],

            // ── 9. Dzal ── straight_family ────────────────────────────────────
            // ذكر = zikir → "zikir" bermula dengan Z (bunyi Dzal hampir Z) ✓
            ['order_num'=>9,'arabic'=>'ذ','name'=>'Dzal','sound'=>'/ð/','transliteration'=>'dzaal',
             'group_name'=>'straight_family','difficulty'=>1,
             'mnemonic'=>'DZAL sama bentuk Dal tapi ada titik di atas — DZALim ada tanda hitam (titik) di kepala.',
             'visual_description'=>'Sama seperti Dal dengan satu titik di atas kepala.',
             'form_isolated'=>'ذ','form_initial'=>'ذ','form_medial'=>'ـذ','form_final'=>'ـذ',
             'example_word'=>'ذكر','example_translation'=>'zikir','audio_url'=>null],

            // ── 10. Ra ── straight_family ─────────────────────────────────────
            // رسالة = risalah → "risalah" bermula dengan R seperti Ra ✓
            ['order_num'=>10,'arabic'=>'ر','name'=>'Ro','sound'=>'/r/','transliteration'=>'ro',
             'group_name'=>'straight_family','difficulty'=>1,
             'mnemonic'=>'RO seperti kepala orang tunduk mengangguk — hujung lidah bergetar SEKALI sahaja, jangan berulang-ulang.',
             'visual_description'=>'Garisan dengan kepala condong lebih besar dan rendah dari Dal.',
             'form_isolated'=>'ر','form_initial'=>'ر','form_medial'=>'ـر','form_final'=>'ـر',
             'example_word'=>'رسالة','example_translation'=>'risalah','audio_url'=>null],

            // ── 11. Zay ── straight_family ────────────────────────────────────
            // زيتون = zaitun → "zaitun" bermula dengan Z seperti Zay ✓
            ['order_num'=>11,'arabic'=>'ز','name'=>'Zai','sound'=>'/z/','transliteration'=>'zai',
             'group_name'=>'straight_family','difficulty'=>1,
             'mnemonic'=>'ZAI macam Ro tapi ada titik di atas — ZAIton (zaitun) sebiji di atas kepala. Bunyi berdesir seperti lebah.',
             'visual_description'=>'Sama seperti Ro dengan satu titik di atas.',
             'form_isolated'=>'ز','form_initial'=>'ز','form_medial'=>'ـز','form_final'=>'ـز',
             'example_word'=>'زيتون','example_translation'=>'zaitun','audio_url'=>null],

            // ── 12. Sin ── teeth_family ───────────────────────────────────────
            // سلام = salam → "salam" bermula dengan S seperti Sin ✓
            ['order_num'=>12,'arabic'=>'س','name'=>'Sin','sound'=>'/s/','transliteration'=>'siin',
             'group_name'=>'teeth_family','difficulty'=>2,
             'mnemonic'=>'SIN senyum tunjukkan tiga gigi hadapan — SIN SENYUM tiga gerigi yang cantik!',
             'visual_description'=>'Tiga gundukan kecil seperti gigi berjajar, ekor panjang rata di bawah.',
             'form_isolated'=>'س','form_initial'=>'سـ','form_medial'=>'ـسـ','form_final'=>'ـس',
             'example_word'=>'سلام','example_translation'=>'salam','audio_url'=>null],

            // ── 13. Shin ── teeth_family ──────────────────────────────────────
            // شهادة = syahadah → "syahadah" bermula dengan Sy (= Sh) seperti Shin ✓
            ['order_num'=>13,'arabic'=>'ش','name'=>'Syin','sound'=>'/ʃ/','transliteration'=>'syin',
             'group_name'=>'teeth_family','difficulty'=>2,
             'mnemonic'=>'SYIN macam Sin tapi ada tiga titik di atas — bintang bertaburan di atas tiga gigi. Angin tersembur: "sy".',
             'visual_description'=>'Tiga gundukan seperti Sin dengan tiga titik segitiga di atas.',
             'form_isolated'=>'ش','form_initial'=>'شـ','form_medial'=>'ـشـ','form_final'=>'ـش',
             'example_word'=>'شهادة','example_translation'=>'syahadah','audio_url'=>null],

            // ── 14. Sad ── teeth_family ───────────────────────────────────────
            // صبر = sabar → "sabar" bermula dengan S (emphatik) seperti Sad ✓
            ['order_num'=>14,'arabic'=>'ص','name'=>'Sod','sound'=>'/sˤ/','transliteration'=>'sod',
             'group_name'=>'teeth_family','difficulty'=>2,
             'mnemonic'=>'SOD seperti beg besar yang penuh sesak — bunyi TEBAL berdesir: lidah naik ke lelangit, mulut terasa penuh.',
             'visual_description'=>'Gelung bujur seperti beg di kanan, satu gigi kecil, kemudian ekor mangkuk di kiri.',
             'form_isolated'=>'ص','form_initial'=>'صـ','form_medial'=>'ـصـ','form_final'=>'ـص',
             'example_word'=>'صبر','example_translation'=>'sabar','audio_url'=>null],

            // ── 15. Dad ── teeth_family ───────────────────────────────────────
            // ضعيف = daif → "daif" bermula dengan D seperti Dad ✓
            ['order_num'=>15,'arabic'=>'ض','name'=>'Dhod','sound'=>'/dˤ/','transliteration'=>'dhod',
             'group_name'=>'teeth_family','difficulty'=>2,
             'mnemonic'=>'DHOD macam Sod tapi ada titik di atas — tepi lidah menekan geraham atas, bunyi tebal dan memanjang.',
             'visual_description'=>'Sama seperti Sod dengan satu titik di atas badan huruf.',
             'form_isolated'=>'ض','form_initial'=>'ضـ','form_medial'=>'ـضـ','form_final'=>'ـض',
             'example_word'=>'ضعيف','example_translation'=>'daif (lemah)','audio_url'=>null],

            // ── 16. Tho ── misc ───────────────────────────────────────────────
            // طالب = talib → "talib" bermula dengan T seperti Tho ✓
            ['order_num'=>16,'arabic'=>'ط','name'=>'Tha','sound'=>'/tˤ/','transliteration'=>'tho',
             'group_name'=>'misc','difficulty'=>3,
             'mnemonic'=>'THA macam lelaki berdiri tegak di atas dulang — bunyi T yang TEBAL: hujung lidah ke pangkal gigi atas, lidah naik.',
             'visual_description'=>'Gelung bujur mendatar seperti dulang dengan satu batang lurus tegak di atasnya.',
             'form_isolated'=>'ط','form_initial'=>'طـ','form_medial'=>'ـطـ','form_final'=>'ـط',
             'example_word'=>'طالب','example_translation'=>'talib','audio_url'=>null],

            // ── 17. Zho ── misc ───────────────────────────────────────────────
            // ظهر = zuhur → "zuhur" bermula dengan Z seperti Zho ✓
            ['order_num'=>17,'arabic'=>'ظ','name'=>'Zho','sound'=>'/ðˤ/','transliteration'=>'Dhaa',
             'group_name'=>'misc','difficulty'=>3,
             'mnemonic'=>'ZHO macam Tha tapi ada titik — bunyi Z tebal: hujung lidah di hujung gigi atas, lidah naik ke lelangit.',
             'visual_description'=>'Sama seperti Tha dengan satu titik di atas gelung.',
             'form_isolated'=>'ظ','form_initial'=>'ظـ','form_medial'=>'ـظـ','form_final'=>'ـظ',
             'example_word'=>'ظهر','example_translation'=>'zuhur','audio_url'=>null],

            // ── 18. Ain ── round_family ───────────────────────────────────────
            // عالم = alam → "alam" bermula dengan A (bunyi Ain = vokal tekak) ✓
            ['order_num'=>18,'arabic'=>'ع','name'=>'Ain','sound'=>'/ʕ/','transliteration'=>'ain',
             'group_name'=>'round_family','difficulty'=>2,
             'mnemonic'=>'AIN seperti mata besar yang terbeliak terkejut — "AIN!" jerit orang tengok mata besar.',
             'visual_description'=>'Bentuk seperti mata terbuka lebar dengan ekor melengkung ke bawah.',
             'form_isolated'=>'ع','form_initial'=>'عـ','form_medial'=>'ـعـ','form_final'=>'ـع',
             'example_word'=>'عالم','example_translation'=>'alam','audio_url'=>null],

            // ── 19. Ghain ── round_family ─────────────────────────────────────
            // غراب = gagak → "gagak" bermula dengan G seperti Ghain ✓
            ['order_num'=>19,'arabic'=>'غ','name'=>'Ghain','sound'=>'/ɣ/','transliteration'=>'ghain',
             'group_name'=>'round_family','difficulty'=>2,
             'mnemonic'=>'GHAIN sama dengan Ain tapi ada titik di atas — GHAIB, titik rahsia menandakan sesuatu yang tersembunyi.',
             'visual_description'=>'Sama seperti Ain tetapi dengan satu titik di bahagian atas.',
             'form_isolated'=>'غ','form_initial'=>'غـ','form_medial'=>'ـغـ','form_final'=>'ـغ',
             'example_word'=>'غراب','example_translation'=>'gagak','audio_url'=>null],

            // ── 20. Fa ── tail_family ─────────────────────────────────────────
            // فطرة = fitrah → "fitrah" bermula dengan F seperti Fa ✓
            ['order_num'=>20,'arabic'=>'ف','name'=>'Fa','sound'=>'/f/','transliteration'=>'faa',
             'group_name'=>'tail_family','difficulty'=>2,
             'mnemonic'=>'FA macam muka bulat dengan satu mata titik di atas — FA-ces (muka) berkelip sebelah mata.',
             'visual_description'=>'Kepala bulat kecil di kanan, badan mendatar panjang ke kiri, satu titik di atas kepala.',
             'form_isolated'=>'ف','form_initial'=>'فـ','form_medial'=>'ـفـ','form_final'=>'ـف',
             'example_word'=>'فطرة','example_translation'=>'fitrah','audio_url'=>null],

            // ── 21. Qaf ── tail_family ────────────────────────────────────────
            // قرآن = Quran → "Quran" bermula dengan Q seperti Qaf ✓
            ['order_num'=>21,'arabic'=>'ق','name'=>'Qaf','sound'=>'/q/','transliteration'=>'qaaf',
             'group_name'=>'tail_family','difficulty'=>2,
             'mnemonic'=>'QAF macam mangkuk dalam dengan dua titik di atas — QAFtan (jubah) berhias dua butang.',
             'visual_description'=>'Kepala bulat dengan ekor mangkuk dalam yang turun di bawah garisan, dua titik di atas. Fa pula ekornya mendatar.',
             'form_isolated'=>'ق','form_initial'=>'قـ','form_medial'=>'ـقـ','form_final'=>'ـق',
             'example_word'=>'قرآن','example_translation'=>'Quran','audio_url'=>null],

            // ── 22. Kaf ── tail_family ────────────────────────────────────────
            // كرسي = kerusi → "kerusi" bermula dengan K seperti Kaf ✓
            ['order_num'=>22,'arabic'=>'ك','name'=>'Kaf','sound'=>'/k/','transliteration'=>'kaaf',
             'group_name'=>'tail_family','difficulty'=>2,
             'mnemonic'=>'KAF seperti telapak tangan yang terbuka pamer — "KAFi besar tangan saya!"',
             'visual_description'=>'Bentuk seperti tapak tangan terbuka menghadap ke bawah dengan garisan dalam.',
             'form_isolated'=>'ك','form_initial'=>'كـ','form_medial'=>'ـكـ','form_final'=>'ـك',
             'example_word'=>'كرسي','example_translation'=>'kerusi','audio_url'=>null],

            // ── 23. Lam ── tail_family ────────────────────────────────────────
            // ليمون = limau → "limau" bermula dengan L seperti Lam ✓
            ['order_num'=>23,'arabic'=>'ل','name'=>'Lam','sound'=>'/l/','transliteration'=>'laam',
             'group_name'=>'tail_family','difficulty'=>2,
             'mnemonic'=>'LAM seperti joran pancing yang melengkung menunggu ikan — LAM-bat, sabar tunggu.',
             'visual_description'=>'Garisan tegak yang melengkung ke kiri bawah seperti kail pancing.',
             'form_isolated'=>'ل','form_initial'=>'لـ','form_medial'=>'ـلـ','form_final'=>'ـل',
             'example_word'=>'ليمون','example_translation'=>'limau','audio_url'=>null],

            // ── 24. Mim ── tail_family ────────────────────────────────────────
            // مدرسة = madrasah → "madrasah" bermula dengan M seperti Mim ✓
            ['order_num'=>24,'arabic'=>'م','name'=>'Mim','sound'=>'/m/','transliteration'=>'miim',
             'group_name'=>'tail_family','difficulty'=>2,
             'mnemonic'=>'MIM macam kepala ular yang tergulung bulat dengan ekor kecil — MIM MENYUSUP perlahan-lahan.',
             'visual_description'=>'Bulatan kecil tertutup dengan ekor pendek menurun ke bawah.',
             'form_isolated'=>'م','form_initial'=>'مـ','form_medial'=>'ـمـ','form_final'=>'ـم',
             'example_word'=>'مدرسة','example_translation'=>'madrasah','audio_url'=>null],

            // ── 25. Nun ── dot_family ─────────────────────────────────────────
            // نبي = Nabi → "Nabi" bermula dengan N seperti Nun ✓
            ['order_num'=>25,'arabic'=>'ن','name'=>'Nun','sound'=>'/n/','transliteration'=>'nuun',
             'group_name'=>'dot_family','difficulty'=>1,
             'mnemonic'=>'NUN itu macam ikan dalam mangkuk penuh air — satu titik di atas ialah mata ikan NUN (ikan paus Nabi Yunus).',
             'visual_description'=>'Mangkuk bulat lebih dalam dari Ba/Ta dengan satu titik di atas.',
             'form_isolated'=>'ن','form_initial'=>'نـ','form_medial'=>'ـنـ','form_final'=>'ـن',
             'example_word'=>'نبي','example_translation'=>'Nabi','audio_url'=>null],

            // ── 26. Ha (ringan) ── misc ───────────────────────────────────────
            // هلال = hilal → "hilal" bermula dengan H seperti Ha ✓
            ['order_num'=>27,'arabic'=>'ه','name'=>'Ha (ringan)','sound'=>'/h/','transliteration'=>'ha',
             'group_name'=>'misc','difficulty'=>3,
             'mnemonic'=>'HA ringan seperti nafas lega "hhh" — bunyi dari PANGKAL halkum (paling dalam), nipis dan lembut.',
             'visual_description'=>'Bentuk bebas satu gelung bulat (ه). Bentuknya paling banyak berubah: awal هـ (dua gelung), tengah ـهـ, akhir ـه.',
             'form_isolated'=>'ه','form_initial'=>'هـ','form_medial'=>'ـهـ','form_final'=>'ـه',
             'example_word'=>'هلال','example_translation'=>'hilal','audio_url'=>null],

            // ── 27. Waw ── straight_family ────────────────────────────────────
            // وقت = waktu → "waktu" bermula dengan W seperti Waw ✓
            ['order_num'=>26,'arabic'=>'و','name'=>'Waw','sound'=>'/w/','transliteration'=>'waw',
             'group_name'=>'straight_family','difficulty'=>1,
             'mnemonic'=>'WAW macam orang terkejut angkat kepala — "WAW!" kepala bulat dengan ekor terkulai.',
             'visual_description'=>'Kepala bulat kecil dengan ekor panjang menurun ke bawah kiri.',
             'form_isolated'=>'و','form_initial'=>'و','form_medial'=>'ـو','form_final'=>'ـو',
             'example_word'=>'وقت','example_translation'=>'waktu','audio_url'=>null],

            // ── 28. Ya ── dot_family ──────────────────────────────────────────
            // يتيم = yatim → "yatim" bermula dengan Y seperti Ya ✓
            ['order_num'=>29,'arabic'=>'ي','name'=>'Ya','sound'=>'/j/','transliteration'=>'ya',
             'group_name'=>'dot_family','difficulty'=>1,
             'mnemonic'=>'YA seperti ular tidur sambil leperkan dua titik di bawah — "YA! Ada ular bawah sana!"',
             'visual_description'=>'Ekor panjang melengkung ke bawah dengan dua titik di bawah ekor.',
             'form_isolated'=>'ي','form_initial'=>'يـ','form_medial'=>'ـيـ','form_final'=>'ـي',
             'example_word'=>'يتيم','example_translation'=>'yatim','audio_url'=>null],

            // ── 29. Hamzah ── misc (bonus) ────────────────────────────────────
            // أمل = harapan → "harapan" bermula dengan H/A (vokal seperti Hamzah) ✓
            ['order_num'=>28,'arabic'=>'ء','name'=>'Hamzah','sound'=>'/ʔ/','transliteration'=>'hamza',
             'group_name'=>'misc','difficulty'=>3,
             'mnemonic'=>'HAMZAH macam kepala Ain yang kecil — HENTI sekejap di tekak, seperti hentian dalam sebutan "sa-at" atau "ra-ayat".',
             'visual_description'=>'Tanda kecil seperti kepala Ain. Hamzah tidak bersambung; ia bersendirian (ء) atau duduk di atas "kerusi": alif (أ / إ), wau (ؤ) atau ya tanpa titik (ئ).',
             'form_isolated'=>'ء','form_initial'=>'ء','form_medial'=>'ء','form_final'=>'ء',
             'example_word'=>'أمل','example_translation'=>'harapan','audio_url'=>null],
        ];

        foreach ($letters as $letter) {
            ArabicLetter::create($letter);
        }
    }
}
