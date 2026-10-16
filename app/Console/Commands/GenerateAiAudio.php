<?php

namespace App\Console\Commands;

use App\Models\ArabicLetter;
use App\Support\Harakat;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Jana sebutan huruf (nama huruf) dan suku kata baris dengan suara AI OpenAI.
 * Hasil: public/audio/ai/{huruf,baris}/*.mp3 + public/audio/ai/index.json.
 *
 * Enjin:
 *   gemini — Google Gemini TTS (ada free tier): gaya sebutan diarah dengan prompt.
 *            Setiap audio kemudian "didengar semula" oleh model Gemini lain sebagai soalan
 *            aneka pilihan (29 nama huruf / 30 suku kata) — dijana semula jika tersalah kenal.
 *   chat   — model audio GPT OpenAI (seperti suara ChatGPT): AI "diminta" menyebut, lalu
 *            transkrip jawapannya disemak supaya hanya teks Arab itu yang disebut.
 *   tts    — OpenAI /v1/audio/speech: baca teks terus.
 *
 * Fail sedia ada dilangkau (jimat kos) — guna --force atau --huruf=ب,خ untuk jana semula.
 */
class GenerateAiAudio extends Command
{
    protected $signature = 'audio:ai
        {--set=semua : huruf | baris | semua}
        {--huruf= : Hanya huruf tertentu, cth: ب,خ,ض (jana semula walaupun sudah ada)}
        {--force : Jana semula semua}
        {--engine= : gemini | chat (seperti ChatGPT) | tts — lalai dari AI_AUDIO_ENGINE}
        {--jeda= : Saat antara permintaan (lalai 6 untuk Gemini free tier)}';

    protected $description = 'Jana sebutan huruf & suku kata dengan suara AI (OpenAI TTS)';

    /** Nama huruf berbaris penuh — ini yang disebut bila huruf dimainkan. */
    private const LETTER_NAMES = [
        'ا' => 'أَلِفْ', 'ب' => 'بَاءْ', 'ت' => 'تَاءْ', 'ث' => 'ثَاءْ', 'ج' => 'جِيمْ', 'ح' => 'حَاءْ', 'خ' => 'خَاءْ',
        'د' => 'دَالْ', 'ذ' => 'ذَالْ', 'ر' => 'رَاءْ', 'ز' => 'زَايْ', 'س' => 'سِينْ', 'ش' => 'شِينْ', 'ص' => 'صَادْ',
        'ض' => 'ضَادْ', 'ط' => 'طَاءْ', 'ظ' => 'ظَاءْ', 'ع' => 'عَيْنْ', 'غ' => 'غَيْنْ', 'ف' => 'فَاءْ', 'ق' => 'قَافْ',
        'ك' => 'كَافْ', 'ل' => 'لَامْ', 'م' => 'مِيمْ', 'ن' => 'نُونْ', 'و' => 'وَاوْ', 'ه' => 'هَاءْ', 'ء' => 'هَمْزَةْ',
        'ي' => 'يَاءْ',
    ];

    private const INSTRUCTIONS = <<<'TXT'
        Voice: a calm, patient Quran teacher (ustaz) teaching an adult beginner.
        Language: Classical Arabic (fusha) exactly as in Quran recitation with tajwid — not a dialect.
        Pronounce ONLY the given Arabic text, exactly once, with no other words before or after.
        Articulation: use the precise makhraj of every letter. Heavy letters (خ ص ض غ ط ق ظ, and ر with fathah/dammah) with full tafkhim;
        all other letters light. Keep these pairs clearly distinct: ح vs ه, ع vs ء, س vs ص, ت vs ط, د vs ض, ذ vs ز vs ظ, ك vs ق, ث vs س.
        Pace: slow and clear, with a short natural pause at the end. Short vowels short; long vowels about two counts.
        TXT;

