<?php

namespace Tests\Feature;

use Database\Seeders\ArabicLetterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HafalanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ArabicLetterSeeder::class);
    }

    public function test_main_pages_render(): void
    {
        foreach (['/', '/pelan', '/belajar', '/belajar/1', '/hafalan', '/hafalan/sesi?group=all', '/kuiz', '/statistik', '/keputusan'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_quiz_distractors_prefer_lookalike_letters(): void
    {
        $questions = $this->get('/kuiz/main?mode=letter&count=30')->viewData('questions');

        $ba = $questions->firstWhere('arabic', 'ب');
        $this->assertNotNull($ba);
        $this->assertCount(4, $ba['options']);
        $this->assertContains('ب', $ba['options']);
        $this->assertEmpty(array_diff($ba['options']->all(), ['ب', 'ت', 'ث', 'ن', 'ي']));
    }

    public function test_each_mode_builds_valid_questions(): void
    {
        foreach (['name', 'letter', 'audio', 'form', 'mixed'] as $mode) {
            $questions = $this->get("/kuiz/main?mode={$mode}&count=all")->viewData('questions');

            $this->assertCount(29, $questions);
            foreach ($questions as $q) {
                $this->assertContains($q['answer'], $q['options']->all());
                $this->assertCount(4, $q['options']->unique());
                if ($q['mode'] === 'form') {
                    $this->assertNotSame($q['arabic'], $q['question']);
                }
            }
        }
    }

    public function test_quiz_can_target_specific_letters(): void
    {
        $questions = $this->get('/kuiz/main?mode=name&count=10&ids[]=2&ids[]=3')->viewData('questions');

        $this->assertCount(10, $questions);
        $this->assertEqualsCanonicalizing([2, 3], $questions->pluck('id')->unique()->values()->all());
    }

    public function test_form_mode_never_asks_hamzah_by_form(): void
    {
        $questions = $this->get('/kuiz/main?mode=form&count=all')->viewData('questions');

        $hamzah = $questions->firstWhere('arabic', 'ء');
        $this->assertSame('name', $hamzah['mode']);
    }

    public function test_plan_covers_every_letter_exactly_once_in_first_eight_days(): void
    {
        $plan = $this->get('/pelan')->viewData('plan');

        $this->assertCount(21, $plan);
        $introduced = collect($plan)->take(8)->pluck('letters')->flatten();
        $this->assertCount(29, $introduced);
        $this->assertCount(29, $introduced->unique());
        $this->assertSame([], collect($plan)->slice(8)->pluck('letters')->flatten()->all());
    }

    public function test_plan_calendar_downloads_valid_ics(): void
    {
        $response = $this->get('/pelan.ics?mula=2026-10-17&masa=21:00');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $body = $response->getContent();
        $this->assertSame(21, substr_count($body, 'BEGIN:VEVENT'));
        $this->assertStringContainsString('DTSTART:20261017T210000', $body);
        $this->assertStringContainsString('DTSTART:20261106T210000', $body);
        foreach (explode("\r\n", $body) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }
    }

    public function test_plan_calendar_rejects_bad_input(): void
    {
        $this->get('/pelan.ics?mula=esok&masa=25:00')->assertRedirect();
    }
}
