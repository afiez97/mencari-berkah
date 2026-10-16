<?php

namespace Tests\Feature;

use App\Console\Commands\GenerateAiAudio;
use App\Support\OpenAudio;
use Database\Seeders\ArabicLetterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAudioTest extends TestCase
{
    use RefreshDatabase;

    private string $public;

    /** Bila ditetapkan, API palsu pulangkan ralat ini: [status, mesej]. */
    private ?array $apiError = null;

    /** Bilangan respons 429 "had seminit" sebelum API palsu berjaya. */
    private int $rateLimited = 0;

    /** Model TTS yang kuota hariannya "habis" dalam API palsu. */
    private array $dailyExhausted = [];

    /** Apa yang "disebut" oleh model chat palsu: fn(string $diminta, int $n): string */
    private $chatSays;

    /** Tempoh (saat) audio Gemini palsu: fn(int $n): float */
    private $geminiSeconds;

    /** Apa yang "didengar" oleh model semakan Gemini palsu: fn(string $disebut, array $pilihan, int $n): string */
    private $geminiHears;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ArabicLetterSeeder::class);

        // Folder public sementara — ujian tidak menyentuh public/audio sebenar.
        $this->public = sys_get_temp_dir().'/hafalan-public-'.uniqid();
        File::ensureDirectoryExists($this->public);
        $this->app->usePublicPath($this->public);
        OpenAudio::flush();

        $this->chatSays = fn ($asked) => $asked;
        $this->geminiSeconds = fn () => 0.8;
        $this->geminiHears = fn ($spoken) => $spoken;

        config([
            'hafalan.ai_audio_engine' => 'tts',
            'hafalan.ai_audio_delay' => 0,
            'hafalan.ai_retry_ms' => 0,
            'services.gemini.check_models' => ['gemini-3.5-flash'],
            'services.openai.key' => 'sk-test',
            'services.openai.tts_model' => 'gpt-4o-mini-tts', 'services.openai.tts_voice' => 'onyx',
            'services.openai.audio_model' => 'gpt-audio', 'services.openai.audio_voice' => 'cedar',
            'services.gemini.key' => 'gm-test',
            'services.gemini.tts_models' => ['gemini-2.5-flash-preview-tts'], 'services.gemini.tts_voice' => 'Charon',
        ]);

        // Setiap panggilan pulangkan bunyi berbeza — seperti API sebenar.
        $n = 0;
        $spoken = null;
        $checks = 0;
        Http::fake(function ($request) use (&$n, &$spoken, &$checks) {
            if ($this->apiError) {
                return Http::response(['error' => ['message' => $this->apiError[1]]], $this->apiError[0]);
            }
            if ($this->rateLimited > 0) {
                $this->rateLimited--;
                return Http::response(['error' => ['code' => 429, 'message' => 'Quota exceeded. Please retry in 0.01s.',
                    'details' => [['@type' => 'type.googleapis.com/google.rpc.RetryInfo', 'retryDelay' => '0s']]]], 429);
            }
            $url = $request->url();
            if (str_contains($url, 'generativelanguage.googleapis.com') && isset($request['generationConfig']['responseSchema'])) {
                $choices = $request['generationConfig']['responseSchema']['properties']['choice']['enum'];
                $heard = ($this->geminiHears)($spoken, $choices, $checks++);
                return Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode(['choice' => $heard])]]]]]]);
            }
            foreach ($this->dailyExhausted as $m) {
                if (str_contains($url, "models/{$m}:")) {
                    return Http::response(['error' => ['code' => 429, 'message' => 'Quota exceeded for metric: free_tier_requests, limit: 10. Please retry in 17h27m25s.']], 429);
                }
            }
            if (str_contains($url, 'generativelanguage.googleapis.com')) {
                $prompt = $request['contents'][0]['parts'][0]['text'];
                $spoken = mb_substr($prompt, mb_strrpos($prompt, ' ') + 1); // teks Arab sentiasa perkataan terakhir
                $pcm = str_repeat("\x01\x00", (int) (24000 * ($this->geminiSeconds)($n++)));
                return Http::response(['candidates' => [['content' => ['parts' => [['inlineData' => [
                    'mimeType' => 'audio/L16;codec=pcm;rate=24000', 'data' => base64_encode($pcm.$n),
                ]]]]]]]);
            }
            if (str_contains($url, '/chat/completions')) {
                preg_match('/«(.+)»/u', $request['messages'][1]['content'], $m);
                $said = ($this->chatSays)($m[1], $n);
                return Http::response(['choices' => [['message' => ['audio' => [
                    'data' => base64_encode('MP3:'.$said.':'.$n++), 'transcript' => $said,
                ]]]]]);
            }
            return Http::response('MP3:'.$request['input'].':'.$n++, 200, ['Content-Type' => 'audio/mpeg']);
        });
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->public);
        OpenAudio::flush();
        parent::tearDown();
    }

    private function index(): array
    {
        return json_decode(file_get_contents($this->public.'/audio/ai/index.json'), true);
    }

    // ── Umum (enjin tts) ────────────────────────────────────────────

    public function test_requires_api_key(): void
    {
        config(['services.openai.key' => null]);

        $this->artisan('audio:ai')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_generates_letters_and_syllables_and_serves_them_first(): void
    {
        $this->artisan('audio:ai')->assertSuccessful();

        // 29 nama huruf + 28 huruf × 3 baris.
        Http::assertSentCount(29 + 84);
        Http::assertSent(fn ($r) => $r['input'] === 'خَاءْ' && $r['model'] === 'gpt-4o-mini-tts'
            && $r['voice'] === 'onyx' && str_contains($r['instructions'], 'tafkhim'));

        $index = $this->index();
        $this->assertCount(113, $index['items']);
        $this->assertFileExists($this->public.$index['items']['ب']['file']);

        OpenAudio::flush();
        $map = OpenAudio::clientMap(\App\Models\ArabicLetter::all());
        $this->assertSame('ai', $map['ب']['source']);
        $this->assertSame('ai', $map['بَ']['source']);
        $this->assertStringStartsWith('/audio/ai/huruf/02-', $map['ب']['url']);
    }

    public function test_rerun_skips_existing_and_huruf_option_regenerates_only_that_letter(): void
    {
        $this->artisan('audio:ai')->assertSuccessful();
        $before = $this->index()['items'];

        $this->artisan('audio:ai')->assertSuccessful();
        Http::assertSentCount(113); // tiada panggilan baharu

        $this->artisan('audio:ai', ['--huruf' => 'ض'])->assertSuccessful();
        Http::assertSentCount(113 + 4); // nama huruf + 3 suku kata

        $after = $this->index()['items'];
        $this->assertNotSame($before['ض']['file'], $after['ض']['file']);
        $this->assertFileDoesNotExist($this->public.$before['ض']['file']);
        $this->assertSame($before['ب']['file'], $after['ب']['file']);
    }

    public function test_api_error_is_reported(): void
    {
        $this->apiError = [401, 'Incorrect API key'];

        $this->artisan('audio:ai', ['--set' => 'huruf'])->expectsOutputToContain('Incorrect API key')->assertFailed();
    }

    public function test_progress_is_kept_when_quota_runs_out_midway(): void
    {
        // Had free tier habis selepas 5 huruf.
        $calls = 0;
        $this->geminiSeconds = function () use (&$calls) {
            if (++$calls >= 5) { // respons ke-5 masih berjaya; ke-6 kena had
                $this->apiError = [429, 'Resource has been exhausted (quota)'];
            }
            return 0.8;
        };

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf'])
            ->expectsOutputToContain('Jalankan semula arahan yang sama untuk sambung')
            ->assertFailed();
        $this->assertCount(5, $this->index()['items']);

        // Esok: kuota pulih, sambung — hanya 24 yang tinggal dijana.
        $this->apiError = null;
        $this->geminiSeconds = fn () => 0.8;
        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf'])->assertSuccessful();
        $this->assertCount(29, $this->index()['items']);
    }

    // ── Enjin Gemini ────────────────────────────────────────────────

    public function test_gemini_is_default_when_its_key_exists(): void
    {
        config(['hafalan.ai_audio_engine' => null]);

        $this->artisan('audio:ai', ['--set' => 'huruf'])->assertSuccessful();

        Http::assertSentCount(29 * 2); // jana + dengar semula
        Http::assertSent(fn ($r) => str_contains($r->url(), 'models/gemini-3.5-flash:generateContent')
            && count($r['generationConfig']['responseSchema']['properties']['choice']['enum']) === 29);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'models/gemini-2.5-flash-preview-tts:generateContent')
            && $r->header('x-goog-api-key') === ['gm-test']
            && $r['generationConfig']['speechConfig']['voiceConfig']['prebuiltVoiceConfig']['voiceName'] === 'Charon'
            && str_ends_with($r['contents'][0]['parts'][0]['text'], ': ضَادْ'));

        $index = $this->index();
        $this->assertSame('Google Gemini', $index['generator']['provider']);
        $this->assertTrue($index['items']['ض']['verified']);
        // Gemini pulangkan WAV; audio:optimise menukarnya ke MP3 jika ffmpeg ada.
        $file = $index['items']['ض']['file'];
        $this->assertMatchesRegularExpression('#\.(wav|mp3)$#', $file);
        if (str_ends_with($file, '.wav')) {
            $this->assertSame('RIFF', substr(file_get_contents($this->public.$file), 0, 4));
        }
    }

    public function test_gemini_missing_key_points_to_free_ai_studio(): void
    {
        config(['services.gemini.key' => null]);

        $this->artisan('audio:ai', ['--engine' => 'gemini'])->expectsOutputToContain('aistudio.google.com')->assertFailed();
    }

    public function test_gemini_retries_when_audio_is_too_long(): void
    {
        // Cubaan pertama 9 saat (AI tersebut arahan) — mesti dicuba semula.
        $this->geminiSeconds = fn ($n) => $n === 0 ? 9.0 : 0.9;

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf', '--huruf' => 'ب'])->assertSuccessful();

        Http::assertSentCount(3); // terlalu panjang (tiada semakan), kemudian jana + semak
        $this->assertArrayHasKey('ب', $this->index()['items']);
    }

    public function test_gemini_regenerates_when_listen_back_hears_another_letter(): void
    {
        // Semakan pertama dengar هَاءْ walaupun diminta حَاءْ — mesti dijana semula.
        $this->geminiHears = fn ($spoken, $choices, $n) => $n === 0 ? 'هَاءْ' : $spoken;

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf', '--huruf' => 'ح'])->assertSuccessful();

        Http::assertSentCount(4);
        $this->assertTrue($this->index()['items']['ح']['verified']);
    }

    public function test_gemini_reports_letter_that_keeps_being_misheard(): void
    {
        $this->geminiHears = fn () => 'هَاءْ';

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf', '--huruf' => 'ح'])
            ->expectsOutputToContain('didengar sebagai هَاءْ')
            ->assertSuccessful();
        $this->assertFileDoesNotExist($this->public.'/audio/ai/huruf');
    }

    public function test_syllable_check_offers_same_vowel_row_and_other_vowels_of_same_letter(): void
    {
        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'baris', '--huruf' => 'خ'])->assertSuccessful();

        Http::assertSent(function ($r) {
            $enum = $r['generationConfig']['responseSchema']['properties']['choice']['enum'] ?? null;
            return $enum && in_array('خَ', $enum) && in_array('خِ', $enum) && in_array('خُ', $enum)
                && in_array('حَ', $enum) && count($enum) === 30;
        });
    }

    public function test_wav_header_is_valid(): void
    {
        $wav = GenerateAiAudio::wav(str_repeat("\x00\x00", 24000), 24000);

        $this->assertSame(44 + 48000, strlen($wav));
        $this->assertSame(24000, unpack('V', substr($wav, 24, 4))[1]); // kadar sampel
        $this->assertSame(48000, unpack('V', substr($wav, 40, 4))[1]); // saiz data
    }

    // ── Enjin chat (seperti suara ChatGPT) ──────────────────────────

    public function test_chat_engine_asks_like_chatgpt_and_saves_transcript(): void
    {
        $this->artisan('audio:ai', ['--engine' => 'chat', '--set' => 'huruf'])->assertSuccessful();

        Http::assertSentCount(29);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/chat/completions')
            && $r['model'] === 'gpt-audio' && $r['audio']['voice'] === 'cedar'
            && $r['modalities'] === ['text', 'audio']
            && str_contains($r['messages'][1]['content'], 'Sebut nama huruf hijaiyah ض')
            && str_contains($r['messages'][1]['content'], '«ضَادْ»'));

        $index = $this->index();
        $this->assertSame('chat', $index['generator']['engine']);
        $this->assertSame('ضَادْ', $index['items']['ض']['transcript']);
    }

    public function test_chat_engine_retries_when_ai_adds_extra_words(): void
    {
        $this->chatSays = fn ($asked, $n) => $n === 0 ? 'Baik, ini sebutannya: '.$asked : $asked;

        $this->artisan('audio:ai', ['--engine' => 'chat', '--set' => 'huruf', '--huruf' => 'ا'])->assertSuccessful();

        Http::assertSentCount(2);
        $this->assertSame('أَلِفْ', $this->index()['items']['ا']['transcript']);
    }

    public function test_chat_engine_reports_letters_it_cannot_say_cleanly(): void
    {
        $this->chatSays = fn ($asked) => 'Sure! '.$asked;

        $this->artisan('audio:ai', ['--engine' => 'chat', '--set' => 'huruf', '--huruf' => 'ظ'])
            ->expectsOutputToContain('tidak dapat dijana dengan bersih')
            ->assertSuccessful();

        Http::assertSentCount(3);
        $this->assertFileDoesNotExist($this->public.'/audio/ai/huruf');
    }

    public function test_transcript_matching(): void
    {
        $m = fn ($t, $e) => GenerateAiAudio::transcriptMatches($t, $e);

        $this->assertTrue($m('بَاءْ.', 'بَاءْ'));
        $this->assertTrue($m('باء', 'بَاءْ'));
        $this->assertTrue($m('همزة', 'هَمْزَةْ'));
        $this->assertTrue($m('ألف', 'أَلِفْ'));
        $this->assertFalse($m('حاء', 'هَاءْ'));
        $this->assertFalse($m('ok بَاءْ', 'بَاءْ'));
        $this->assertFalse($m('', 'بَاءْ'));
    }

    // ── Pengoptimuman ───────────────────────────────────────────────

    public function test_optimise_converts_wav_to_smaller_mp3_and_updates_index(): void
    {
        if (!(new \Symfony\Component\Process\ExecutableFinder)->find('ffmpeg')) {
            $this->markTestSkipped('ffmpeg tiada');
        }

        // 0.5 s senyap + 0.6 s nada 440 Hz + 0.5 s senyap.
        $pcm = str_repeat("\x00\x00", 12000);
        for ($i = 0; $i < 14400; $i++) {
            $pcm .= pack('v', (int) (8000 * sin(2 * M_PI * 440 * $i / 24000)) & 0xFFFF);
        }
        $pcm .= str_repeat("\x00\x00", 12000);
        File::ensureDirectoryExists($this->public.'/audio/ai/huruf');
        file_put_contents($this->public.'/audio/ai/huruf/02-aaaaaaaa.wav', GenerateAiAudio::wav($pcm, 24000));
        file_put_contents($this->public.'/audio/ai/index.json', json_encode(['items' => [
            'ب' => ['file' => '/audio/ai/huruf/02-aaaaaaaa.wav', 'text' => 'بَاءْ', 'letter' => 'ب', 'set' => 'huruf'],
        ]]));

        $this->artisan('audio:optimise')->assertSuccessful();

        $item = $this->index()['items']['ب'];
        $this->assertMatchesRegularExpression('#^/audio/ai/huruf/02-[0-9a-f]{8}\.mp3$#', $item['file']);
        $this->assertFileDoesNotExist($this->public.'/audio/ai/huruf/02-aaaaaaaa.wav');
        $mp3 = $this->public.$item['file'];
        $this->assertLessThan(strlen($pcm) / 4, filesize($mp3));

        // Senyap dibuang: tempoh hampir sama dengan nada sahaja.
        $duration = (float) trim((new \Symfony\Component\Process\Process(['ffprobe', '-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', $mp3]))->mustRun()->getOutput());
        $this->assertLessThan(1.1, $duration);
    }

    public function test_gemini_waits_out_per_minute_limit_instead_of_stopping(): void
    {
        $this->rateLimited = 2;

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf', '--huruf' => 'ب'])
            ->expectsOutputToContain('had seminit')
            ->assertSuccessful();

        Http::assertSentCount(2 + 2); // 2× kena had, kemudian jana + semak
        $this->assertTrue($this->index()['items']['ب']['verified']);
    }

    public function test_retry_after_parsing(): void
    {
        $r = fn ($json) => GenerateAiAudio::retryAfterSeconds(new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(429, [], json_encode($json))));

        $this->assertSame(13.0, $r(['error' => ['details' => [['@type' => 'RetryInfo', 'retryDelay' => '13s']]]]));
        $this->assertSame(13.04, $r(['error' => ['message' => 'Please retry in 13.04s.']]));
        $this->assertNull($r(['error' => ['message' => 'Daily limit reached']]));
    }

    public function test_gemini_switches_model_when_daily_quota_is_used_up(): void
    {
        config(['services.gemini.tts_models' => ['gemini-2.5-flash-preview-tts', 'gemini-3.8-flash-tts']]);
        $this->dailyExhausted = ['gemini-2.5-flash-preview-tts'];

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf'])
            ->expectsOutputToContain('kuota habis — tukar model')
            ->assertSuccessful();

        $items = $this->index()['items'];
        $this->assertCount(29, $items);
        $this->assertSame('gemini-3.8-flash-tts', $items['ب']['model']);
        // Model yang habis hanya dicuba sekali, bukan untuk setiap huruf.
        $this->assertCount(1, Http::recorded(fn ($r) => str_contains($r->url(), 'gemini-2.5-flash-preview-tts')));
        // Model 3.x diberi gaya sebagai tag supaya arahan tidak dibaca kuat.
        Http::assertSent(fn ($r) => str_contains($r->url(), 'gemini-3.8-flash-tts')
            && str_starts_with($r['contents'][0]['parts'][0]['text'], '[slowly'));
    }

    public function test_gemini_stops_cleanly_when_every_model_is_out_of_quota(): void
    {
        config(['services.gemini.tts_models' => ['gemini-2.5-flash-preview-tts', 'gemini-3.8-flash-tts']]);
        $this->dailyExhausted = ['gemini-2.5-flash-preview-tts', 'gemini-3.8-flash-tts'];

        $this->artisan('audio:ai', ['--engine' => 'gemini', '--set' => 'huruf'])
            ->expectsOutputToContain('Kuota harian semua model TTS Gemini sudah habis')
            ->assertFailed();
    }

    public function test_pages_render_when_generator_model_is_a_list(): void
    {
        // Bila model bertukar ikut kuota, index.json simpan senarai model — pernah sebabkan ralat 500 di production.
        File::ensureDirectoryExists($this->public.'/audio/ai/huruf');
        file_put_contents($this->public.'/audio/ai/huruf/02-aaaaaaaa.wav', 'RIFF');
        file_put_contents($this->public.'/audio/ai/huruf/03-bbbbbbbb.wav', 'RIFF');
        file_put_contents($this->public.'/audio/ai/index.json', json_encode([
            'generator' => ['provider' => 'Google Gemini', 'engine' => 'gemini', 'model' => ['gemini-3.8-flash-tts', 'gemini-2.5-flash-preview-tts']],
            'items' => [
                'ب' => ['file' => '/audio/ai/huruf/02-aaaaaaaa.wav', 'text' => 'بَاءْ', 'letter' => 'ب', 'set' => 'huruf', 'model' => 'gemini-3.8-flash-tts', 'verified' => true],
                'ت' => ['file' => '/audio/ai/huruf/03-bbbbbbbb.wav', 'text' => 'تَاءْ', 'letter' => 'ت', 'set' => 'huruf'],
            ],
        ]));
        OpenAudio::flush();
        $this->withoutVite(); // folder public sementara tiada build/manifest.json

        foreach (['/', '/kredit', '/belajar/2', '/baris', '/hafalan/sesi?group=all'] as $url) {
            $this->get($url)->assertOk();
        }
        $map = OpenAudio::clientMap(\App\Models\ArabicLetter::all());
        $this->assertSame('Suara AI · Google Gemini gemini-3.8-flash-tts', $map['ب']['credit']);
        $this->assertStringContainsString('gemini-3.8-flash-tts, gemini-2.5-flash-preview-tts', $map['ت']['credit']);
    }

    public function test_generator_refuses_to_run_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('audio:ai')->expectsOutputToContain('hanya untuk pembangunan')->assertFailed();
        Http::assertNothingSent();
    }
}