    public function handle(): int
    {
        $this->exhaustedModels = [];

        $engine = $this->option('engine')
            ?: config('hafalan.ai_audio_engine')
            ?: (filled(config('services.gemini.key')) ? 'gemini' : 'chat');
        if (!in_array($engine, ['gemini', 'chat', 'tts'], true)) {
            $this->error('--engine mesti gemini, chat atau tts');
            return self::FAILURE;
        }

        $provider = $engine === 'gemini' ? 'gemini' : 'openai';
        $key = config("services.{$provider}.key");
        if (blank($key)) {
            $this->error($engine === 'gemini'
                ? 'GEMINI_API_KEY belum ditetapkan dalam .env — dapatkan percuma di https://aistudio.google.com/apikey'
                : 'OPENAI_API_KEY belum ditetapkan dalam .env');
            return self::FAILURE;
        }

        $set = $this->option('set');
        if (!in_array($set, ['huruf', 'baris', 'semua'], true)) {
            $this->error('--set mesti huruf, baris atau semua');
            return self::FAILURE;
        }

        $only = collect(explode(',', (string) $this->option('huruf')))->map(fn ($c) => trim($c))->filter()->all();
        $letters = ArabicLetter::orderBy('order_num')->get();

        $indexPath = public_path('audio/ai/index.json');
        $index = is_file($indexPath) ? json_decode(file_get_contents($indexPath), true) : [];
        $index['items'] ??= [];

        $jobs = [];
        if ($set !== 'baris') {
            foreach ($letters as $l) {
                $jobs[] = ['key' => $l->arabic, 'text' => self::LETTER_NAMES[$l->arabic], 'dir' => 'huruf', 'name' => sprintf('%02d', $l->order_num), 'letter' => $l->arabic];
            }
        }
        if ($set !== 'huruf') {
            foreach (array_keys(Harakat::VOWELS) as $vowel) {
                foreach (Harakat::row($letters, $vowel) as $syl) {
                    $order = $letters->firstWhere('arabic', $syl['letter'])->order_num;
                    $jobs[] = ['key' => $syl['text'], 'text' => $syl['text'], 'dir' => 'baris', 'name' => sprintf('%02d-%s', $order, $vowel), 'letter' => $syl['letter']];
                }
            }
        }

        [$model, $voice] = match ($engine) {
            'gemini' => [config('services.gemini.tts_models'), config('services.gemini.tts_voice')],
            'chat' => [config('services.openai.audio_model'), config('services.openai.audio_voice')],
            'tts' => [config('services.openai.tts_model'), config('services.openai.tts_voice')],
        };
        $delay = (float) ($this->option('jeda') ?? config('hafalan.ai_audio_delay') ?? ($engine === 'gemini' ? 6 : 0));
        $this->line("Enjin: {$engine} · model: ".implode(', ', (array) $model)." · suara: {$voice}".($delay ? " · jeda {$delay}s" : ''));

        $index['generator'] = ['provider' => $engine === 'gemini' ? 'Google Gemini' : 'OpenAI', 'engine' => $engine, 'model' => $model, 'voice' => $voice];
        $requests = 0;

        $made = 0;
        $skipped = 0;
        $problems = [];

        // Pilihan jawapan untuk semakan Gemini: semua nama huruf; atau suku kata berbaris sama + baris lain huruf sama.
        $syllables = collect($jobs)->where('dir', 'baris');
        foreach ($jobs as &$job) {
            $job['choices'] = $job['dir'] === 'huruf'
                ? array_values(self::LETTER_NAMES)
                : $syllables->filter(fn ($s) => str_ends_with($s['name'], substr($job['name'], -2)) || $s['letter'] === $job['letter'])
                    ->pluck('text')->unique()->values()->all();
        }
        unset($job);

        $bar = $this->output->createProgressBar(count($jobs));
        foreach ($jobs as $job) {
            $bar->advance();
            $existing = $index['items'][$job['key']] ?? null;
            $wanted = $only ? in_array($job['letter'], $only, true) : true;
            $fresh = $existing && is_file(public_path(ltrim($existing['file'], '/')));

            if (!$wanted || ($fresh && !$this->option('force') && !$only)) {
                $skipped++;
                continue;
            }

            if ($requests++ && $delay) {
                usleep((int) ($delay * 1_000_000));
            }

            $result = match ($engine) {
                'gemini' => $this->viaGemini($key, $model, $voice, $job),
                'chat' => $this->viaChat($key, $model, $voice, $job),
                'tts' => $this->viaTts($key, $model, $voice, $job),
            };

            if (isset($result['fatal'])) {
                $bar->clear();
                $this->error("Gagal untuk {$job['text']}: {$result['fatal']}");
                if ($made) {
                    $this->warn("{$made} audio yang sudah siap telah disimpan. Jalankan semula arahan yang sama untuk sambung.");
                    $this->call('audio:optimise');
                }
                $this->reportProblems($problems);
                return self::FAILURE;
            }
            if (isset($result['problem'])) {
                $problems[] = "{$job['text']} — {$result['problem']}";
                continue;
            }

            $audio = $result['audio'];
            // Nama fail ikut kandungan supaya cache pelayar/service worker tidak memainkan versi lama.
            $file = sprintf('/audio/ai/%s/%s-%s.%s', $job['dir'], $job['name'], substr(md5($audio), 0, 8), $result['ext'] ?? 'mp3');
            File::ensureDirectoryExists(public_path('audio/ai/'.$job['dir']));
            file_put_contents(public_path(ltrim($file, '/')), $audio);

            if ($existing && $existing['file'] !== $file) {
                File::delete(public_path(ltrim($existing['file'], '/')));
            }

            $index['items'][$job['key']] = array_filter([
                'file' => $file, 'text' => $job['text'], 'letter' => $job['letter'], 'set' => $job['dir'],
                'transcript' => $result['transcript'] ?? null,
                'verified' => $result['verified'] ?? null,
                'model' => $result['model'] ?? (is_string($model) ? $model : null),
            ], fn ($v) => $v !== null);
            $made++;
            // Simpan selepas setiap fail — jika terhenti (cth. had free tier), kerja tidak hilang.
            $this->writeIndex($indexPath, $index);
        }
        $bar->finish();
        $this->newLine(2);

        if ($made) {
            $this->writeIndex($indexPath, $index);
            $this->call('audio:optimise');
        }

        $this->info("{$made} audio dijana, {$skipped} dilangkau. Dengar & semak di /kredit.");

        $this->reportProblems($problems);

        return self::SUCCESS;
    }

