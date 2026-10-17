<?php

namespace Tests\Feature;

use App\Models\ArabicLetter;
use App\Support\Harakat;
use Database\Seeders\ArabicLetterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtrasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ArabicLetterSeeder::class);
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }

    // ── Rakaman guru ────────────────────────────────────────────────

    public function test_audio_upload_is_disabled_without_teacher_key(): void
    {
        config(['hafalan.audio_key' => null]);

        $this->postJson('/api/letters/2/audio', ['audio' => UploadedFile::fake()->create('a.mp3', 10, 'audio/mpeg')])
            ->assertForbidden();
    }

    public function test_audio_upload_rejects_wrong_key(): void
    {
        config(['hafalan.audio_key' => 'rahsia']);

        $this->postJson('/api/letters/2/audio', ['audio' => UploadedFile::fake()->create('a.mp3', 10, 'audio/mpeg')], ['X-Teacher-Key' => 'salah'])
            ->assertForbidden();
    }

    public function test_teacher_can_upload_and_remove_official_audio(): void
    {
        Storage::fake('public');
        config(['hafalan.audio_key' => 'rahsia']);

        $response = $this->postJson('/api/letters/2/audio',
            ['audio' => UploadedFile::fake()->create('ba.mp3', 10, 'audio/mpeg')],
            ['X-Teacher-Key' => 'rahsia']);

        $response->assertOk();
        $url = ArabicLetter::find(2)->audio_url;
        $this->assertStringStartsWith('/storage/audio/huruf-2-', $url);
        Storage::disk('public')->assertExists(substr($url, strlen('/storage/')));

        // Rakaman rasmi dihantar ke pelayar supaya speakArabic() mengutamakannya.
        $this->get('/')->assertSee(json_encode($url), false);

        $this->deleteJson('/api/letters/2/audio', [], ['X-Teacher-Key' => 'rahsia'])->assertOk();
        $this->assertNull(ArabicLetter::find(2)->audio_url);
        Storage::disk('public')->assertMissing(substr($url, strlen('/storage/')));
    }

    // ── Selaras peranti ─────────────────────────────────────────────

    public function test_sync_roundtrip(): void
    {
        $code = $this->postJson('/api/sync')->assertCreated()->json('code');
        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{12}$/', $code);

        $data = ['arabic_sm2' => ['2' => ['repetitions' => 1, 'interval' => 1, 'nextReview' => 1, 'lastReview' => 1]]];
        $this->putJson("/api/sync/{$code}", ['data' => $data])->assertOk();

        // Kod boleh ditaip dengan sengkang dan huruf kecil.
        $pretty = strtolower(implode('-', str_split($code, 4)));
        $this->getJson("/api/sync/{$pretty}")->assertOk()->assertJsonPath('data.arabic_sm2.2.repetitions', 1);
    }

    public function test_sync_unknown_code_is_404(): void
    {
        $this->getJson('/api/sync/AAAAAAAAAAAA')->assertNotFound();
        $this->putJson('/api/sync/AAAAAAAAAAAA', ['data' => []])->assertNotFound();
    }

    // ── Baris ───────────────────────────────────────────────────────

    public function test_baris_pages_render(): void
    {
        $this->get('/baris')->assertOk()->assertSee('بَ', false);
        $this->get('/baris/latih?pelajaran=i&mode=see')->assertOk();
    }

    public function test_harakat_rows_skip_alif_and_carry_hamzah(): void
    {
        $row = Harakat::row(ArabicLetter::all(), 'i');

        $this->assertCount(28, $row);
        $this->assertSame('إِ', collect($row)->firstWhere('letter', 'ء')['text']);
        $this->assertNull(collect($row)->firstWhere('letter', 'ا'));
        $this->assertSame('bi', collect($row)->firstWhere('letter', 'ب')['rumi']);
    }

    public function test_harakat_questions_have_unique_options_with_answer(): void
    {
        foreach (['a', 'i', 'u', 'campur'] as $lesson) {
            foreach (Harakat::questions(ArabicLetter::all(), $lesson, 'mixed', 40) as $q) {
                $this->assertContains($q['answer'], $q['options']);
                $this->assertCount(4, $q['options']);
            }
        }
        // Rumi mesti unik supaya soalan "lihat → pilih bacaan" tiada dua jawapan betul.
        $rumi = collect(['a', 'i', 'u'])->flatMap(fn ($v) => collect(Harakat::row(ArabicLetter::all(), $v))->pluck('rumi'));
        $this->assertSame($rumi->count(), $rumi->unique()->count());
    }

    // ── Cara sebut ──────────────────────────────────────────────────

    public function test_names_follow_akuislam_and_stay_unique(): void
    {
        $names = ArabicLetter::orderBy('order_num')->pluck('name', 'arabic');

        $this->assertSame('Kho', $names['خ']);
        $this->assertSame('Sod', $names['ص']);
        $this->assertSame('Dhod', $names['ض']);
        $this->assertSame('Ro', $names['ر']);
        $this->assertSame(['و', 'ه', 'ء', 'ي'], $names->keys()->slice(-4)->values()->all());
        $this->assertSame($names->count(), $names->unique()->count());
    }

    public function test_every_sifat_pair_covers_each_letter_exactly_once(): void
    {
        $letters = ArabicLetter::where('arabic', '!=', 'ا')->pluck('arabic')->sort()->values()->all();

        foreach (\App\Support\Tajwid::pairs() as $pair) {
            $all = collect($pair)->flatMap(fn ($s) => explode(' ', $s[2]))->sort()->values()->all();
            $this->assertSame($letters, $all, 'Pasangan '.$pair[0][0]);
        }
    }

    public function test_every_letter_page_shows_makhraj(): void
    {
        foreach (ArabicLetter::all() as $letter) {
            $this->get('/belajar/'.$letter->id)->assertOk()->assertSee('CARA SEBUT')->assertSee('Makhraj:');
        }
    }

    public function test_tebal_letters_read_with_o_on_fathah(): void
    {
        $this->assertSame('kho', Harakat::syllable('خ', 'a')['rumi']);
        $this->assertSame('ro', Harakat::syllable('ر', 'a')['rumi']);
        $this->assertSame('ri', Harakat::syllable('ر', 'i')['rumi']);
        $this->assertSame('ba', Harakat::syllable('ب', 'a')['rumi']);
    }

    // ── Rakaman terbuka (Lingua Libre) ──────────────────────────────

    public function test_open_audio_covers_every_letter_with_free_licence(): void
    {
        $entries = \App\Support\OpenAudio::entries();

        $this->assertCount(29, $entries);
        foreach (ArabicLetter::all() as $letter) {
            $e = $entries[$letter->arabic] ?? null;
            $this->assertNotNull($e, 'Tiada rakaman untuk '.$letter->name);
            $this->assertSame(sprintf('/audio/huruf/%02d.mp3', $letter->order_num), $e['file'], 'Nombor fail = order_num');
            $this->assertFileExists(public_path(ltrim($e['file'], '/')));
            $this->assertContains($e['license'], ['CC0', 'CC BY 4.0', 'CC BY-SA 3.0', 'CC BY-SA 4.0']);
            $this->assertStringStartsWith('https://commons.wikimedia.org/', $e['source_page']);
        }
    }

    public function test_teacher_recording_overrides_open_audio_and_delete_reverts(): void
    {
        Storage::fake('public');
        config(['hafalan.audio_key' => 'rahsia']);

        // Sandaran = suara AI jika sudah dijana, jika tidak Lingua Libre — ujian tidak bergantung pada yang mana.
        $fallback = \App\Support\OpenAudio::clientEntry('ب');
        $this->assertContains($fallback['source'], ['ai', 'open']);
        $this->get('/')->assertSee(json_encode($fallback['url']), false);

        $url = $this->postJson('/api/letters/2/audio', ['audio' => UploadedFile::fake()->create('ba.mp3', 10, 'audio/mpeg')], ['X-Teacher-Key' => 'rahsia'])
            ->json('audio_url');
        $this->get('/')->assertSee(json_encode($url), false)->assertDontSee(json_encode($fallback['url']), false);

        $this->deleteJson('/api/letters/2/audio', [], ['X-Teacher-Key' => 'rahsia'])
            ->assertJsonPath('fallback.url', $fallback['url'])
            ->assertJsonPath('fallback.source', $fallback['source']);
    }

    public function test_credits_page_lists_every_speaker(): void
    {
        $this->get('/kredit')->assertOk()->assertSee('AlNatiq')->assertSee('Zinou2go')->assertSee('Rashed Aljahdali')->assertSee('CC BY-SA 3.0');
    }

    public function test_reseeding_updates_old_data_but_keeps_ids_and_teacher_audio(): void
    {
        // Simulasi pangkalan data lama di production (nama & bentuk Hamzah lama, rakaman guru).
        $hamzah = ArabicLetter::where('arabic', 'ء')->first();
        $hamzah->update(['name' => 'Hamzah', 'form_initial' => 'أ', 'form_medial' => 'ئـ', 'form_final' => 'ئ', 'order_num' => 29]);
        ArabicLetter::where('arabic', 'خ')->update(['name' => 'Kha', 'audio_url' => '/storage/audio/guru-kho.webm']);
        $ids = ArabicLetter::pluck('id', 'arabic')->all();

        $this->seed(ArabicLetterSeeder::class);

        $this->assertSame($ids, ArabicLetter::pluck('id', 'arabic')->all());
        $this->assertSame(29, ArabicLetter::count());
        $fresh = ArabicLetter::where('arabic', 'ء')->first();
        $this->assertSame(['ء', 'ء', 'ء'], [$fresh->form_initial, $fresh->form_medial, $fresh->form_final]);
        $this->assertSame(28, $fresh->order_num);
        $kho = ArabicLetter::where('arabic', 'خ')->first();
        $this->assertSame('Kho', $kho->name);
        $this->assertSame('/storage/audio/guru-kho.webm', $kho->audio_url);
    }
}