    private function reportProblems(array $problems): void
    {
        if (!$problems) {
            return;
        }
        $this->warn(count($problems).' tidak dapat dijana dengan bersih (kekal audio lama / Lingua Libre):');
        foreach ($problems as $p) {
            $this->line('  • '.$p);
        }
        $this->line('Cuba lagi: php artisan audio:ai --huruf=<huruf>');
    }

    private function writeIndex(string $path, array $index): void
    {
        $index['generator']['updated_at'] = now()->toIso8601String();
        ksort($index['items']);
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Google Gemini TTS — PCM 24 kHz 16-bit mono, dibungkus sebagai WAV. */
    /** Model TTS Gemini yang kuota hariannya sudah habis dalam larian ini. */
    private array $exhaustedModels = [];

    /**
     * Gemini TTS — PCM 24 kHz 16-bit mono, dibungkus sebagai WAV.
     * Setiap model ada kuota harian sendiri (free tier ~10/hari); bila habis, tukar ke model seterusnya.
     */
    private function viaGemini(string $key, array $models, string $voice, array $job): array
    {
        $last = null;
        foreach ($models as $model) {
            if (in_array($model, $this->exhaustedModels, true)) {
                continue;
            }

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $response = $this->send(fn () => $this->http(null)->withHeaders(['x-goog-api-key' => $key])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'contents' => [['parts' => [['text' => self::geminiPrompt($model, $job)]]]],
                        'generationConfig' => [
                            'responseModalities' => ['AUDIO'],
                            'speechConfig' => ['voiceConfig' => ['prebuiltVoiceConfig' => ['voiceName' => $voice]]],
                        ],
                    ]));

                // Kuota harian model ini habis (tiada masa tunggu pendek) — cuba model lain.
                if ($response->status() === 429 || $response->status() === 404) {
                    $this->exhaustedModels[] = $model;
                    $this->output->write(" ↪ {$model} ".($response->status() === 404 ? 'tiada' : 'kuota habis').' — tukar model');
                    continue 2;
                }
                if (!$response->successful()) {
                    return $this->httpFailure($response);
                }

                $pcm = base64_decode((string) $response->json('candidates.0.content.parts.0.inlineData.data'), true);
                $mime = (string) $response->json('candidates.0.content.parts.0.inlineData.mimeType');
                $rate = preg_match('/rate=(\d+)/', $mime, $m) ? (int) $m[1] : 24000;
                $seconds = $pcm ? strlen($pcm) / ($rate * 2) : 0;

                // Nama huruf/suku kata ~0.4–3 saat; lebih panjang = AI tersebut arahan juga.
                if (!$pcm || $seconds < 0.25 || $seconds > 3.5) {
                    $last = $pcm ? sprintf('tempoh %.1f saat', $seconds) : 'tiada audio';
                    continue;
                }

                $wav = self::wav($pcm, $rate);
                $heard = $this->geminiHears($key, $wav, $job);
                if ($heard === null || $heard === $job['text']) {
                    // null = model semakan tidak tersedia buat masa ini; simpan, tandakan belum disahkan.
                    return ['audio' => $wav, 'ext' => 'wav', 'verified' => $heard !== null, 'model' => $model];
                }
                $last = "didengar sebagai {$heard}";
            }

            // 3 cubaan pada model ini gagal semakan — jangan tukar model, laporkan sahaja.
            return ['problem' => $last];
        }

        return ['fatal' => 'Kuota harian semua model TTS Gemini sudah habis ('.implode(', ', $models).'). '
            .'Kuota pulih esok — jalankan semula arahan yang sama untuk sambung.'];
    }

    /**
     * Model 2.5 patuh arahan ayat penuh; model 3.x pula membaca ayat itu kuat-kuat,
     * jadi gaya diberi sebagai tag dalam kurungan siku.
     */
    public static function geminiPrompt(string $model, array $job): string
    {
        if (str_starts_with($model, 'gemini-2.5')) {
            $kind = $job['dir'] === 'huruf' ? 'the name of this Arabic letter' : 'this Arabic syllable';

            return "Say {$kind} slowly and clearly, exactly once, like a Quran teacher (ustaz) teaching a beginner, "
                .'in Classical Quranic Arabic with precise tajwid makhraj (tafkhim for heavy letters): '.$job['text'];
        }

        return '[slowly, clearly, like a Quran teacher, Classical Arabic with tajwid] '.$job['text'];
    }

    /**
     * Gemini "mendengar" audio dan memilih teks yang disebut daripada senarai pilihan.
     * Pulangkan pilihan itu, atau null jika semua model semakan tidak tersedia.
     */
    private function geminiHears(string $key, string $wav, array $job): ?string
    {
        $what = $job['dir'] === 'huruf' ? 'the NAME of one Arabic (hijaiyah) letter' : 'one Arabic syllable (a letter with a short vowel)';
        $body = [
            'contents' => [['parts' => [
                ['inline_data' => ['mime_type' => 'audio/wav', 'data' => base64_encode($wav)]],
                ['text' => "Listen carefully. A speaker says {$what}. Which option is spoken? Pay close attention to makhraj: "
                    .'ح vs ه, ع vs ء, س vs ص, ت vs ط, د vs ض, ذ vs ز vs ظ, ك vs ق, and to the short vowel (a / i / u). Answer with the exact option.'],
            ]]],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => ['type' => 'OBJECT', 'properties' => ['choice' => ['type' => 'STRING', 'enum' => $job['choices']]], 'required' => ['choice']],
            ],
        ];

        foreach (config('services.gemini.check_models') as $model) {
            $response = $this->send(fn () => $this->http(null)->withHeaders(['x-goog-api-key' => $key])
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.trim($model).':generateContent', $body));
            $choice = json_decode((string) $response->json('candidates.0.content.parts.0.text'), true)['choice'] ?? null;
            if ($response->successful() && $choice) {
                return $choice;
            }
        }

        return null;
    }

    /** Masa tunggu dari ralat 429: header Retry-After, RetryInfo.retryDelay "13s", atau "retry in 13.04s". */
    public static function retryAfterSeconds($response): ?float
    {
        if (is_numeric($header = $response->header('Retry-After'))) {
            return (float) $header;
        }
        foreach ((array) $response->json('error.details') as $detail) {
            if (isset($detail['retryDelay']) && preg_match('/([\d.]+)s/', $detail['retryDelay'], $m)) {
                return (float) $m[1];
            }
        }
        if (preg_match('/retry in ([\d.]+)s/i', (string) $response->json('error.message'), $m)) {
            return (float) $m[1];
        }

        return null;
    }

    /** Bungkus PCM 16-bit mono dengan pengepala WAV supaya boleh dimainkan di semua pelayar. */
    public static function wav(string $pcm, int $rate): string
    {
        return 'RIFF'.pack('V', 36 + strlen($pcm)).'WAVE'
            .'fmt '.pack('VvvVVvv', 16, 1, 1, $rate, $rate * 2, 2, 16)
            .'data'.pack('V', strlen($pcm)).$pcm;
    }

    /** Model audio GPT melalui Chat Completions — sama seperti bertanya ChatGPT dengan suara. */
    private function viaChat(string $key, string $model, string $voice, array $job): array
    {
        $ask = $job['dir'] === 'huruf'
            ? "Sebut nama huruf hijaiyah {$job['letter']} seperti guru al-Quran mengajar murid: «{$job['text']}»"
            : "Sebut suku kata ini seperti guru al-Quran mengajar murid: «{$job['text']}»";

        $last = null;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $response = $this->send(fn () => $this->http($key)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'modalities' => ['text', 'audio'],
                'audio' => ['voice' => $voice, 'format' => 'mp3'],
                'messages' => [
                    ['role' => 'system', 'content' => self::INSTRUCTIONS."\nReply with audio of ONLY the Arabic text inside « ». No greeting, no explanation, no Malay or English words."],
                    ['role' => 'user', 'content' => $ask],
                ],
            ]));

            if (!$response->successful()) {
                return $this->httpFailure($response);
            }

            $audio = base64_decode((string) $response->json('choices.0.message.audio.data'), true);
            $transcript = trim((string) $response->json('choices.0.message.audio.transcript'));

            if ($audio && self::transcriptMatches($transcript, $job['text'])) {
                return ['audio' => $audio, 'transcript' => $transcript];
            }
            $last = $transcript ?: '(tiada audio)';
        }

        return ['problem' => "AI menyebut lain: \"{$last}\""];
    }

    private function viaTts(string $key, string $model, string $voice, array $job): array
    {
        $response = $this->send(fn () => $this->http($key)->post('https://api.openai.com/v1/audio/speech', [
            'model' => $model,
            'voice' => $voice,
            'input' => $job['text'],
            'instructions' => self::INSTRUCTIONS,
            'response_format' => 'mp3',
            'speed' => 0.9,
        ]));

        return $response->successful() ? ['audio' => $response->body()] : $this->httpFailure($response);
    }

    // Cuba semula ralat sambungan / pelayan sibuk; had kadar (429) diurus oleh send(); ralat kunci (401) terus dilaporkan.
    private function http(?string $bearer)
    {
        return Http::when($bearer, fn ($h) => $h->withToken($bearer))->timeout(90)
            ->retry(3, config('hafalan.ai_retry_ms', 2000), fn ($e) => $e instanceof ConnectionException
                || in_array($e->response?->status(), [500, 502, 503], true), throw: false);
    }

    /**
     * Hantar permintaan; jika kena had kadar (429) dengan masa tunggu yang munasabah
     * (cth. free tier Gemini 3/minit), tunggu seperti diminta pelayan dan cuba lagi.
     */
    private function send(callable $request)
    {
        for ($waits = 0; ; $waits++) {
            $response = $request();
            $wait = $response->status() === 429 ? self::retryAfterSeconds($response) : null;
            if ($wait === null || $wait > 120 || $waits >= 20) {
                return $response;
            }
            $this->output->write(sprintf(' ⏳ had seminit — tunggu %ds…', (int) ceil($wait)));
            usleep((int) ((ceil($wait) + 1) * 1_000_000 * (config('hafalan.ai_retry_ms', 2000) > 0 ? 1 : 0)));
        }
    }

    private function httpFailure($response): array
    {
        return ['fatal' => 'HTTP '.$response->status().' '.$response->json('error.message')];
    }

    /**
     * Transkrip mesti hanya teks Arab yang diminta: tiada huruf Latin, dan huruf asasnya
     * (tanpa baris) sama dengan teks asal. Contoh gagal: "Baik, ini dia: بَاءْ".
     */
    public static function transcriptMatches(string $transcript, string $expected): bool
    {
        if ($transcript === '' || preg_match('/\p{Latin}/u', $transcript)) {
            return false;
        }
        $base = fn ($t) => preg_replace('/[^\x{0621}-\x{064A}]/u', '', $t);
        $normalise = fn ($t) => strtr($base($t), ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ة' => 'ه', 'ى' => 'ي']);

        return $normalise($transcript) === $normalise($expected);
    }
}
